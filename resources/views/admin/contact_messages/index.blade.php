@extends('admin.layouts.admin')

@section('title', 'رسائل الاتصال')

@section('content')
<h1 class="text-3xl font-bold text-gray-800 mb-6">إدارة رسائل الاتصال</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-semibold text-gray-700">قائمة رسائل الاتصال</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الرسالة</th>
                    <th>تاريخ الإرسال</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr>
                        <td>{{ $message->id }}</td>
                        <td>{{ $message->name ?? 'غير محدد' }}</td> {{-- قد يكون الاسم null --}}
                        <td>{{ $message->email }}</td>
                        <td>{{ Str::limit($message->message, 150) }}</td> {{-- عرض جزء من الرسالة --}}
                        <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form action="{{ route('admin.contact_messages.delete', $message->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">لا توجد رسائل اتصال لعرضها.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $messages->links() }} {{-- لعرض روابط التصفح (pagination) --}}
    </div>
</div>
@endsection
