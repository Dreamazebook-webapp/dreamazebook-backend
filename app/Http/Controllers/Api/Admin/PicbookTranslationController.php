<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use App\Models\PicbookTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class PicbookTranslationController extends Controller
{
    use ApiResponse;

    public function store(Request $request, Picbook $picbook)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                'string',
                'size:2',
                Rule::in($picbook->supported_languages),
                Rule::unique('picbook_translations')->where(function ($query) use ($picbook) {
                    return $query->where('picbook_id', $picbook->id);
                })
            ],
            'bookname' => 'required|string|max:255',
            'intro' => 'nullable|string',
            'description' => 'nullable|string',
            'cover' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->error(__('validation.failed'), $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            
            $translation = $picbook->translations()->create($validator->validated());
            
            DB::commit();
            return $this->success($translation, __('picbook.translation_create_success'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(__('picbook.translation_create_failed'), ['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, PicbookTranslation $translation)
    {
        $validator = Validator::make($request->all(), [
            'bookname' => 'string|max:255',
            'intro' => 'nullable|string',
            'description' => 'nullable|string',
            'cover' => 'string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->error(__('validation.failed'), $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            
            $translation->update($validator->validated());
            
            DB::commit();
            return $this->success($translation, __('picbook.translation_update_success'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(__('picbook.translation_update_failed'), ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(PicbookTranslation $translation)
    {
        try {
            $translation->delete();
            return $this->success(null, __('picbook.translation_delete_success'), 204);
        } catch (\Exception $e) {
            return $this->error(__('picbook.translation_delete_failed'), ['error' => $e->getMessage()], 500);
        }
    }
} 