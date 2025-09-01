@extends('admin.layouts.admin')

@section('title', 'إدارة الفئات')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة الفئات</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-700">قائمة الفئات</h2>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-success">إضافة فئة جديدة</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>الاسم</th>
                    <th>Slug</th>
                    <th>الوصف</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ Str::limit($category->description, 70) }}</td>
                        <td>{{ $category->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary">تعديل</a>
                            <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفئة؟ سيؤثر هذا على المشاريع المرتبطة بها.');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">لا توجد فئات لعرضها.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $categories->links() }} {{-- لعرض روابط التصفح (pagination) --}}
    </div>
</div>
@endsection
