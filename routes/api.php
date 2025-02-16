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

//new
// 图片处理路由
Route::post('/image/add-text', [ImageController::class, 'addText']);
Route::post('/image/preview', [ImageController::class, 'preview']);
Route::post('/image/merge', [ImageController::class, 'merge']);

Route::middleware(['web', 'auth'])->group(function () {
    // AI换脸相关API
    // Route::post('/aiface/upload', [AiFaceController::class, 'upload']);
    // Route::post('/aiface/process', [AiFaceController::class, 'process']);
    // Route::get('/aiface/status/{taskId}', [AiFaceController::class, 'status']);
});

// 前台认证路由
// Route::prefix('auth')->group(function () {
//     Route::post('login', [LoginController::class, 'login']);
//     Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
//     Route::get('me', [LoginController::class, 'me'])->middleware('auth:sanctum');
// });

// User Auth Routes
Route::post('/auth/register', [UserAuthController::class, 'register']);
Route::post('/auth/login', [UserAuthController::class, 'login']);
Route::middleware(['auth:sanctum', 'check.user.type'])->group(function () {
    Route::post('/auth/logout', [UserAuthController::class, 'logout']);
    // Add other user routes here
});

// 前台文件上传路由
Route::prefix('files')->middleware(['auth:sanctum'])->group(function () {
    // Route::post('upload', [FileController::class, 'userUpload']);
    // Route::delete('delete', [FileController::class, 'destroy']);
});

// 前台其他路由...
Route::prefix('picbooks')->group(function () {
    Route::get('/', [PicbookController::class, 'index']);
    Route::get('/{picbook}', [PicbookController::class, 'show']);
    Route::get('/{picbook}/variant', [PicbookController::class, 'getVariant']);
    Route::get('/{picbook}/pages', [PicbookController::class, 'getPages']);
});

// 所有需要管理员权限的路由
Route::prefix('admin')->group(function () {
    // Admin Auth Routes
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::middleware(['auth:sanctum', 'check.user.type:admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'adminMe']);
        // Add other admin routes here
        //后台绘本管理路由
        Route::prefix('picbooks')->group(function () {
            Route::get('trashed', [AdminPicbookController::class, 'trashed']);
            // Route::post('{id}/restore', [AdminPicbookController::class, 'restore']);
            // Route::delete('{id}/force', [AdminPicbookController::class, 'forceDelete']);
            Route::apiResource('', AdminPicbookController::class)->parameters(['' => 'picbook']);
        });

        // 绘本变体管理路由
        Route::prefix('picbooks/{picbook}/variants')->group(function () {
            Route::post('/', [PicbookVariantController::class, 'store']);
            Route::put('{variant}', [PicbookVariantController::class, 'update']);
            Route::delete('{variant}', [PicbookVariantController::class, 'destroy']);
            Route::post('{variant}/activate', [PicbookVariantController::class, 'activate']);
            Route::post('{variant}/deactivate', [PicbookVariantController::class, 'deactivate']);
        });

        // 绘本翻译管理路由
        Route::prefix('picbooks/{picbook}/translations')->group(function () {
            Route::post('/', [PicbookTranslationController::class, 'store']);
            Route::put('{translation}', [PicbookTranslationController::class, 'update']);
            Route::delete('{translation}', [PicbookTranslationController::class, 'destroy']);
        });
    });
    // 后台文件上传路由
    // Route::prefix('files')->group(function () {
    //     Route::post('upload', [FileController::class, 'adminUpload']);
    //     Route::delete('delete', [FileController::class, 'destroy']);
    // });

    //后台绘本管理路由
    // Route::prefix('picbooks')->middleware(['auth:sanctum', 'check.user.type:admin'])->group(function () {
    //     Route::get('trashed', [AdminPicbookController::class, 'trashed']);
    //     Route::post('{id}/restore', [AdminPicbookController::class, 'restore']);
    //     Route::delete('{id}/force', [AdminPicbookController::class, 'forceDelete']);
    //     Route::apiResource('/', AdminPicbookController::class);
    // });

    // // 后台绘本页面管理路由
    // Route::prefix('picbook-pages')->group(function () {
    //     Route::get('/{picbook}/pages', [AdminPicbookPageController::class, 'index']);
    //     Route::post('/{picbook}/pages', [AdminPicbookPageController::class, 'store']);
    //     Route::put('/pages/{page}', [AdminPicbookPageController::class, 'update']);
    //     Route::delete('/pages/{page}', [AdminPicbookPageController::class, 'destroy']);
    //     Route::put('/pages/{page}/translations/{language}', [AdminPicbookPageController::class, 'updateTranslation']);
    //     Route::post('/pages/{page}/publish', [AdminPicbookPageController::class, 'publish']);
    //     Route::post('/pages/{page}/hide', [AdminPicbookPageController::class, 'hide']);
    //     Route::post('/pages/{page}/variants', [AdminPicbookPageController::class, 'createVariants']);
    // });

    // // 后台绘本变体管理路由
    // Route::prefix('picbook-variants')->group(function () {
    //     Route::post('/', [AdminPicbookVariantController::class, 'store']);
    //     Route::put('/{variant}', [AdminPicbookVariantController::class, 'update']);
    //     Route::delete('/{variant}', [AdminPicbookVariantController::class, 'destroy']);
    //     Route::post('/{variant}/activate', [AdminPicbookVariantController::class, 'activate']);
    //     Route::post('/{variant}/deactivate', [AdminPicbookVariantController::class, 'deactivate']);
    // });
});

// 语言切换路由
Route::prefix('language')->group(function () {
    Route::post('switch', [LanguageController::class, 'switch']);
    Route::get('current', [LanguageController::class, 'current']);
});
