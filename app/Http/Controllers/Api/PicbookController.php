<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PicbookController extends Controller
{
    // 前台列表，只显示已发布的绘本
    public function index(Request $request)
    {
        $query = Picbook::query()
            ->where('status', Picbook::STATUS_PUBLISHED);

        // 应用过滤条件
        if ($request->has('tag')) {
            $query->withTag($request->tag);
        }

        // 加载关联数据
        $query->with(['translations']);

        // 分页
        $perPage = $request->input('per_page', 15);
        return $query->paginate($perPage);
    }

    // 前台详情，只显示基本信息
    public function show(Request $request, Picbook $picbook)
    {
        if ($picbook->status !== Picbook::STATUS_PUBLISHED) {
            return response()->json(['message' => '绘本不存在'], 404);
        }

        return $picbook->load(['translations']);
    }

    // 获取指定语言和特征的变体
    public function getVariant(Request $request, Picbook $picbook)
    {
        if ($picbook->status !== Picbook::STATUS_PUBLISHED) {
            return response()->json(['message' => '绘本不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'language' => 'required|string|size:2',
            'gender' => 'required|integer',
            'skincolor' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 检查是否支持请求的特征
        if (!$picbook->supportsLanguage($request->language)) {
            return response()->json(['message' => '不支持的语言'], 422);
        }
        if (!$picbook->supportsGender($request->gender)) {
            return response()->json(['message' => '不支持的性别'], 422);
        }
        if (!$picbook->supportsSkinColor($request->skincolor)) {
            return response()->json(['message' => '不支持的肤色'], 422);
        }

        $variant = $picbook->getVariant($request->language, $request->gender, $request->skincolor);
        if (!$variant) {
            return response()->json(['message' => '未找到对应的变体'], 404);
        }

        return $variant;
    }

    // 获取绘本的页面及其翻译
    public function getPages(Request $request, Picbook $picbook)
    {
        if ($picbook->status !== Picbook::STATUS_PUBLISHED) {
            return response()->json(['message' => '绘本不存在'], 404);
        }

        $validator = Validator::make($request->all(), [
            'language' => 'required|string|size:2',
            'gender' => 'required|integer',
            'skincolor' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$picbook->supportsLanguage($request->language)) {
            return response()->json(['message' => '不支持的语言'], 422);
        }
        $pages = $picbook->pages()
            ->where('gender', $request->gender)
            ->where('skincolor', $request->skincolor)
            ->orderBy('page_number')
            ->with(['translations' => function($query) use ($request) {
                $query->where('language', $request->language);
            }])
            ->get();

        return $pages;
    }
} 