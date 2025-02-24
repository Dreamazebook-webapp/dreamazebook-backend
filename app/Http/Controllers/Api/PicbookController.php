<?php

namespace App\Http\Controllers\Api;

use App\Models\Picbook;
use App\Models\PicbookPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PicbookController extends ApiController
{
    /**
     * 获取绘本列表
     */
    public function index(Request $request)
    {
        $query = Picbook::where('status', 1); // 只显示已发布的绘本
        
        // 搜索条件
        if ($request->has('keyword')) {
            $query->where('default_name', 'like', '%' . $request->keyword . '%');
        }
        
        // 标签筛选
        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        // 语言筛选
        if ($request->has('language')) {
            $query->whereJsonContains('supported_languages', $request->language);
        }

        // 价格范围筛选
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // 货币筛选
        if ($request->has('currencycode')) {
            $query->where('currencycode', $request->currencycode);
        }

        // 选择类型筛选
        if ($request->has('choices_type')) {
            $query->where('choices_type', $request->choices_type);
        }

        // 排序
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // 只允许特定字段排序
        $allowedSortFields = ['created_at', 'price', 'rating'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $picbooks = $query->paginate($request->input('per_page', 15));

        return $this->success($picbooks, __('messages.picbook.list_success'));
    }

    /**
     * 获取绘本详情
     */
    public function show(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required|string|size:2',
            'gender' => 'required|integer|in:1,2',
            'skincolor' => 'required|integer|in:1,2,3'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        $picbook = Picbook::where('status', 1)->findOrFail($id);

        // 验证请求的参数是否在支持范围内
        if (!in_array($request->language, $picbook->supported_languages)) {
            return $this->error('不支持的语言');
        }
        if (!in_array($request->gender, $picbook->supported_genders)) {
            return $this->error('不支持的性别');
        }
        if (!in_array($request->skincolor, $picbook->supported_skincolors)) {
            return $this->error('不支持的肤色');
        }

        // 获取绘本页面及其变体
        $pages = PicbookPage::where('picbook_id', $id)
            ->where('status', 1)
            ->with(['variants' => function ($query) use ($request) {
                $query->where([
                    'language' => $request->language,
                    'gender' => $request->gender,
                    'skincolor' => $request->skincolor
                ]);
            }])
            ->orderBy('page_number')
            ->get();

        $picbook->pages = $pages;
        $picbook->choice_pages_count = $picbook->choice_pages_count;

        return $this->success($picbook, __('messages.picbook.detail_success'));
    }

    /**
     * 获取绘本支持的配置选项
     */
    public function options($id)
    {
        $picbook = Picbook::where('status', 1)->findOrFail($id);
        
        return $this->success([
            'supported_languages' => $picbook->supported_languages,
            'supported_genders' => $picbook->supported_genders,
            'supported_skincolors' => $picbook->supported_skincolors,
            'price' => [
                'amount' => $picbook->price,
                'symbol' => $picbook->pricesymbol,
                'currency' => $picbook->currencycode
            ],
            'choices_type' => $picbook->choices_type,
            'choice_pages_count' => $picbook->choice_pages_count
        ], __('messages.picbook.options_success'));
    }
} 