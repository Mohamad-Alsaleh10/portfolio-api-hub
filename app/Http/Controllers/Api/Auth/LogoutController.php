<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Log the user out (revoke the current token).
     * تسجيل خروج المستخدم (إلغاء الـ Token الحالي).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // التحقق من أن هناك مستخدم مصادق عليه عبر Sanctum
        if ($request->user('sanctum')) {
            // إلغاء الـ Token الحالي الذي يستخدمه المستخدم
            $request->user('sanctum')->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully!'], 200);
    }
}
