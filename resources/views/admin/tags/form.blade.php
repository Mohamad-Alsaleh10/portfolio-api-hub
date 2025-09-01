@extends('admin.layouts.admin')

@section('title', isset($tag) ? 'تعديل الوسم: ' . $tag->name : 'إضافة وسم جديد')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">{{ isset($tag) ? 'تعديل الوسم' : 'إضافة وسم جديد' }}</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="{{ isset($tag) ? route('admin.tags.update', $tag->id) : route('admin.tags.store') }}" method="POST">
        @csrf
        @if(isset($tag))
            @method('PUT') {{-- استخدام PUT لعمليات التعديل --}}
        @endif

        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">اسم الوسم:</label>
            <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('name', $tag->name ?? '') }}" required>
            @error('name')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="slug" class="block text-gray-700 text-sm font-bold mb-2">Slug (اختياري، يولد تلقائيًا إذا ترك فارغًا):</label>
            <input type="text" name="slug" id="slug" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('slug', $tag->slug ?? '') }}">
            @error('slug')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="btn btn-primary">
                {{ isset($tag) ? 'تحديث الوسم' : 'إضافة الوسم' }}
            </button>
            <a href="{{ route('admin.tags.index') }}" class="btn bg-gray-500 hover:bg-gray-700">إلغاء</a>
        </div>
    </form>
</div>
@endsection
