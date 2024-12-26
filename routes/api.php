<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\PersonalizeController;
use App\Http\Controllers\PreviewController;

// 获取分类信息
Route::get('/categories', [IndexController::class, 'categories']);

//获取所有图书
Route::get('/books', [BookController::class, 'getAllBooks']);

// 获取书籍详情
Route::get('/books/{id}', [BookController::class, 'showBook']);

// 个性化设置
Route::get('/personalize', [PersonalizeController::class, 'showCharacterForm']);
Route::post('/personalize/save', [PersonalizeController::class, 'saveCharacterInfo']);
Route::get('/personalize/preview/{bookid}', [PreviewController::class, 'previewBook']);
