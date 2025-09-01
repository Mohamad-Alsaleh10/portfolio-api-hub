@extends('admin.layouts.admin')

@section('title', 'إدارة التعليقات')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة التعليقات</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-700">قائمة التعليقات</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>محتوى التعليق</th>
                    <th>المستخدم</th>
                    <th>المشروع</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comments as $comment)
                    <tr>
                        <td>{{ $comment->id }}</td>
                        <td>{{ Str::limit($comment->content, 100) }}</td> {{-- عرض جزء من التعليق --}}
                        <td>{{ $comment->user->name ?? 'مستخدم محذوف' }}</td> {{-- اسم المعلق --}}
                        <td>
                            @if ($comment->project)
                                <a href="{{ route('admin.projects.index') }}" class="text-blue-500 hover:underline">
                                    {{ Str::limit($comment->project->title, 50) }}
                                </a>
                            @else
                                مشروع محذوف
                            @endif
                        </td>
                        <td>{{ $comment->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التعليق؟');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">لا توجد تعليقات لعرضها.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $comments->links() }} {{-- لعرض روابط التصفح (pagination) --}}
    </div>
</div>
@endsection
