@extends('admin.layouts.admin')

@section('title', 'المشاريع')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة المشاريع</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-700">قائمة المشاريع</h2>
        {{-- هنا يمكن إضافة زر لإنشاء مشروع جديد إذا أردت، لكن المشاريع تُنشأ من واجهة المستخدم عادة --}}
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>العنوان</th>
                    <th>المالك</th>
                    <th>الفئات</th>
                    <th>الوسوم</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr>
                        <td>{{ $project->id }}</td>
                        <td>{{ $project->title }}</td>
                        <td><a href="{{ route('admin.users.edit', $project->user->id) }}" class="text-blue-600 hover:underline">{{ $project->user->name }}</a></td>
                        <td>
                            @forelse ($project->categories as $category)
                                <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $category->name }}</span>
                            @empty
                                <span class="text-gray-500 text-xs">لا يوجد</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse ($project->tags as $tag)
                                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $tag->name }}</span>
                            @empty
                                <span class="text-gray-500 text-xs">لا يوجد</span>
                            @endforelse
                        </td>
                        <td>{{ $project->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-primary">عرض</a>
                            <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-info">تعديل</a>
                            <form action="{{ route('admin.projects.delete', $project->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟ جميع الوسائط والتعليقات والإعجابات ستُحذف أيضاً.');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">لا توجد مشاريع لعرضها.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</div>
@endsection
