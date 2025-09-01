@extends('admin.layouts.admin')

@section('title', 'إدارة الوسوم')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة الوسوم</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-700">قائمة الوسوم</h2>
        <a href="{{ route('admin.tags.create') }}" class="btn btn-success">إضافة وسم جديد</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>الاسم</th>
                    <th>Slug</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tags as $tag)
                    <tr>
                        <td>{{ $tag->id }}</td>
                        <td>{{ $tag->name }}</td>
                        <td>{{ $tag->slug }}</td>
                        <td>{{ $tag->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.tags.edit', $tag->id) }}" class="btn btn-primary">تعديل</a>
                            <form action="{{ route('admin.tags.delete', $tag->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الوسم؟ سيؤثر هذا على المشاريع المرتبطة به.');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">لا توجد وسوم لعرضها.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tags->links() }} {{-- لعرض روابط التصفح (pagination) --}}
    </div>
</div>
@endsection
