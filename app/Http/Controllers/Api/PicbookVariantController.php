<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use App\Models\PicbookVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PicbookVariantController extends Controller
{
    public function store(Request $request, Picbook $picbook)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required|string|size:2',
            'bookname' => 'required|string|max:255',
            'gender' => ['required', Rule::in([Picbook::GENDER_MALE, Picbook::GENDER_FEMALE])],
            'skincolor' => 'required|integer|min:1',
            'cover' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 检查是否已存在相同特征的变体
        $exists = $picbook->variants()
            ->where('gender', $request->gender)
            ->where('skincolor', $request->skincolor)
            ->exists();

        if ($exists) {
            return response()->json(['message' => '已存在相同特征的变体'], 422);
        }

        $variant = $picbook->variants()->create(array_merge(
            $validator->validated(),
            ['status' => PicbookVariant::STATUS_ACTIVE]
        ));

        return response()->json($variant, 201);
    }

    public function update(Request $request, PicbookVariant $variant)
    {
        $validator = Validator::make($request->all(), [
            'bookname' => 'string|max:255',
            'cover' => 'string',
            'status' => ['integer', Rule::in([
                PicbookVariant::STATUS_INACTIVE,
                PicbookVariant::STATUS_ACTIVE,
                PicbookVariant::STATUS_PROCESSING
            ])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $variant->update($validator->validated());
        return $variant;
    }

    public function destroy(PicbookVariant $variant)
    {
        $variant->delete();
        return response()->json(null, 204);
    }

    public function activate(PicbookVariant $variant)
    {
        $variant->activate();
        return $variant;
    }

    public function deactivate(PicbookVariant $variant)
    {
        $variant->deactivate();
        return $variant;
    }
} 