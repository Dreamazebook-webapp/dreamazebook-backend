<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;

// 主页面渲染
Route::get('/', [IndexController::class, 'index'])->name('home');

// 初始化页面
Route::get('/init', function () {
    return view('init');
});
