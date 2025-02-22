<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\Admin\PicbookController as AdminPicbookController;
use App\Http\Controllers\Api\PicbookController;
use App\Http\Controllers\Api\Admin\PicbookVariantController;
use App\Http\Controllers\Api\Admin\PicbookTranslationController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\PicbookPageController;
use App\Http\Controllers\Api\Admin\PicbookPageVariantController;

// 图片处理路由
Route::prefix('image')->group(function () {
    Route::post('add-text', [ImageController::class, 'addText']);
    Route::post('preview', [ImageController::class, 'preview']);
    Route::post('merge', [ImageController::class, 'merge']);
});

// AI换脸相关API（暂未启用）
Route::middleware(['web', 'auth'])->prefix('aiface')->group(function () {
    // Route::post('upload', [AiFaceController::class, 'upload']);
    // Route::post('process', [AiFaceController::class, 'process']);
    // Route::get('status/{taskId}', [AiFaceController::class, 'status']);
});

// 语言切换路由
Route::prefix('language')->group(function () {
    Route::post('switch', [LanguageController::class, 'switch']);
    Route::get('current', [LanguageController::class, 'current']);
});

/*
|--------------------------------------------------------------------------
| 前台 API Routes
|--------------------------------------------------------------------------
*/

// 前台认证路由
Route::prefix('auth')->group(function () {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);
    Route::middleware(['auth:sanctum', 'check.user.type'])->group(function () {
        Route::post('logout', [UserAuthController::class, 'logout']);
        Route::get('me', [UserAuthController::class, 'me']);
    });
});

// 前台用户管理路由
Route::prefix('user')->middleware(['auth:sanctum'])->group(function () {
    Route::put('profile', [UserAuthController::class, 'updateProfile']);
    Route::put('password', [UserAuthController::class, 'updatePassword']);
});

// 前台API V1版本路由组
Route::prefix('v1')->group(function () {
    // 绘本相关路由
    Route::prefix('picbooks')->group(function () {
        Route::get('/', [PicbookController::class, 'index']);
        Route::get('/{id}', [PicbookController::class, 'show']);
        Route::get('/{id}/options', [PicbookController::class, 'options']);
        Route::get('/{id}/variant', [PicbookController::class, 'getVariant']);
        Route::get('/{id}/pages', [PicbookController::class, 'getPages']);
    });
});

/*
|--------------------------------------------------------------------------
| 后台 API Routes
|--------------------------------------------------------------------------
*/

// 后台认证路由（不带版本号）
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::middleware(['auth:sanctum', 'check.user.type:admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
    });
});

// 后台API V1版本路由组
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'check.user.type:admin'])->group(function () {
    // 用户管理路由
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::post('{id}/disable', [AdminUserController::class, 'disable']);
        Route::post('{id}/enable', [AdminUserController::class, 'enable']);
    });

    // 绘本管理路由
    Route::apiResource('picbooks', AdminPicbookController::class);
    
    // 绘本变体管理路由
    Route::prefix('picbook_variants')->group(function () {
        Route::get('/', [PicbookVariantController::class, 'index']);
        Route::post('/', [PicbookVariantController::class, 'store']);
        Route::get('/{id}', [PicbookVariantController::class, 'show']);
        Route::put('/{id}', [PicbookVariantController::class, 'update']);
        Route::delete('/{id}', [PicbookVariantController::class, 'destroy']);
        Route::post('/batch', [PicbookVariantController::class, 'batchStore']);
    });

    // 绘本页面管理路由
    Route::prefix('picbook-pages')->group(function () {
        Route::get('/', [PicbookPageController::class, 'index']);
        Route::post('/', [PicbookPageController::class, 'store']);
        Route::get('/{id}', [PicbookPageController::class, 'show']);
        Route::put('/{id}', [PicbookPageController::class, 'update']);
        Route::delete('/{id}', [PicbookPageController::class, 'destroy']);
    });

    // 绘本页面变体管理路由
    Route::prefix('picbook-page-variants')->group(function () {
        Route::get('/', [PicbookPageVariantController::class, 'index']);
        Route::post('/', [PicbookPageVariantController::class, 'store']);
        Route::get('/{id}', [PicbookPageVariantController::class, 'show']);
        Route::put('/{id}', [PicbookPageVariantController::class, 'update']);
        Route::delete('/{id}', [PicbookPageVariantController::class, 'destroy']);
    });
});
