@extends('admin.layouts.admin')

@section('title', isset($user) ? 'تعديل المستخدم' : 'إنشاء مستخدم')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">{{ isset($user) ? 'تعديل المستخدم: ' . $user->name : 'إنشاء مستخدم جديد' }}</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="{{ isset($user) ? route('admin.users.update', $user->id) : '#' }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($user))
            @method('PUT')
        @endif

        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">الاسم:</label>
            <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $user->name ?? '') }}" required>
            @error('name')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">البريد الإلكتروني:</label>
            <input type="email" name="email" id="email" class="form-input" value="{{ old('email', $user->email ?? '') }}" required>
            @error('email')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="role" class="block text-gray-700 text-sm font-bold mb-2">الدور:</label>
            <select name="role" id="role" class="form-select" required>
                <option value="user" {{ (old('role', $user->role ?? '') == 'user') ? 'selected' : '' }}>مستخدم</option>
                <option value="admin" {{ (old('role', $user->role ?? '') == 'admin') ? 'selected' : '' }}>مدير</option>
            </select>
            @error('role')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="bio" class="block text-gray-700 text-sm font-bold mb-2">السيرة الذاتية:</label>
            <textarea name="bio" id="bio" rows="5" class="form-textarea">{{ old('bio', $user->bio ?? '') }}</textarea>
            @error('bio')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="profile_picture" class="block text-gray-700 text-sm font-bold mb-2">الصورة الشخصية:</label>
            @if(isset($user) && $user->profile_picture)
                <div class="mb-2">
                    <img src="{{ Storage::url($user->profile_picture) }}" alt="صورة الملف الشخصي" class="w-20 h-20 object-cover rounded-full">
                    <label class="inline-flex items-center mt-2">
                        <input type="checkbox" name="remove_profile_picture" value="1" class="form-checkbox">
                        <span class="ml-2 text-gray-700">إزالة الصورة الحالية</span>
                    </label>
                </div>
            @endif
            <input type="file" name="profile_picture" id="profile_picture" class="form-input-file">
            @error('profile_picture')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">كلمة المرور (اتركها فارغة لعدم التغيير):</label>
            <input type="password" name="password" id="password" class="form-input">
            @error('password')
                <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">تأكيد كلمة المرور:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="btn btn-primary">
                {{ isset($user) ? 'تحديث المستخدم' : 'إنشاء مستخدم' }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection
