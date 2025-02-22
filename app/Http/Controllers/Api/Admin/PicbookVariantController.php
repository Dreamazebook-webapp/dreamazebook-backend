<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\Picbook;
use App\Models\PicbookVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * 绘本变体管理控制器
 */
class PicbookVariantController extends ApiController
{
    /**
     * 获取绘本变体列表
     * 
     * @route GET /api/v1/admin/picbook_variants
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'picbook_id' => 'required|integer|exists:picbooks,id'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        $query = PicbookVariant::where('picbook_id', $request->picbook_id);
        
        // 语言筛选
        if ($request->has('language')) {
            $query->where('language', $request->language);
        }

        // 性别筛选
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        // 肤色筛选
        if ($request->has('skincolor')) {
            $query->where('skincolor', $request->skincolor);
        }

        // 状态筛选
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $variants = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($variants, __('picbook.variant_list_success'));
    }

    /**
     * 创建绘本变体
     * 
     * @route POST /api/v1/admin/picbook_variants
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'picbook_id' => 'required|integer|exists:picbooks,id',
            'language' => ['required', 'string', 'size:2'],
            'gender' => 'required|integer|in:1,2',
            'skincolor' => 'required|integer|in:1,2,3',
            'bookname' => 'required|string|max:255',
            'intro' => 'nullable|string',
            'description' => 'nullable|string',
            'cover' => 'required|string',
            'price' => 'required|numeric|min:0',
            'pricesymbol' => 'required|string|max:10',
            'currencycode' => 'required|string|size:3',
            'tags' => 'nullable|array',
            'status' => 'required|integer|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        // 检查变体组合是否已存在
        $exists = PicbookVariant::where([
            'picbook_id' => $request->picbook_id,
            'language' => $request->language,
            'gender' => $request->gender,
            'skincolor' => $request->skincolor
        ])->exists();

        if ($exists) {
            return $this->error(__('picbook.variant_exists'));
        }

        // 检查语言、性别、肤色是否在绘本支持范围内
        $picbook = Picbook::findOrFail($request->picbook_id);
        
        if (!in_array($request->language, $picbook->supported_languages)) {
            return $this->error(__('picbook.language_not_supported'));
        }
        if (!in_array($request->gender, $picbook->supported_genders)) {
            return $this->error(__('picbook.gender_not_supported'));
        }
        if (!in_array($request->skincolor, $picbook->supported_skincolors)) {
            return $this->error(__('picbook.skincolor_not_supported'));
        }

        $variant = PicbookVariant::create($request->all());
        return $this->success($variant, __('picbook.variant_create_success'));
    }

    /**
     * 获取绘本变体详情
     * 
     * @route GET /api/v1/admin/picbook_variants/{id}
     */
    public function show($id)
    {
        $variant = PicbookVariant::with('picbook')->findOrFail($id);
        return $this->success($variant, __('picbook.variant_detail_success'));
    }

    /**
     * 更新绘本变体
     * 
     * @route PUT /api/v1/admin/picbook_variants/{id}
     */
    public function update(Request $request, $id)
    {
        $variant = PicbookVariant::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'language' => ['string', 'size:2', Rule::unique('picbook_variants')
                ->where('picbook_id', $variant->picbook_id)
                ->where('gender', $request->input('gender', $variant->gender))
                ->where('skincolor', $request->input('skincolor', $variant->skincolor))
                ->ignore($variant->id)],
            'gender' => ['integer', 'in:1,2', Rule::unique('picbook_variants')
                ->where('picbook_id', $variant->picbook_id)
                ->where('language', $request->input('language', $variant->language))
                ->where('skincolor', $request->input('skincolor', $variant->skincolor))
                ->ignore($variant->id)],
            'skincolor' => ['integer', 'in:1,2,3', Rule::unique('picbook_variants')
                ->where('picbook_id', $variant->picbook_id)
                ->where('language', $request->input('language', $variant->language))
                ->where('gender', $request->input('gender', $variant->gender))
                ->ignore($variant->id)],
            'bookname' => 'string|max:255',
            'intro' => 'nullable|string',
            'description' => 'nullable|string',
            'cover' => 'string',
            'price' => 'numeric|min:0',
            'pricesymbol' => 'string|max:10',
            'currencycode' => 'string|size:3',
            'tags' => 'nullable|array',
            'status' => 'integer|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        // 如果要更改语言、性别或肤色，需要检查是否在绘本支持范围内
        if ($request->has('language') || $request->has('gender') || $request->has('skincolor')) {
            $picbook = $variant->picbook;
            
            if ($request->has('language') && !in_array($request->language, $picbook->supported_languages)) {
                return $this->error(__('picbook.language_not_supported'));
            }
            if ($request->has('gender') && !in_array($request->gender, $picbook->supported_genders)) {
                return $this->error(__('picbook.gender_not_supported'));
            }
            if ($request->has('skincolor') && !in_array($request->skincolor, $picbook->supported_skincolors)) {
                return $this->error(__('picbook.skincolor_not_supported'));
            }
        }

        $variant->update($request->all());
        return $this->success($variant, __('picbook.variant_update_success'));
    }

    /**
     * 删除绘本变体
     * 
     * @route DELETE /api/v1/admin/picbook_variants/{id}
     */
    public function destroy($id)
    {
        $variant = PicbookVariant::findOrFail($id);
        $variant->delete();
        return $this->success(null, __('picbook.variant_delete_success'));
    }

    /**
     * 批量创建绘本变体
     * 
     * @route POST /api/v1/admin/picbook_variants/batch
     */
    public function batchStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'picbook_id' => 'required|integer|exists:picbooks,id',
            'variants' => 'required|array',
            'variants.*.language' => 'required|string|size:2',
            'variants.*.gender' => 'required|integer|in:1,2',
            'variants.*.skincolor' => 'required|integer|in:1,2,3',
            'variants.*.bookname' => 'required|string|max:255',
            'variants.*.intro' => 'nullable|string',
            'variants.*.description' => 'nullable|string',
            'variants.*.cover' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.pricesymbol' => 'required|string|max:10',
            'variants.*.currencycode' => 'required|string|size:3',
            'variants.*.tags' => 'nullable|array',
            'variants.*.status' => 'required|integer|in:0,1,2'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $picbook = Picbook::findOrFail($request->picbook_id);
        $variants = collect($request->variants);

        // 检查所有变体的语言、性别、肤色是否都在支持范围内
        foreach ($variants as $variant) {
            if (!in_array($variant['language'], $picbook->supported_languages)) {
                return $this->error(__('picbook.language_not_supported') . ': ' . $variant['language']);
            }
            if (!in_array($variant['gender'], $picbook->supported_genders)) {
                return $this->error(__('picbook.gender_not_supported') . ': ' . $variant['gender']);
            }
            if (!in_array($variant['skincolor'], $picbook->supported_skincolors)) {
                return $this->error(__('picbook.skincolor_not_supported') . ': ' . $variant['skincolor']);
            }
        }

        // 检查变体组合是否有重复
        $combinations = $variants->map(function ($variant) {
            return $variant['language'] . '-' . $variant['gender'] . '-' . $variant['skincolor'];
        });

        if ($combinations->unique()->count() !== $combinations->count()) {
            return $this->error(__('picbook.variant_combinations_duplicate'));
        }

        // 检查变体组合是否已存在
        foreach ($variants as $variant) {
            $exists = PicbookVariant::where([
                'picbook_id' => $request->picbook_id,
                'language' => $variant['language'],
                'gender' => $variant['gender'],
                'skincolor' => $variant['skincolor']
            ])->exists();

            if ($exists) {
                return $this->error(__('picbook.variant_exists') . ': ' . 
                    $variant['language'] . '-' . $variant['gender'] . '-' . $variant['skincolor']);
            }
        }

        // 批量创建变体
        $createdVariants = collect();
        foreach ($variants as $variant) {
            $variant['picbook_id'] = $request->picbook_id;
            $createdVariants->push(PicbookVariant::create($variant));
        }

        return $this->success($createdVariants, __('picbook.variant_batch_create_success'));
    }
} 