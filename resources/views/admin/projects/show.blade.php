@extends('admin.layouts.admin')

@section('title', 'تفاصيل المشروع: ' . $project->title)

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">تفاصيل المشروع: {{ $project->title }}</h1>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <div class="mb-4">
        <p class="text-gray-700"><strong class="font-semibold">المعرف (ID):</strong> {{ $project->id }}</p>
        <p class="text-gray-700"><strong class="font-semibold">العنوان:</strong> {{ $project->title }}</p>
        <p class="text-gray-700"><strong class="font-semibold">Slug:</strong> {{ $project->slug }}</p>
        <p class="text-gray-700"><strong class="font-semibold">الوصف:</strong> {{ $project->description }}</p>
        <p class="text-gray-700"><strong class="font-semibold">المالك:</strong> <a href="{{ route('admin.users.edit', $project->user->id) }}" class="text-blue-600 hover:underline">{{ $project->user->name }}</a></p>
        <p class="text-gray-700"><strong class="font-semibold">تاريخ الإنشاء:</strong> {{ $project->created_at->format('Y-m-d H:i') }}</p>
        <p class="text-gray-700"><strong class="font-semibold">آخر تحديث:</strong> {{ $project->updated_at->format('Y-m-d H:i') }}</p>
    </div>

    <div class="mb-4">
        <h3 class="text-xl font-semibold text-gray-700 mb-2">الفئات:</h3>
        @if($project->categories->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($project->categories as $category)
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $category->name }}</span>
                @endforeach
            </div>
        @else
            <p class="text-gray-600">لا توجد فئات مرتبطة.</p>
        @endif
    </div>

    <div class="mb-4">
        <h3 class="text-xl font-semibold text-gray-700 mb-2">الوسوم:</h3>
        @if($project->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                @foreach ($project->tags as $tag)
                    <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $tag->name }}</span>
                @endforeach
            </div>
        @else
            <p class="text-gray-600">لا توجد وسوم مرتبطة.</p>
        @endif
    </div>

    <div class="mb-4">
        <h3 class="text-xl font-semibold text-gray-700 mb-2">الوسائط:</h3>
        @if($project->media->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($project->media as $media)
                    <div class="relative group">
                        @if($media->file_type === 'image')
                            <img src="{{ Storage::url($media->file_path) }}" alt="{{ $project->title }}" class="w-full h-32 object-cover rounded-lg shadow-sm">
                        @else
                            <video src="{{ Storage::url($media->file_path) }}" controls class="w-full h-32 object-cover rounded-lg shadow-sm"></video>
                        @endif
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg">
                            <span class="text-white text-sm">ID: {{ $media->id }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600">لا توجد وسائط للمشروع.</p>
        @endif
    </div>

    <div class="mb-4">
        <h3 class="text-xl font-semibold text-gray-700 mb-2">التعليقات ({{ $project->comments->count() }}):</h3>
        @if($project->comments->isNotEmpty())
            <div class="space-y-4">
                @foreach ($project->comments as $comment)
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <p class="text-sm text-gray-800"><strong class="font-semibold">{{ $comment->user->name ?? 'مستخدم محذوف' }}:</strong> {{ $comment->content }}</p>
                        <p class="text-xs text-gray-500 mt-1">بتاريخ: {{ $comment->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600">لا توجد تعليقات على هذا المشروع.</p>
        @endif
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-info ml-2">تعديل المشروع</a>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">العودة إلى المشاريع</a>
    </div>
</div>
@endsection
