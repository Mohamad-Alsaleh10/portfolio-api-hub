<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Like;
use App\Models\Comment;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Validation\ValidationException;
use App\Notifications\LikeNotification;


class SocialController extends Controller
{
    // --- Likes (الإعجابات) ---

    /**
     * Toggle a like on a project.
     * تبديل حالة الإعجاب بمشروع (إضافة/إزالة).
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleLike(Project $project)
    {
        $user = auth()->user(); // المستخدم المصادق عليه

        // البحث عن إعجاب حالي من قبل هذا المستخدم على هذا المشروع
        $like = Like::where('user_id', $user->id)
                    ->where('project_id', $project->id)
                    ->first();

        if ($like) {
            // إذا كان الإعجاب موجوداً، قم بحذفه (إلغاء الإعجاب)
            $like->delete();
            return response()->json(['message' => 'Project unliked successfully.', 'liked' => false], 200);
        } else {
            // إذا لم يكن الإعجاب موجوداً، قم بإنشائه (إضافة إعجاب)
            Like::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
            ]);

                // إرسال الإشعار إلى صاحب المشروع
            $project->user->notify(new LikeNotification($like));

            return response()->json(['message' => 'Project liked successfully.', 'liked' => true], 201); // 201 Created
        }
    }

    // --- Comments (التعليقات) ---

    /**
     * Add a comment to a project.
     * إضافة تعليق على مشروع.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request, Project $project)
    {
        $request->validate([
            'content' => 'required|string|max:500', // محتوى التعليق مطلوب وبحد أقصى 500 حرف
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(), // المستخدم المصادق عليه
            'project_id' => $project->id,
            'content' => $request->content,
        ]);

        // تحميل المستخدم الذي قام بالتعليق لعرضه في الاستجابة
        $comment->load('user:id,name,profile_picture');

        return response()->json([
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ], 201);
    }

    /**
     * Update a comment.
     * تحديث تعليق.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComment(Request $request, Comment $comment)
    {
        // التحقق من أن المستخدم الحالي هو صاحب التعليق
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized to update this comment.'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment->update(['content' => $request->content]);

        // تحميل المستخدم الذي قام بالتعليق لعرضه في الاستجابة
        $comment->load('user:id,name,profile_picture');

        return response()->json([
            'message' => 'Comment updated successfully!',
            'comment' => $comment
        ], 200);
    }

    /**
     * Delete a comment.
     * حذف تعليق.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment(Request $request, Comment $comment)
    {
        // التحقق من أن المستخدم الحالي هو صاحب التعليق أو مدير (سنتعامل مع الأدوار لاحقاً في Admin Panel)
        if ($request->user()->id !== $comment->user_id) {
            // يمكن إضافة check لدور المدير هنا: || $request->user()->isAdmin()
            return response()->json(['message' => 'Unauthorized to delete this comment.'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully!'], 200);
    }

    // --- Following (المتابعة) ---

    /**
     * Toggle follow/unfollow a user.
     * تبديل حالة المتابعة لمستخدم (متابعة/إلغاء متابعة).
     *
     * @param  \App\Models\User  $userToFollow
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleFollow(Request $request, $id) // تم تعديل توقيع الدالة
    {
        // **الخطوة الجديدة:** جلب المستخدم يدوياً باستخدام find
        $userToFollow = User::find($id);

        if (is_null($userToFollow)) {
            // إذا لم يتم العثور على المستخدم بالـ ID المحدد
            return response()->json(['message' => 'The user you are trying to follow does not exist.'], Response::HTTP_NOT_FOUND); // 404 Not Found
        }

        $currentUser = auth()->user();

        // تأكد من أن المستخدم الحالي مصادق عليه (هذا يجب أن يتم بواسطة middleware 'auth:sanctum')
        if (!$currentUser) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }

        // لا يمكن للمستخدم متابعة نفسه
        if ($currentUser->id === $userToFollow->id) {
            return response()->json(['message' => 'You cannot follow yourself.'], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        }

        // البحث عن علاقة متابعة حالية
        $follow = Follow::where('follower_id', $currentUser->id)
                        ->where('followed_id', $userToFollow->id)
                        ->first();

        if ($follow) {
            // إذا كان يتابعه بالفعل، قم بحذفه (إلغاء المتابعة)
            $follow->delete();
            return response()->json(['message' => 'User unfollowed successfully.', 'following' => false], 200);
        } else {
            // إذا لم يكن الإعجاب موجوداً، قم بإنشائه (إضافة إعجاب)
            Follow::create([
                'follower_id' => $currentUser->id,
                'followed_id' => $userToFollow->id,
            ]);
            return response()->json(['message' => 'User followed successfully.', 'following' => true], 201);
        }
    }
}
