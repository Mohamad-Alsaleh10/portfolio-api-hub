<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    /**
     * Get all comments for a specific project.
     * جلب جميع التعليقات لمشروع محدد.
     *
     * @param  Project $project
     * @return JsonResponse
     */
    public function index(Project $project): JsonResponse
    {
        $comments = $project->comments()
                            ->with('user:id,name,profile_picture') // جلب بيانات المستخدم مع كل تعليق
                            ->latest()
                            ->paginate(15);

        return response()->json($comments);
    }

    /**
     * Store a new comment for a specific project.
     * تخزين تعليق جديد لمشروع محدد.
     *
     * @param  Request $request
     * @param  Project $project
     * @return JsonResponse
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = auth()->user();

        // إنشاء التعليق
        $comment = $project->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);

        // جلب التعليق مع بيانات المستخدم لإرجاعه في الرد
        $comment->load('user:id,name,profile_picture');

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment,
        ], 201);
    }

    /**
     * Remove the specified comment from storage.
     * حذف التعليق المحدد من التخزين.
     *
     * @param  Request $request
     * @param  Comment $comment
     * @return JsonResponse
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $user = auth()->user();

        // التحقق من أن المستخدم المصادق عليه هو صاحب التعليق أو صاحب المشروع
        if ($user->id !== $comment->user_id && $user->id !== $comment->project->user_id) {
            return response()->json(['message' => 'Unauthorized to delete this comment.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully.']);
    }
}