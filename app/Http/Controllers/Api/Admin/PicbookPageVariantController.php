<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\PicbookPage;
use App\Models\PicbookPageVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PicbookPageVariantController extends ApiController
{
    /**
     * 获取绘本页面变体列表
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_id' => 'required|integer|exists:picbook_pages,id'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        $query = PicbookPageVariant::where('page_id', $request->page_id);
        
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

        $variants = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->success($variants, __('picbook.page_variant_list_success'));
    }

    /**
     * 创建绘本页面变体
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_id' => 'required|integer|exists:picbook_pages,id',
            'language' => 'required|string|size:2',
            'gender' => 'required|integer|in:1,2',
            'skincolor' => 'required|integer|in:1,2,3',
            'image_url' => 'nullable|string',
            'content' => 'required|string',
            'choice_options' => 'nullable|array',
            'question' => 'nullable|string|required_with:choice_options'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        // 检查变体组合是否已存在
        $exists = PicbookPageVariant::where([
            'page_id' => $request->page_id,
            'language' => $request->language,
            'gender' => $request->gender,
            'skincolor' => $request->skincolor
        ])->exists();

        if ($exists) {
            return $this->error(__('picbook.page_variant_exists'));
        }

        // 检查语言、性别、肤色是否在绘本支持范围内
        $page = PicbookPage::with('picbook')->findOrFail($request->page_id);
        $picbook = $page->picbook;

        if (!in_array($request->language, $picbook->supported_languages)) {
            return $this->error(__('picbook.language_not_supported'));
        }
        if (!in_array($request->gender, $picbook->supported_genders)) {
            return $this->error(__('picbook.gender_not_supported'));
        }
        if (!in_array($request->skincolor, $picbook->supported_skincolors)) {
            return $this->error(__('picbook.skincolor_not_supported'));
        }

        // 如果页面有选择题，变体必须提供选项
        if ($page->is_choices && empty($request->choice_options)) {
            return $this->error(__('picbook.choice_options_required'));
        }

        $variant = PicbookPageVariant::create($request->all());
        return $this->success($variant, __('picbook.page_variant_create_success'));
    }

    /**
     * 获取绘本页面变体详情
     */
    public function show($id)
    {
        $variant = PicbookPageVariant::with('page.picbook')->findOrFail($id);
        return $this->success($variant, __('picbook.page_variant_detail_success'));
    }

    /**
     * 更新绘本页面变体
     */
    public function update(Request $request, $id)
    {
        $variant = PicbookPageVariant::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'language' => 'string|size:2',
            'gender' => 'integer|in:1,2',
            'skincolor' => 'integer|in:1,2,3',
            'image_url' => 'nullable|string',
            'content' => 'string',
            'choice_options' => 'nullable|array',
            'question' => 'nullable|string|required_with:choice_options'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        // 如果要更改语言、性别或肤色，需要检查组合是否已存在
        if ($request->has('language') || $request->has('gender') || $request->has('skincolor')) {
            $exists = PicbookPageVariant::where([
                'page_id' => $variant->page_id,
                'language' => $request->input('language', $variant->language),
                'gender' => $request->input('gender', $variant->gender),
                'skincolor' => $request->input('skincolor', $variant->skincolor)
            ])
            ->where('id', '!=', $id)
            ->exists();

            if ($exists) {
                return $this->error(__('picbook.page_variant_exists'));
            }

            // 检查是否在绘本支持范围内
            $page = PicbookPage::with('picbook')->findOrFail($variant->page_id);
            $picbook = $page->picbook;

            if ($request->has('language') && !in_array($request->language, $picbook->supported_languages)) {
                return $this->error(__('picbook.language_not_supported'));
            }
            if ($request->has('gender') && !in_array($request->gender, $picbook->supported_genders)) {
                return $this->error(__('picbook.gender_not_supported'));
            }
            if ($request->has('skincolor') && !in_array($request->skincolor, $picbook->supported_skincolors)) {
                return $this->error(__('picbook.skincolor_not_supported'));
            }

            // 如果页面有选择题，变体必须提供选项
            if ($page->is_choices && $request->has('choice_options') && empty($request->choice_options)) {
                return $this->error(__('picbook.choice_options_required'));
            }
        }

        $variant->update($request->all());
        return $this->success($variant, __('picbook.page_variant_update_success'));
    }

    /**
     * 删除绘本页面变体
     */
    public function destroy($id)
    {
        $variant = PicbookPageVariant::findOrFail($id);
        $variant->delete();
        return $this->success(null, __('picbook.page_variant_delete_success'));
    }
} 