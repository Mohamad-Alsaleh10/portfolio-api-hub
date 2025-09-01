<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * معالجة الطلب الوارد.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // إذا لم يكن المستخدم مصادق عليه، قم بإعادة توجيههم إلى صفحة تسجيل الدخول
        if (!Auth::check()) {
            return redirect('/login');
        }

        // جلب المستخدم المصادق عليه
        $user = Auth::user();

        // التحقق مما إذا كان دور المستخدم يتطابق مع الدور المطلوب
        if ($user->role === $role) {
            return $next($request); // السماح بمرور الطلب
        }

        // إذا لم يكن الدور مطابقاً، قم بإعادة توجيههم أو إرجاع خطأ 403 (Unauthorized)
        return abort(403, 'Unauthorized action.');
    }
}
