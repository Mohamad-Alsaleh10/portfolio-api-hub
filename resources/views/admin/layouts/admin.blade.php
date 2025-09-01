<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CreativeHub') }} | Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (Breeze default) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FontAwesome for icons (اختياري، يمكنك تضمينه إذا كنت تستخدم أيقونات) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" xintegrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* يمكنك إضافة CSS مخصص هنا للوحة التحكم إذا لزم الأمر */
        body {
            font-family: 'figtree', sans-serif;
            background-color: #f8f8f8;
        }
        .sidebar {
            width: 250px;
            background-color: #2d3748; /* Dark grey */
            color: #cbd5e0; /* Light grey */
            min-height: 100vh;
            padding: 20px;
        }
        .sidebar a {
            color: #cbd5e0;
            padding: 10px 15px;
            display: block;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #4a5568; /* Slightly lighter grey on hover */
        }
        .sidebar .active {
            background-color: #4299e1; /* Blue for active link */
            color: white;
        }
        .header {
            background-color: #fff;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #edf2f7;
        }
        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-primary { background-color: #4299e1; }
        .btn-danger { background-color: #e53e3e; }
        .btn-success { background-color: #48bb78; }
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination li a, .pagination li span {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            text-decoration: none;
            color: #4a5568;
        }
        .pagination li.active span {
            background-color: #4299e1;
            color: white;
            border-color: #4299e1;
        }
        .pagination li a:hover {
            background-color: #edf2f7;
        }
    </style>
</head>
<body>
    <div class="flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2 class="text-2xl font-bold mb-6 text-white">لوحة التحكم</h2>
            <nav>
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-tachometer-alt mr-2"></i>الرئيسية</a></li>
                    <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}"><i class="fas fa-users mr-2"></i>إدارة المستخدمين</a></li>
                    <li><a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.index') ? 'active' : '' }}"><i class="fas fa-folder-open mr-2"></i>إدارة المشاريع</a></li>
                    <li><a href="{{ route('admin.comments.index') }}" class="{{ request()->routeIs('admin.comments.index') ? 'active' : '' }}"><i class="fas fa-comments mr-2"></i>إدارة التعليقات</a></li>
                    <li><a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.index') ? 'active' : '' }}"><i class="fas fa-list-alt mr-2"></i>الفئات</a></li>
                    <li><a href="{{ route('admin.tags.index') }}" class="{{ request()->routeIs('admin.tags.index') ? 'active' : '' }}"><i class="fas fa-tags mr-2"></i>الوسوم</a></li>
                    <li><a href="{{ route('admin.contact_messages.index') }}" class="{{ request()->routeIs('admin.contact_messages.index') ? 'active' : '' }}"><i class="fas fa-envelope mr-2"></i>رسائل الاتصال</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-400 hover:text-red-300">
                                <i class="fas fa-sign-out-alt mr-2"></i>تسجيل الخروج
                            </a>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header/Navbar -->
            <header class="header flex justify-between items-center">
                <div class="text-xl font-semibold">@yield('title')</div>
                <div>
                    <!-- معلومات المستخدم المسجل دخول كمدير -->
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </header>

            <!-- Page Content -->
            <main class="content">
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
