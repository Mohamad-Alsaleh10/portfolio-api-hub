@extends('admin.layouts.admin')

@section('title', 'تعديل المشروع: ' . $project->title)

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">تعديل المشروع: {{ $project->title }}</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">العنوان:</label>
            <input type="text" name="title" id="title" class="form-input" value="{{ old('title', $project->title) }}" required>
            @error('title')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">الوصف:</label>
            <textarea name="description" id="description" rows="5" class="form-textarea" required>{{ old('description', $project->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <h3 class="text-xl font-semibold text-gray-700 mb-2">الوسائط الحالية:</h3>
            @if($project->media->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                    @foreach ($project->media as $media)
                        <div class="relative group border rounded-lg overflow-hidden">
                            @if($media->file_type === 'image')
                                <img src="{{ Storage::url($media->file_path) }}" alt="{{ $project->title }}" class="w-full h-32 object-cover">
                            @else
                                <video src="{{ Storage::url($media->file_path) }}" controls class="w-full h-32 object-cover"></video>
                            @endif
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="text-white text-sm mb-1">ID: {{ $media->id }}</span>
                                <label class="inline-flex items-center text-white text-sm">
                                    <input type="checkbox" name="remove_media_ids[]" value="{{ $media->id }}" class="form-checkbox text-red-500">
                                    <span class="ml-2">حذف</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600 mb-4">لا توجد وسائط لهذا المشروع حالياً.</p>
            @endif
            <label for="new_media" class="block text-gray-700 text-sm font-bold mb-2">إضافة وسائط جديدة:</label>
            <input type="file" name="new_media[]" id="new_media" class="form-input-file" multiple>
            @error('new_media.*')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="category_ids" class="block text-gray-700 text-sm font-bold mb-2">الفئات:</label>
            <select name="category_ids[]" id="category_ids" class="form-multiselect" multiple>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', $project->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_ids')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="tag_ids" class="block text-gray-700 text-sm font-bold mb-2">الوسوم:</label>
            <select name="tag_ids[]" id="tag_ids" class="form-multiselect" multiple>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tag_ids', $project->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
            @error('tag_ids')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="btn btn-primary">تحديث المشروع</button>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection
