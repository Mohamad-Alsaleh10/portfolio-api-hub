<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController; 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// مسارات المصادقة الأساسية
Route::post('/register', [RegisterController::class, 'register']);

Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LogoutController::class, 'logout']) 
    ->middleware('auth:sanctum'); 

// مسارات المستخدمين والمشاريع والميزات الاجتماعية التي تتطلب مصادقة (Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // جلب وتحديث تفاصيل المستخدم الحالي
    Route::get('/user/profile', [UserController::class, 'show']);
    Route::post('/user/profile', [UserController::class, 'update']);
    
    Route::get('/projects/for-you', [DiscoveryController::class, 'forYouProjects']);

    // API لإدارة المشاريع
    Route::apiResource('projects', ProjectController::class);

    // API للميزات الاجتماعية
    Route::post('/projects/{project}/toggle-like', [SocialController::class, 'toggleLike']);
    Route::post('/projects/{project}/comments', [App\Http\Controllers\Api\CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [App\Http\Controllers\Api\CommentController::class, 'destroy']);
    Route::post('/users/{id}/toggle-follow', [SocialController::class, 'toggleFollow']);


    Route::get('/notifications', [UserController::class, 'getNotifications']);
    Route::post('/notifications/{id}/mark-as-read', [UserController::class, 'markAsRead']);
    
    
});

// المسارات العامة التي لا تتطلب مصادقة (للعرض فقط)
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project:slug}', [ProjectController::class, 'show']);
// API للبحث والاكتشاف (لا تتطلب مصادقة)
Route::get('/search/projects', [DiscoveryController::class, 'searchProjects']);
Route::get('/search/users', [DiscoveryController::class, 'searchUsers']);
Route::get('/trending-projects', [DiscoveryController::class, 'trendingProjects']);
Route::get('/categories', [DiscoveryController::class, 'getCategories']);
Route::get('/categories/{category:slug}/projects', [DiscoveryController::class, 'getProjectsByCategory']);
Route::get('/tags', [DiscoveryController::class, 'getTags']);
Route::get('/tags/{tag:slug}/projects', [DiscoveryController::class, 'getProjectsByTag']);

// API لنموذج الاتصال (لا تتطلب مصادقة)
Route::post('/contact', [ContactController::class, 'submitMessage']);





// Admin Routes - Protected by 'admin' role check
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'index']);
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users']);
    Route::post('/users/{user}/update', [App\Http\Controllers\Admin\AdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'deleteUser']);

    Route::get('/projects', [App\Http\Controllers\Admin\AdminController::class, 'projects']);
    Route::post('/projects/{project}/update', [App\Http\Controllers\Controllers\Admin\AdminController::class, 'updateProject']);
    Route::delete('/projects/{project}', [App\Http\Controllers\Admin\AdminController::class, 'deleteProject']);

    Route::get('/comments', [App\Http\Controllers\Admin\AdminController::class, 'comments']);
    Route::delete('/comments/{comment}', [App\Http\Controllers\Admin\AdminController::class, 'deleteComment']);

    Route::get('/categories', [App\Http\Controllers\Admin\AdminController::class, 'categories']);
    Route::post('/categories', [App\Http\Controllers\Admin\AdminController::class, 'storeCategory']);
    Route::post('/categories/{category}/update', [App\Http\Controllers\Admin\AdminController::class, 'updateCategory']);
    Route::delete('/categories/{category}', [App\Http\Controllers\Admin\AdminController::class, 'deleteCategory']);

    Route::get('/tags', [App\Http\Controllers\Admin\AdminController::class, 'tags']);
    Route::post('/tags', [App\Http\Controllers\Admin\AdminController::class, 'storeTag']);
    Route::post('/tags/{tag}/update', [App\Http\Controllers\Admin\AdminController::class, 'updateTag']);
    Route::delete('/tags/{tag}', [App\Http\Controllers\Admin\AdminController::class, 'deleteTag']);

    Route::get('/contact-messages', [App\Http\Controllers\Admin\AdminController::class, 'contactMessages']);
    Route::delete('/contact-messages/{message}', [App\Http\Controllers\Admin\AdminController::class, 'deleteContactMessage']);

    Route::get('/reports', [App\Http\Controllers\Admin\AdminController::class, 'reports']);
    Route::post('/reports/{report}/accept', [App\Http\Controllers\Admin\AdminController::class, 'acceptReport']);
    Route::post('/reports/{report}/reject', [App\Http\Controllers\Admin\AdminController::class, 'rejectReport']);
});