<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PicbookVariantController extends Controller
{
    public function store(Request $request)
    {
        // 实现创建变体的逻辑
    }

    public function update(Request $request, $variant)
    {
        // 实现更新变体的逻辑
    }

    public function destroy($variant)
    {
        // 实现删除变体的逻辑
    }

    public function activate($variant)
    {
        // 实现激活变体的逻辑
    }

    public function deactivate($variant)
    {
        // 实现停用变体的逻辑
    }
} 