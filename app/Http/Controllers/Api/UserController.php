<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UpdateUserProfileRequest; // سنقوم بإنشاء هذا لاحقاً
use Illuminate\Support\Facades\Storage; // لاستخدام التخزين

class UserController extends Controller
{
    /**
     * Display the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        // استخدام $request->user() يجلب المستخدم المصادق عليه
        $user = $request->user();

        // إرجاع بيانات المستخدم
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture' => $user->profile_picture ? Storage::url($user->profile_picture) : null, // إنشاء URL للصورة
            'bio' => $user->bio,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  \App\Http\Requests\UpdateUserProfileRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserProfileRequest $request)
    {
        $user = $request->user();

        // تحديث الحقول المسموح بها
        $user->name = $request->name;
        $user->bio = $request->bio;
        // يمكن للمستخدم تغيير البريد الإلكتروني، ولكن قد يتطلب ذلك تأكيداً لاحقاً
        if ($request->filled('email') && $request->email !== $user->email) {
            $user->email = $request->email;
            // يمكنك إضافة منطق للتحقق من البريد الإلكتروني هنا (مثل إرسال رابط تأكيد)
        }

        // تحديث كلمة المرور إذا تم توفيرها
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // معالجة رفع الصورة الشخصية
        if ($request->hasFile('profile_picture')) {
            // حذف الصورة القديمة إذا وجدت
            if ($user->profile_picture) {
                Storage::delete($user->profile_picture);
            }
            // حفظ الصورة الجديدة
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        } elseif ($request->boolean('remove_profile_picture')) {
            // إذا طلب المستخدم إزالة الصورة
            if ($user->profile_picture) {
                Storage::delete($user->profile_picture);
                $user->profile_picture = null;
            }
        }


        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture ? Storage::url($user->profile_picture) : null,
                'bio' => $user->bio,
                'role' => $user->role,
            ]
        ], 200);
    }
}
