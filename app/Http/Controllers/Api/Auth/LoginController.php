<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle an incoming authentication request.
     * معالجة طلب المصادقة الوارد من الـ API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // محاولة مصادقة المستخدم
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'), // رسالة خطأ قياسية من Laravel
            ]);
        }

        $user = Auth::user();

        // حذف أي Tokens قديمة لهذا المستخدم لمنع تراكمها
        // يمكنك إبقاء هذه الخطوة اختيارية إذا كنت تفضل أن يكون للمستخدم عدة Tokens
        $user->tokens()->delete();

        // إنشاء Sanctum API Token جديد للمستخدم
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture,
                'bio' => $user->bio,
                'role' => $user->role,
            ]
        ], 200);
    }
}

