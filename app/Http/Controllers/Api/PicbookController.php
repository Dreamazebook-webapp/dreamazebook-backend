<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\App;

class PicbookController extends Controller
{
    use ApiResponse;

    // 前台列表，只显示已发布的绘本
    public function index(Request $request)
    {
        $query = Picbook::query()
            ->where('status', Picbook::STATUS_PUBLISHED);

        // 应用过滤条件
        if ($request->has('tag')) {
            $query->withTag($request->tag);
        }

        // 加载当前语言的翻译
        $query->with(['translations' => function($query) {
            $query->where('language', App::getLocale());
        }]);

        // 分页
        $perPage = $request->input('per_page', 15);
        return $this->success(
            $query->paginate($perPage),
            __('picbook.list_success')
        );
    }

    // 前台详情，只显示基本信息
    public function show(Request $request, Picbook $picbook)
    {
        if ($picbook->status !== Picbook::STATUS_PUBLISHED) {
            return $this->error(__('picbook.not_found'), null, 404);
        }

        // 加载当前语言的翻译
        return $this->success(
            $picbook->load(['translations' => function($query) {
                $query->where('language', App::getLocale());
            }]),
            __('picbook.detail_success')
        );
    }

    // 获取指定语言和特征的变体
    public function getVariant(Request $request, Picbook $picbook)
    {
        if ($picbook->status !== Picbook::STATUS_PUBLISHED) {
            return $this->error(__('picbook.not_found'), null, 404);
        }

        $validator = Validator::make($request->all(), [
            'language' => 'required|string|size:2',
            'gender' => 'required|integer',
            'skincolor' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        // 检查是否支持请求的特征
        if (!$picbook->supportsLanguage($request->language)) {
            return $this->error(__('picbook.language_not_supported'), null, 422);
        }
        if (!$picbook->supportsGender($request->gender)) {
            return $this->error(__('picbook.gender_not_supported'), null, 422);
        }
        if (!$picbook->supportsSkinColor($request->skincolor)) {
            return $this->error(__('picbook.skincolor_not_supported'), null, 422);
        }

        $variant = $picbook->getVariant($request->language, $request->gender, $request->skincolor);
        if (!$variant) {
            return $this->error(__('picbook.variant_not_found'), null, 404);
        }

        return $this->success($variant, __('picbook.variant_success'));
    }

    // 获取绘本的页面及其翻译
    public function getPages(Request $request, Picbook $picbook)
    {
        if ($picbook->status !== Picbook::STATUS_PUBLISHED) {
            return $this->error(__('picbook.not_found'), null, 404);
        }

        $validator = Validator::make($request->all(), [
            'language' => 'required|string|size:2',
            'gender' => 'required|integer',
            'skincolor' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        if (!$picbook->supportsLanguage($request->language)) {
            return $this->error(__('picbook.language_not_supported'), null, 422);
        }
        $pages = $picbook->pages()
            ->where('gender', $request->gender)
            ->where('skincolor', $request->skincolor)
            ->orderBy('page_number')
            ->with(['translations' => function($query) use ($request) {
                $query->where('language', $request->language);
            }])
            ->get();

        return $this->success($pages, __('picbook.pages_success'));
    }
} 