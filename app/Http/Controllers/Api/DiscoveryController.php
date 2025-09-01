<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder; // لاستخدام Builder لـ queries معقدة

class DiscoveryController extends Controller
{
    /**
     * Search for projects.
     * البحث عن المشاريع بناءً على الكلمات المفتاحية، الفئات، والوسوم.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProjects(Request $request)
    {
        $query = Project::with(['user:id,name,profile_picture', 'media:id,project_id,file_path', 'categories:id,name', 'tags:id,name'])
                        ->withCount(['likes', 'comments']);

        // البحث بالنص في العنوان والوصف
        if ($request->has('keyword') && $request->keyword !== '') {
            $keyword = $request->keyword;
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // الفلترة حسب الفئة
        if ($request->has('category_id')) {
            $query->whereHas('categories', function (Builder $q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // الفلترة حسب الوسم
        if ($request->has('tag_id')) {
            $query->whereHas('tags', function (Builder $q) use ($request) {
                $q->where('tag_id', $request->tag_id);
            });
        }

        // الفلترة حسب المستخدم (مشاريع مستخدم معين)
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $projects = $query->latest()->paginate(10); // ترتيب حسب الأحدث وتقسيم على صفحات

        return response()->json($projects);
    }

    /**
     * Search for users.
     * البحث عن المستخدمين بناءً على الاسم أو السيرة الذاتية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $query = User::select('id', 'name', 'profile_picture', 'bio'); // تحديد الحقول المطلوبة لتجنب جلب بيانات حساسة

        if ($request->has('keyword') && $request->keyword !== '') {
            $keyword = $request->keyword;
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('bio', 'like', '%' . $keyword . '%');
            });
        }

        $users = $query->latest()->paginate(10); // ترتيب حسب الأحدث وتقسيم على صفحات

        return response()->json($users);
    }

    /**
     * Get trending projects.
     * جلب المشاريع الرائجة (يمكن تحديد المعايير لاحقاً، حالياً حسب المشاهدات والإعجابات).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trendingProjects()
    {
        // مثال بسيط للرائجة: المشاريع ذات أكبر عدد من المشاهدات في آخر 30 يوماً
        // أو يمكن أن تكون حسب عدد الإعجابات في فترة معينة
        $trending = Project::with(['user:id,name,profile_picture', 'media:id,project_id,file_path'])
                           ->withCount(['likes', 'comments'])
                           ->where('created_at', '>=', now()->subDays(30)) // المشاريع التي أنشئت في آخر 30 يومًا
                           ->orderBy('views_count', 'desc') // ترتيب حسب عدد المشاهدات
                           ->orderBy('likes_count', 'desc') // ثم حسب عدد الإعجابات
                           ->take(10) // جلب 10 مشاريع فقط
                           ->get();

        return response()->json($trending);
    }

    /**
     * Get all categories.
     * جلب جميع الفئات.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories()
    {
        $categories = Category::select('id', 'name', 'slug', 'description')->get();
        return response()->json($categories);
    }

    /**
     * Get all tags.
     * جلب جميع الوسوم.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTags()
    {
        $tags = Tag::select('id', 'name', 'slug')->get();
        return response()->json($tags);
    }

    /**
     * Get projects by a specific category slug.
     * جلب المشاريع حسب slug الفئة.
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectsByCategory(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail(); // جلب الفئة أو إرجاع 404

        $projects = $category->projects()
                             ->with(['user:id,name,profile_picture', 'media:id,project_id,file_path'])
                             ->withCount(['likes', 'comments'])
                             ->latest()
                             ->paginate(10);

        return response()->json($projects);
    }

    /**
     * Get projects by a specific tag slug.
     * جلب المشاريع حسب slug الوسم.
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectsByTag(string $slug)
    {
        $tag = Tag::where('slug', $slug)->firstOrFail(); // جلب الوسم أو إرجاع 404

        $projects = $tag->projects()
                        ->with(['user:id,name,profile_picture', 'media:id,project_id,file_path'])
                        ->withCount(['likes', 'comments'])
                        ->latest()
                        ->paginate(10);

        return response()->json($projects);
    }


    /**
     * Get personalized project recommendations for the authenticated user.
     * جلب مشاريع مقترحة للمستخدم المصادق عليه بناءً على اهتماماته.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forYouProjects(Request $request)
    {
        $user = $request->user();

        // 1. جمع الفئات والوسوم من المشاريع التي أعجب بها المستخدم
        $likedProjects = $user->likes()->with('project.categories', 'project.tags')->get()->pluck('project');

        $preferredCategories = $likedProjects->pluck('categories')->flatten()->pluck('id')->unique();
        $preferredTags = $likedProjects->pluck('tags')->flatten()->pluck('id')->unique();

        // 2. البحث عن مشاريع مشابهة بناءً على الفئات والوسوم
        $projects = Project::with(['user:id,name,profile_picture', 'media:id,project_id,file_path'])
                           ->where(function ($query) use ($preferredCategories, $preferredTags) {
                               // البحث عن المشاريع التي تشترك في أي من الفئات المفضلة
                               $query->whereHas('categories', function ($q) use ($preferredCategories) {
                                   $q->whereIn('category_id', $preferredCategories);
                               });

                               // أو المشاريع التي تشترك في أي من الوسوم المفضلة
                               $query->orWhereHas('tags', function ($q) use ($preferredTags) {
                                   $q->whereIn('tag_id', $preferredTags);
                               });
                           })
                           // استبعاد المشاريع التي يملكها المستخدم
                           ->where('user_id', '!=', $user->id)
                           // استبعاد المشاريع التي أعجب بها المستخدم بالفعل
                           ->whereDoesntHave('likes', function ($q) use ($user) {
                               $q->where('user_id', $user->id);
                           })
                           ->inRandomOrder() // ترتيب عشوائي لتقديم تنوع
                           ->limit(10) // جلب 10 مشاريع فقط
                           ->get();

        return response()->json($projects);
    }
}
