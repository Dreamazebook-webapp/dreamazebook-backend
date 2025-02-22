<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\PicbookPage;
use App\Models\PicbookPageVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PicbookPageController extends ApiController
{
    /**
     * 获取绘本页面列表
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

        $query = PicbookPage::where('picbook_id', $request->picbook_id);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $pages = $query->orderBy('page_number', 'asc')
            ->with('variants')
            ->paginate($request->input('per_page', 15));

        return $this->success($pages, __('messages.picbook.page_list_success'));
    }

    /**
     * 创建绘本页面
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'picbook_id' => 'required|integer|exists:picbooks,id',
            'page_number' => 'required|integer|min:1',
            'image_url' => 'required|string',
            'elements' => 'nullable|json',
            'is_choices' => 'boolean',
            'question' => 'required_if:is_choices,true|nullable|string',
            'status' => 'required|integer|in:0,1',
            'is_ai_face' => 'boolean',
            'mask_image_url' => 'required_if:is_ai_face,true|nullable|string',
            'variants' => 'array'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        try {
            DB::beginTransaction();
            
            $page = PicbookPage::create($request->except('variants'));

            // 创建变体
            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    $variant['page_id'] = $page->id;
                    PicbookPageVariant::create($variant);
                }
            }

            DB::commit();
            return $this->success($page->load('variants'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('创建失败：' . $e->getMessage());
        }
    }

    /**
     * 获取绘本页面详情
     */
    public function show($id)
    {
        $page = PicbookPage::with('variants')->findOrFail($id);
        return $this->success($page);
    }

    /**
     * 更新绘本页面
     */
    public function update(Request $request, $id)
    {
        $page = PicbookPage::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'page_number' => 'integer|min:1',
            'image_url' => 'string',
            'elements' => 'nullable|json',
            'is_choices' => 'boolean',
            'question' => 'required_if:is_choices,true|nullable|string',
            'status' => 'integer|in:0,1',
            'is_ai_face' => 'boolean',
            'mask_image_url' => 'required_if:is_ai_face,true|nullable|string',
            'variants' => 'array'
        ]);

        if ($validator->fails()) {
            return $this->error(
                __('validation.failed'),
                $validator->errors(),
                422
            );
        }

        try {
            DB::beginTransaction();
            
            $page->update($request->except('variants'));

            // 更新变体
            if ($request->has('variants')) {
                // 删除旧的变体
                $page->variants()->delete();
                
                // 创建新的变体
                foreach ($request->variants as $variant) {
                    $variant['page_id'] = $page->id;
                    PicbookPageVariant::create($variant);
                }
            }

            DB::commit();
            return $this->success($page->load('variants'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除绘本页面
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $page = PicbookPage::findOrFail($id);
            // 删除变体
            $page->variants()->delete();
            // 删除页面
            $page->delete();

            DB::commit();
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('删除失败：' . $e->getMessage());
        }
    }
} 