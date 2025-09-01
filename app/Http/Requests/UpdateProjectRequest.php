<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // المستخدم المصادق عليه يجب أن يكون صاحب المشروع
        // note: 'project' here is the route model binding for Project
        return $this->user() && $this->user()->id === $this->route('project')->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'], // 'sometimes' لتحديث الحقول فقط إذا تم إرسالها
            'description' => ['sometimes', 'required', 'string'],
            'new_media' => ['nullable', 'array'], // لإضافة وسائط جديدة
            'new_media.*' => ['file', 'mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi', 'max:10240'],
            'remove_media_ids' => ['nullable', 'array'], // لحذف وسائط موجودة
            'remove_media_ids.*' => ['exists:project_media,id'], // التأكد من أن IDs الوسائط موجودة
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ];
    }
}
