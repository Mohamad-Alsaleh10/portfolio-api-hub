<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Notifications\CommentNotification;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     * عرض قائمة بالمشاريع.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // جلب جميع المشاريع مع المستخدم صاحب المشروع والوسائط
        // يمكن إضافة تصفية وبحث هنا لاحقاً
        $projects = Project::with(['user:id,name,profile_picture', 'media', 'categories:id,name', 'tags:id,name'])
                           ->withCount(['likes', 'comments']) // لحساب عدد الإعجابات والتعليقات
                           ->latest() // ترتيب حسب الأحدث
                           ->paginate(10); // تقسيم النتائج على صفحات

        return response()->json($projects);
    }

    /**
     * Store a newly created project in storage.
     * تخزين مشروع تم إنشاؤه حديثاً.
     *
     * @param  \App\Http\Requests\StoreProjectRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProjectRequest $request)
    {
        // إنشاء slug فريد للمشروع
        $slug = Str::slug($request->title);
        // التأكد من أن الـ slug فريد
        $originalSlug = $slug;
        $count = 1;
        while (Project::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $project = Project::create([
            'user_id' => $request->user()->id, // تعيين المستخدم الحالي كصاحب المشروع
            'title' => $request->title,
            'description' => $request->description,
            'slug' => $slug,
        ]);

        // معالجة رفع وسائط المشروع
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $index => $file) {
                $path = $file->store('project_media', 'public'); // حفظ الملفات في مجلد 'project_media'
                ProjectMedia::create([
                    'project_id' => $project->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(), // حفظ نوع MIME (مثال: image/jpeg, video/mp4)
                    'order' => $index, // حفظ ترتيب الوسائط
                ]);
            }
        }

        // ربط الفئات
        if ($request->has('category_ids')) {
            $project->categories()->attach($request->category_ids);
        }

        // ربط الوسوم
        if ($request->has('tag_ids')) {
            $project->tags()->attach($request->tag_ids);
        }

        return response()->json([
            'message' => 'Project created successfully!',
            'project' => $project->load(['user:id,name', 'media', 'categories', 'tags']) // إعادة تحميل المشروع بالبيانات المترابطة
        ], 201); // 201 Created
    }

    /**
     * Display the specified project.
     * عرض المشروع المحدد.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        // زيادة عدد المشاهدات
        $project->increment('views_count');

        // تحميل المشروع مع جميع علاقاته لعرض تفاصيله
        $project->load(['user:id,name,profile_picture', 'media', 'categories:id,name', 'tags:id,name', 'likes.user:id,name', 'comments.user:id,name']);

        // إضافة معلومات هل المستخدم الحالي أعجب بالمشروع أم لا
        $project->is_liked_by_current_user = $project->likes->contains('user_id', auth()->id());

        return response()->json($project);
    }

    /**
     * Update the specified project in storage.
     * تحديث المشروع المحدد.
     *
     * @param  \App\Http\Requests\UpdateProjectRequest  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // التحقق من أن المستخدم الحالي هو صاحب المشروع
        if ($request->user()->id !== $project->user_id) {
            return response()->json(['message' => 'Unauthorized to update this project.'], 403);
        }

        // تحديث الحقول الأساسية
        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title), // يمكن إعادة توليد الـ slug أو تركه كما هو إذا لم يتغير العنوان
        ]);

        // معالجة تحديث وسائط المشروع (هذا الجزء يمكن أن يكون معقداً وقد يتطلب واجهة أمامية للتعامل مع حذف/إضافة)
        // في هذا المثال، سنقوم بحذف كل الوسائط القديمة وإعادة رفعها
        if ($request->has('remove_media_ids')) {
            foreach ($request->remove_media_ids as $mediaId) {
                $media = ProjectMedia::find($mediaId);
                if ($media && $media->project_id === $project->id) {
                    Storage::delete($media->file_path);
                    $media->delete();
                }
            }
        }

        // إضافة وسائط جديدة
        if ($request->hasFile('new_media')) {
            foreach ($request->file('new_media') as $index => $file) {
                $path = $file->store('project_media', 'public');
                ProjectMedia::create([
                    'project_id' => $project->id,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'order' => $project->media()->count() + $index, // ترتيب جديد
                ]);
            }
        }


        // تحديث الفئات
        if ($request->has('category_ids')) {
            $project->categories()->sync($request->category_ids); // sync لحذف/إضافة الفئات ليتطابق مع القائمة الجديدة
        } else {
             $project->categories()->detach(); // إذا لم يتم إرسال فئات، قم بإزالتها كلها
        }

        // تحديث الوسوم
        if ($request->has('tag_ids')) {
            $project->tags()->sync($request->tag_ids); // sync لحذف/إضافة الوسوم ليتطابق مع القائمة الجديدة
        } else {
            $project->tags()->detach(); // إذا لم يتم إرسال وسوم، قم بإزالتها كلها
        }

        return response()->json([
            'message' => 'Project updated successfully!',
            'project' => $project->load(['user:id,name', 'media', 'categories', 'tags'])
        ]);
    }

    /**
     * Remove the specified project from storage.
     * حذف المشروع المحدد من التخزين.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Project $project)
    {
        // التحقق من أن المستخدم الحالي هو صاحب المشروع
        if ($request->user()->id !== $project->user_id) {
            return response()->json(['message' => 'Unauthorized to delete this project.'], 403);
        }

        // حذف جميع وسائط المشروع من التخزين
        foreach ($project->media as $media) {
            Storage::delete($media->file_path);
        }

        $project->delete(); // هذا سيقوم أيضاً بحذف العلاقات (likes, comments, pivot tables) بسبب onDelete('cascade') في الترحيلات

        return response()->json(['message' => 'Project deleted successfully!'], 200);
    }

    public function addComment(Request $request, Project $project)
    {
        $request->validate(['content' => 'required|string|max:1000']);
        
        $user = $request->user();

        $comment = $project->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);
        
        // إرسال الإشعار لصاحب المشروع إذا لم يكن هو نفسه المعلق
        if ($project->user_id !== $user->id) {
            $project->user->notify(new CommentNotification($comment));
        }

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment->load('user') // تحميل بيانات المستخدم الذي علق
        ], 201);
    }
}
