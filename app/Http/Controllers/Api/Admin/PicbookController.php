<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use App\Models\PicbookVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PicbookController extends Controller
{
    // 后台列表，显示所有绘本
    public function index(Request $request)
    {
        $query = Picbook::query();

        // 应用过滤条件
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('tag')) {
            $query->withTag($request->tag);
        }

        // 加载关联数据
        $query->with(['translations', 'variants']);

        // 分页
        $perPage = $request->input('per_page', 15);
        return $query->paginate($perPage);
    }

    // 后台详情，显示完整信息
    public function show(Picbook $picbook)
    {
        return $picbook->load(['translations', 'variants', 'pages' => function($query) {
            $query->orderBy('page_number');
        }]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_cover' => 'required|string',
            'pricesymbol' => 'required|string|max:10',
            'price' => 'required|numeric|min:0',
            'currencycode' => 'required|string|size:3',
            'total_pages' => 'required|integer|min:1',
            'rating' => [
                'required',
                'numeric',
                'min:0',
                'max:5',
                'regex:/^\d+(\.\d)?$/'
            ],
            'has_choices' => 'boolean',
            'has_qa' => 'boolean',
            'supported_languages' => 'required|array|min:1',
            'supported_languages.*' => 'required|string|size:2',
            'supported_genders' => 'required|array|min:1',
            'supported_genders.*' => 'required|integer|in:1,2',
            'supported_skincolors' => 'required|array|min:1',
            'supported_skincolors.*' => 'required|integer|min:1',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'status' => ['required', Rule::in([
                Picbook::STATUS_DRAFT,
                Picbook::STATUS_PUBLISHED,
                Picbook::STATUS_ARCHIVED
            ])],
            // 默认变体信息
            'default_variant' => 'required|array',
            'default_variant.language' => 'required|string|size:2',
            'default_variant.gender' => 'required|integer|in:1,2',
            'default_variant.skincolor' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();
            
            $picbook = Picbook::create($validator->validated());

            // 创建默认变体
            $defaultVariant = $request->default_variant;
            $picbook->variants()->create([
                'language' => $defaultVariant['language'],
                'bookname' => $request->default_name,
                'gender' => $defaultVariant['gender'],
                'skincolor' => $defaultVariant['skincolor'],
                'cover' => $request->default_cover,
                'status' => PicbookVariant::STATUS_ACTIVE,
            ]);

            DB::commit();
            return response()->json($picbook->load('variants'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '创建失败', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Picbook $picbook)
    {
        $validator = Validator::make($request->all(), [
            'default_name' => 'string|max:255',
            'pricesymbol' => 'string|max:10',
            'price' => 'numeric|min:0',
            'currencycode' => 'string|size:3',
            'default_cover' => 'string',
            'rating' => [
                'numeric',
                'min:0',
                'max:5',
                'regex:/^\d+(\.\d)?$/'
            ],
            'has_choices' => 'boolean',
            'has_qa' => 'boolean',
            'supported_languages' => 'array',
            'supported_languages.*' => 'string|size:2',
            'supported_genders' => 'array',
            'supported_genders.*' => 'integer',
            'supported_skincolors' => 'array',
            'supported_skincolors.*' => 'integer',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'status' => Rule::in([
                Picbook::STATUS_DRAFT,
                Picbook::STATUS_PUBLISHED,
                Picbook::STATUS_ARCHIVED
            ]),
            // 变体更新
            'variants' => 'array',
            'variants.*.language' => 'required|string|size:2',
            'variants.*.gender' => 'required|integer',
            'variants.*.skincolor' => 'required|integer',
            'variants.*.bookname' => 'required|string|max:255',
            'variants.*.cover' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // 更新绘本基本信息
            $picbook->update($validator->validated());

            // 处理语言变更
            if ($request->has('supported_languages')) {
                $oldLanguages = $picbook->getOriginal('supported_languages') ?? [];
                $newLanguages = $request->supported_languages;
                
                // 删除不再支持的语言的翻译
                $removedLanguages = array_diff($oldLanguages, $newLanguages);
                if (!empty($removedLanguages)) {
                    $picbook->translations()->whereIn('language', $removedLanguages)->delete();
                    // 删除相关变体
                    $picbook->variants()->whereIn('language', $removedLanguages)->delete();
                    // 删除页面翻译
                    foreach ($picbook->pages as $page) {
                        $page->translations()->whereIn('language', $removedLanguages)->delete();
                    }
                }
            }

            // 处理性别和肤色变更
            if ($request->has('supported_genders') || $request->has('supported_skincolors')) {
                $oldGenders = $picbook->getOriginal('supported_genders') ?? [];
                $oldSkincolors = $picbook->getOriginal('supported_skincolors') ?? [];
                $newGenders = $request->supported_genders ?? $oldGenders;
                $newSkincolors = $request->supported_skincolors ?? $oldSkincolors;

                // 删除不再支持的性别和肤色组合的变体和页面
                $picbook->variants()
                    ->where(function ($query) use ($newGenders, $newSkincolors) {
                        $query->whereNotIn('gender', $newGenders)
                            ->orWhereNotIn('skincolor', $newSkincolors);
                    })
                    ->delete();

                $picbook->pages()
                    ->where(function ($query) use ($newGenders, $newSkincolors) {
                        $query->whereNotIn('gender', $newGenders)
                            ->orWhereNotIn('skincolor', $newSkincolors);
                    })
                    ->delete();
            }

            // 更新或创建变体
            if ($request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    $picbook->variants()->updateOrCreate(
                        [
                            'language' => $variantData['language'],
                            'gender' => $variantData['gender'],
                            'skincolor' => $variantData['skincolor']
                        ],
                        [
                            'bookname' => $variantData['bookname'],
                            'cover' => $variantData['cover'],
                            'status' => PicbookVariant::STATUS_ACTIVE
                        ]
                    );
                }
            }

            DB::commit();
            return response()->json($picbook->load(['variants', 'translations']), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '更新失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Picbook $picbook)
    {
        try {
            DB::beginTransaction();
            // 软删除关联数据
            $picbook->translations()->delete();
            $picbook->variants()->delete();
            $picbook->pages()->delete();
            $picbook->delete();
            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '删除失败'], 500);
        }
    }

    /**
     * 恢复已删除的绘本
     */
    public function restore(int $id)
    {
        try {
            DB::beginTransaction();
            
            $picbook = Picbook::withTrashed()->findOrFail($id);
            // 恢复绘本及其关联数据
            $picbook->restore();
            $picbook->translations()->withTrashed()->restore();
            $picbook->variants()->withTrashed()->restore();
            $picbook->pages()->withTrashed()->restore();
            
            DB::commit();
            return response()->json($picbook->load(['translations', 'variants', 'pages']), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '恢复失败'], 500);
        }
    }

    /**
     * 永久删除绘本
     */
    public function forceDelete(int $id)
    {
        try {
            DB::beginTransaction();
            
            $picbook = Picbook::withTrashed()->findOrFail($id);
            // 永久删除绘本及其关联数据
            $picbook->translations()->forceDelete();
            $picbook->variants()->forceDelete();
            $picbook->pages()->forceDelete();
            $picbook->forceDelete();
            
            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '永久删除失败'], 500);
        }
    }

    /**
     * 获取已删除的绘本列表
     */
    public function trashed()
    {
        $picbooks = Picbook::onlyTrashed()
            ->with(['translations', 'variants'])
            ->paginate();
        return response()->json($picbooks);
    }
} 