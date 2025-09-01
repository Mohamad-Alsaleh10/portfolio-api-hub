<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Project;
use App\Models\Comment;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
            'reportable_type' => 'required|string|in:project,comment,user',
            'reportable_id' => 'required|integer',
        ]);

        $reportable = null;
        switch ($request->reportable_type) {
            case 'project':
                $reportable = Project::find($request->reportable_id);
                break;
            case 'comment':
                $reportable = Comment::find($request->reportable_id);
                break;
            case 'user':
                $reportable = User::find($request->reportable_id);
                break;
        }

        if (!$reportable) {
            throw ValidationException::withMessages(['reportable_id' => 'The specified item does not exist.']);
        }

        Report::create([
            'reporter_id' => auth()->id(),
            'reported_user_id' => $reportable instanceof User ? $reportable->id : $reportable->user_id,
            'reason' => $request->reason,
            'reportable_type' => get_class($reportable),
            'reportable_id' => $reportable->id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Report submitted successfully. We will review your report shortly.'], 201);
    }
}