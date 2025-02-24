<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\Picbook;
use App\Models\PicbookVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PicbookController extends ApiController
{
    /**
     * 获取绘本列表
     */
    public function index(Request $request)
    {
        //per_page和page
        $per_page = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        //验证请求
        $validator = Validator::make($request->all(), [
            'keyword' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1,2',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'currencycode' => 'nullable|string|size:3',
            'per_page' => 'nullable|integer|min:15|max:100',
            'page' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }
        $query = Picbook::query();
        
        // 搜索条件
        if ($request->has('keyword')) {
            $query->where('default_name', 'like', '%' . $request->keyword . '%');
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
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

        $picbooks = $query->orderBy('created_at', 'desc')
            ->paginate($per_page, ['id', 'default_name', 'default_cover', 'price', 'currencycode', 'status','price','rating','has_choices','has_qa','supported_languages','supported_genders','supported_skincolors','none_skin','tags'], 'page', $page);
        return $this->success($picbooks, __('picbook.list_success'));
    }

    /**
     * 创建绘本
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_name' => 'required|string|max:255',
            'default_cover' => 'required|string',
            'pricesymbol' => 'required|string|max:10',
            'price' => 'required|numeric|min:0',
            'currencycode' => 'required|string|size:3',
            'total_pages' => 'required|integer|min:1',
            'supported_languages' => 'required|array',
            'supported_genders' => 'required|array',
            'supported_skincolors' => 'required|array',
            'none_skin' => 'nullable|string',
            'tags' => 'nullable|array',
            'has_choices' => 'boolean',
            'has_qa' => 'boolean',
            'status' => 'required|integer|in:0,1,2',
            'choices_type' => 'required|integer|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        // 验证选择类型与总页数的关系
        if ($request->choices_type > 0) {
            $required_pages = $request->choices_type == 1 ? 8 : 16;
            $type_name = __('picbook.choices_type.type_names.' . $request->choices_type);
            if ($request->total_pages < $required_pages) {
                return $this->error(
                    __('validation.failed'),
                    ['total_pages' => [__('picbook.choices_type.min_pages_error', [
                        'type' => $type_name,
                        'pages' => $required_pages
                    ])]],
                    422
                );
            }
        }

        $picbook = Picbook::create($request->all());
        return $this->success($picbook);
    }

    /**
     * 获取绘本详情
     */
    public function show($id)
    {
        $picbook = Picbook::findOrFail($id);
        //获取变体数量
        $variants = PicbookVariant::where('picbook_id', $id)->count();
        $picbook->variants_count = $variants;
        return $this->success($picbook);
    }

    /**
     * 更新绘本
     */
    public function update(Request $request, $id)
    {
        $picbook = Picbook::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'default_name' => 'string|max:255',
            'default_cover' => 'string',
            'pricesymbol' => 'string|max:10',
            'price' => 'numeric|min:0',
            'currencycode' => 'string|size:3',
            'total_pages' => 'integer|min:1',
            'supported_languages' => 'array',
            'supported_genders' => 'array',
            'supported_skincolors' => 'array',
            'none_skin' => 'nullable|string',
            'tags' => 'nullable|array',
            'has_choices' => 'boolean',
            'has_qa' => 'boolean',
            'status' => 'integer|in:0,1,2',
            'choices_type' => 'integer|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        // 验证选择类型与总页数的关系
        if ($request->has('choices_type') && $request->choices_type > 0) {
            $total_pages = $request->input('total_pages', $picbook->total_pages);
            $required_pages = $request->choices_type == 1 ? 8 : 16;
            $type_name = __('picbook.choices_type.type_names.' . $request->choices_type);
            if ($total_pages < $required_pages) {
                return $this->error(
                    __('validation.failed'),
                    ['total_pages' => [__('picbook.choices_type.min_pages_error', [
                        'type' => $type_name,
                        'pages' => $required_pages
                    ])]],
                    422
                );
            }
        }

        $picbook->update($request->all());
        return $this->success($picbook);
    }

    /**
     * 删除绘本
     */
    public function destroy($id)
    {
        $picbook = Picbook::findOrFail($id);
        $picbook->delete();
        return $this->success(null, '删除成功');
    }
} 