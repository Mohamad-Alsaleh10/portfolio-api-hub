<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ProjectMedia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Report;
use App\Models\Ban;
use App\Notifications\BanNotification; // يجب التأكد من وجود هذا الإشعار
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Get a list of the main dashboard statistics.
     * جلب إحصائيات لوحة التحكم الرئيسية بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $totalUsers = User::count();
        $totalProjects = Project::count();
        $totalComments = Comment::count();
        $newContactMessages = ContactMessage::where('created_at', '>=', now()->subDays(7))->count();

        $latestProjects = Project::with(['user:id,name'])->latest()->take(5)->get();
        $latestUsers = User::latest()->take(5)->get();

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalProjects' => $totalProjects,
            'totalComments' => $totalComments,
            'newContactMessages' => $newContactMessages,
            'latestProjects' => $latestProjects,
            'latestUsers' => $latestUsers,
        ]);
    }

    // --- User Management (إدارة المستخدمين) ---

    /**
     * Get a paginated list of users.
     * جلب قائمة بالمستخدمين بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function users(): JsonResponse
    {
        $users = User::paginate(15);
        return response()->json($users);
    }

    /**
     * Update the specified user in storage via API.
     * تحديث المستخدم المحدد عبر API.
     *
     * @param  Request $request
     * @param  User    $user
     * @return JsonResponse
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,user',
            'bio' => 'nullable|string|max:1000',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|max:2048',
            'remove_profile_picture' => 'boolean',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'bio' => $request->bio,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->boolean('remove_profile_picture') && $user->profile_picture) {
            Storage::delete($user->profile_picture);
            $userData['profile_picture'] = null;
        } elseif ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::delete($user->profile_picture);
            }
            $userData['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->update($userData);

        return response()->json(['message' => 'User updated successfully.', 'user' => $user->fresh()], 200);
    }

    /**
     * Remove the specified user from storage via API.
     * حذف المستخدم المحدد عبر API.
     *
     * @param  User $user
     * @return JsonResponse
     */
    public function deleteUser(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }

        if ($user->profile_picture) {
            Storage::delete($user->profile_picture);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }

    // --- Project Management (إدارة المشاريع) ---

    /**
     * Get a paginated list of projects.
     * جلب قائمة بالمشاريع بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function projects(): JsonResponse
    {
        $projects = Project::with('user')->paginate(15);
        return response()->json($projects);
    }

    /**
     * Update the specified project in storage via API.
     * تحديث المشروع المحدد عبر API.
     *
     * @param  Request $request
     * @param  Project $project
     * @return JsonResponse
     */
    public function updateProject(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'new_media.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi|max:10240',
            'remove_media_ids' => 'nullable|array',
            'remove_media_ids.*' => 'exists:project_media,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $projectData = [
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
        ];

        $project->update($projectData);

        if ($request->has('remove_media_ids')) {
            $mediaToDelete = ProjectMedia::whereIn('id', $request->input('remove_media_ids'))
                                         ->where('project_id', $project->id)
                                         ->get();
            foreach ($mediaToDelete as $media) {
                Storage::delete($media->file_path);
                $media->delete();
            }
        }

        if ($request->hasFile('new_media')) {
            foreach ($request->file('new_media') as $file) {
                $path = $file->store('project_media', 'public');
                ProjectMedia::create([
                    'project_id' => $project->id,
                    'file_path' => $path,
                    'file_type' => Str::startsWith($file->getMimeType(), 'image') ? 'image' : 'video',
                ]);
            }
        }

        $project->categories()->sync($request->input('category_ids', []));
        $project->tags()->sync($request->input('tag_ids', []));

        return response()->json(['message' => 'Project updated successfully.']);
    }

    /**
     * Remove the specified project from storage via API.
     * حذف المشروع المحدد عبر API.
     *
     * @param  Project $project
     * @return JsonResponse
     */
    public function deleteProject(Project $project): JsonResponse
    {
        foreach ($project->media as $media) {
            Storage::delete($media->file_path);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully.']);
    }

    // --- Comment Management (إدارة التعليقات) ---

    /**
     * Get a paginated list of comments.
     * جلب قائمة بالتعليقات بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function comments(): JsonResponse
    {
        $comments = Comment::with(['user', 'project'])->paginate(15);
        return response()->json($comments);
    }

    /**
     * Remove the specified comment from storage via API.
     * حذف التعليق المحدد عبر API.
     *
     * @param  Comment $comment
     * @return JsonResponse
     */
    public function deleteComment(Comment $comment): JsonResponse
    {
        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully.']);
    }

    // --- Category Management (إدارة الفئات) ---

    /**
     * Get a paginated list of categories.
     * جلب قائمة بالفئات بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = Category::paginate(15);
        return response()->json($categories);
    }

    /**
     * Store a newly created category in storage via API.
     * تخزين فئة جديدة عبر API.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Category added successfully.'], 201);
    }

    /**
     * Update the specified category in storage via API.
     * تحديث الفئة المحددة عبر API.
     *
     * @param  Request $request
     * @param  Category $category
     * @return JsonResponse
     */
    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Category updated successfully.']);
    }

    /**
     * Remove the specified category from storage via API.
     * حذف الفئة المحددة عبر API.
     *
     * @param  Category $category
     * @return JsonResponse
     */
    public function deleteCategory(Category $category): JsonResponse
    {
        $category->projects()->detach();
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }

    // --- Tag Management (إدارة الوسوم) ---

    /**
     * Get a paginated list of tags.
     * جلب قائمة بالوسوم بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function tags(): JsonResponse
    {
        $tags = Tag::paginate(15);
        return response()->json($tags);
    }

    /**
     * Store a newly created tag in storage via API.
     * تخزين وسم جديد عبر API.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function storeTag(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'slug' => 'nullable|string|max:255|unique:tags,slug',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Tag::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        Tag::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return response()->json(['message' => 'Tag added successfully.'], 201);
    }

    /**
     * Update the specified tag in storage via API.
     * تحديث الوسم المحدد عبر API.
     *
     * @param  Request $request
     * @param  Tag     $tag
     * @return JsonResponse
     */
    public function updateTag(Request $request, Tag $tag): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
            'slug' => 'nullable|string|max:255|unique:tags,slug,' . $tag->id,
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Tag::where('slug', $slug)->where('id', '!=', $tag->id)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $tag->update([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return response()->json(['message' => 'Tag updated successfully.']);
    }

    /**
     * Remove the specified tag from storage via API.
     * حذف الوسم المحدد عبر API.
     *
     * @param  Tag $tag
     * @return JsonResponse
     */
    public function deleteTag(Tag $tag): JsonResponse
    {
        $tag->projects()->detach();
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully.']);
    }

    // --- Contact Message Management (إدارة رسائل الاتصال) ---

    /**
     * Get a paginated list of contact messages.
     * جلب قائمة برسائل الاتصال بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function contactMessages(): JsonResponse
    {
        $messages = ContactMessage::latest()->paginate(15);
        return response()->json($messages);
    }

    /**
     * Remove the specified contact message from storage via API.
     * حذف رسالة الاتصال المحددة عبر API.
     *
     * @param  ContactMessage $message
     * @return JsonResponse
     */
    public function deleteContactMessage(ContactMessage $message): JsonResponse
    {
        $message->delete();
        return response()->json(['message' => 'Contact message deleted successfully.']);
    }

    // --- Report Management (إدارة التقارير) ---

    /**
     * Get a paginated list of pending reports.
     * جلب قائمة بالتقارير المعلقة بصيغة JSON.
     *
     * @return JsonResponse
     */
    public function reports(): JsonResponse
    {
        $reports = Report::with(['reporter:id,name,profile_picture', 'reportedUser:id,name,profile_picture', 'reportable'])
                         ->where('status', 'pending')
                         ->latest()
                         ->paginate(15);

        return response()->json($reports);
    }

    /**
     * Accept a report and ban the user via API.
     * قبول تقرير وحظر المستخدم عبر API.
     *
     * @param  Request $request
     * @param  Report  $report
     * @return JsonResponse
     */
    public function acceptReport(Request $request, Report $report): JsonResponse
    {
        if ($report->status !== 'pending') {
            return response()->json(['message' => 'This report has already been handled.'], 409);
        }

        $request->validate([
            'ban_duration' => 'required|integer|min:1|max:365',
            'ban_reason' => 'required|string|max:500',
        ]);

        $reportedUser = $report->reportedUser;

        Ban::create([
            'user_id' => $reportedUser->id,
            'admin_id' => auth()->id(),
            'reason' => $request->ban_reason,
            'banned_until' => now()->addDays($request->ban_duration),
        ]);

        $report->status = 'accepted';
        $report->save();

        // يجب التأكد من وجود BanNotification قبل تفعيل هذا السطر
        // $reportedUser->notify(new BanNotification($request->ban_reason, $request->ban_duration));

        return response()->json(['message' => 'User banned successfully and report accepted.']);
    }

    /**
     * Reject a report via API.
     * رفض تقرير عبر API.
     *
     * @param  Report $report
     * @return JsonResponse
     */
    public function rejectReport(Report $report): JsonResponse
    {
        if ($report->status !== 'pending') {
            return response()->json(['message' => 'This report has already been handled.'], 409);
        }

        $report->status = 'rejected';
        $report->save();

        return response()->json(['message' => 'Report rejected successfully.']);
    }
}
