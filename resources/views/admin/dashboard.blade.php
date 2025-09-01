@extends('admin.layouts.admin')

@section('title', 'الرئيسية')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">لوحة تحكم المدير</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-gray-500">إجمالي المستخدمين</div>
            <div class="text-3xl font-bold text-gray-900">{{ $totalUsers }}</div>
        </div>
        <i class="fas fa-users text-4xl text-blue-500"></i>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-gray-500">إجمالي المشاريع</div>
            <div class="text-3xl font-bold text-gray-900">{{ $totalProjects }}</div>
        </div>
        <i class="fas fa-folder-open text-4xl text-green-500"></i>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-gray-500">إجمالي التعليقات</div>
            <div class="text-3xl font-bold text-gray-900">{{ $totalComments }}</div>
        </div>
        <i class="fas fa-comments text-4xl text-yellow-500"></i>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
        <div>
            <div class="text-gray-500">رسائل اتصال جديدة</div>
            <div class="text-3xl font-bold text-gray-900">{{ $newContactMessages }}</div>
        </div>
        <i class="fas fa-envelope text-4xl text-red-500"></i>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">أحدث المشاريع</h2>
        <ul class="divide-y divide-gray-200">
            @forelse ($latestProjects as $project)
                <li class="py-3 flex justify-between items-center">
                    <div>
                        <a href="#" class="text-blue-600 hover:underline font-medium">{{ $project->title }}</a>
                        <p class="text-sm text-gray-500">بواسطة: {{ $project->user->name ?? 'N/A' }}</p>
                    </div>
                    <span class="text-sm text-gray-500">{{ $project->created_at->diffForHumans() }}</span>
                </li>
            @empty
                <li class="py-3 text-gray-500">لا توجد مشاريع حديثة.</li>
            @endforelse
        </ul>
        <div class="mt-4 text-right">
            <a href="{{ route('admin.projects.index') }}" class="text-blue-500 hover:underline">عرض كل المشاريع <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">أحدث المستخدمين</h2>
        <ul class="divide-y divide-gray-200">
            @forelse ($latestUsers as $user)
                <li class="py-3 flex justify-between items-center">
                    <div>
                        <span class="font-medium">{{ $user->name }}</span>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                    <span class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</span>
                </li>
            @empty
                <li class="py-3 text-gray-500">لا يوجد مستخدمون حديثون.</li>
            @endforelse
        </ul>
        <div class="mt-4 text-right">
            <a href="{{ route('admin.users.index') }}" class="text-blue-500 hover:underline">عرض كل المستخدمين <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
    </div>
</div>
@endsection
