<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // أي مستخدم مصادق عليه يمكنه إنشاء مشروع
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'media' => ['required', 'array', 'min:1'], // يجب أن يكون هناك ملف وسائط واحد على الأقل
            'media.*' => ['file', 'mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi', 'max:10240'], // لكل ملف: نوعه وحجمه (10MB)
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'], // التأكد من أن الفئات موجودة
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'], // التأكد من أن الوسوم موجودة
        ];
    }
}
