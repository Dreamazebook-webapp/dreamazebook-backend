<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use App\Models\PicbookVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class PicbookVariantController extends Controller
{
    use ApiResponse;

    public function store(Request $request, Picbook $picbook)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                'string',
                'size:2',
                Rule::in($picbook->supported_languages)
            ],
            'gender' => [
                'required',
                'integer',
                Rule::in($picbook->supported_genders)
            ],
            'skincolor' => [
                'required',
                'integer',
                Rule::in($picbook->supported_skincolors)
            ],
            'bookname' => 'required|string|max:255',
            'cover' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error(__('validation.failed'), $validator->errors(), 422);
        }

        // 检查是否已存在相同特征的变体
        $exists = $picbook->variants()
            ->where('language', $request->language)
            ->where('gender', $request->gender)
            ->where('skincolor', $request->skincolor)
            ->exists();

        if ($exists) {
            return $this->error(__('picbook.variant_exists'), null, 422);
        }

        try {
            $variant = $picbook->variants()->create(array_merge(
                $validator->validated(),
                ['status' => PicbookVariant::STATUS_ACTIVE]
            ));

            return $this->success($variant, __('picbook.variant_create_success'), 201);
        } catch (\Exception $e) {
            return $this->error(__('picbook.variant_create_failed'), ['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, PicbookVariant $variant)
    {
        $validator = Validator::make($request->all(), [
            'bookname' => 'string|max:255',
            'cover' => 'string',
            'status' => Rule::in([
                PicbookVariant::STATUS_INACTIVE,
                PicbookVariant::STATUS_ACTIVE,
                PicbookVariant::STATUS_PROCESSING
            ]),
        ]);

        if ($validator->fails()) {
            return $this->error(__('validation.failed'), $validator->errors(), 422);
        }

        try {
            $variant->update($validator->validated());
            return $this->success($variant, __('picbook.variant_update_success'));
        } catch (\Exception $e) {
            return $this->error(__('picbook.variant_update_failed'), ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(PicbookVariant $variant)
    {
        try {
            $variant->delete();
            return $this->success(null, __('picbook.variant_delete_success'), 204);
        } catch (\Exception $e) {
            return $this->error(__('picbook.variant_delete_failed'), ['error' => $e->getMessage()], 500);
        }
    }

    public function activate(PicbookVariant $variant)
    {
        try {
            $variant->activate();
            return $this->success($variant, __('picbook.variant_activate_success'));
        } catch (\Exception $e) {
            return $this->error(__('picbook.variant_activate_failed'), ['error' => $e->getMessage()], 500);
        }
    }

    public function deactivate(PicbookVariant $variant)
    {
        try {
            $variant->deactivate();
            return $this->success($variant, __('picbook.variant_deactivate_success'));
        } catch (\Exception $e) {
            return $this->error(__('picbook.variant_deactivate_failed'), ['error' => $e->getMessage()], 500);
        }
    }
} 