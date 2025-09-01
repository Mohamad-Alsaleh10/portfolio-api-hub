<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // لاستخدام Rule

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // يجب أن يكون المستخدم مصادق عليه (موجود في الـ Auth::user())
        // بما أن المسار محمي بـ 'auth:sanctum'، هذا يكفي لـ authorize
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->user()->id; // الحصول على ID المستخدم الحالي

        return [
            'name' => ['required', 'string', 'max:255'],
            // البريد الإلكتروني يجب أن يكون فريداً باستثناء البريد الإلكتروني الحالي للمستخدم
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // 'confirmed' يتطلب وجود password_confirmation
            'bio' => ['nullable', 'string', 'max:1000'],
            // الصورة الشخصية: يمكن أن تكون صورة، بحد أقصى 2MB، وأنواع محددة
            'profile_picture' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,svg'],
            // حقل لتحديد ما إذا كان يجب إزالة الصورة الحالية
            'remove_profile_picture' => ['boolean'],
        ];
    }
}
