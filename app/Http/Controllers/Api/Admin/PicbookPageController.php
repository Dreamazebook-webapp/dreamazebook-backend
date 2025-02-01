<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use App\Models\PicbookPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\PicbookPage\StorePicbookPageRequest;
use App\Http\Requests\Admin\PicbookPage\UpdatePicbookPageRequest;

class PicbookPageController extends Controller
{
    public function index(Request $request, Picbook $picbook)
    {
        $query = $picbook->pages()->with('translations');

        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->has('skincolor')) {
            $query->where('skincolor', $request->skincolor);
        }
        if ($request->has('is_choices')) {
            $query->where('is_choices', $request->boolean('is_choices'));
        }
        //是否需要分页
        // $perPage = $request->input('per_page', 15);
        // return $query->orderBy('page_number')->paginate($perPage);
        return $query->orderBy('page_number')->get();
    }

    public function store(Request $request, Picbook $picbook)
    {
        // 验证请求数据
        $validator = Validator::make($request->all(), [
            'page_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('picbook_pages')
                    ->where('picbook_id', $picbook->id)
                    ->where('gender', $request->input('gender'))
                    ->where('skincolor', $request->input('skincolor'))
            ],
            'gender' => ['required', 'integer', 'in:1,2'],
            'skincolor' => ['required', 'integer', 'min:1'],
            'image' => ['required', 'string'],
            'elements' => ['nullable', 'array'],
            'is_choices' => ['boolean'],
            'question' => ['nullable', 'string'],
            'is_ai_face' => ['boolean'],
            'mask_image' => ['nullable', 'string'],
            'has_replaceable_text' => ['boolean'],
            'text_elements' => ['nullable', 'array'],
            'translations' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $validated = $validator->validated();
            
            // 创建页面
            $page = $picbook->pages()->create([
                'page_number' => $validated['page_number'],
                'gender' => $validated['gender'],
                'skincolor' => $validated['skincolor'],
                'image_url' => $validated['image'],
                'elements' => $validated['elements'] ?? null,
                'is_choices' => $validated['is_choices'] ?? false,
                'question' => $validated['question'] ?? null,
                'is_ai_face' => $validated['is_ai_face'] ?? false,
                'mask_image' => $validated['mask_image'] ?? null,
                'has_replaceable_text' => $validated['has_replaceable_text'] ?? false,
                'text_elements' => $validated['text_elements'] ?? null,
            ]);

            // 处理翻译
            if (!empty($validated['translations'])) {
                foreach ($validated['translations'] as $translation) {
                    $page->translations()->create([
                        'language' => $translation['language'],
                        'content' => $translation['content'],
                        'question' => $translation['question'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json($page->load('translations'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '创建失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdatePicbookPageRequest $request, PicbookPage $page)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|max:5120', // 5MB
            'image_url' => 'string',
            'elements' => 'array',
            'is_choices' => [
                'boolean',
                function ($attribute, $value, $fail) use ($page) {
                    if ($value && !$page->picbook->has_choices) {
                        $fail('绘本不支持选择功能');
                    }
                }
            ],
            'question' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($page) {
                    if ($value && !$page->picbook->has_qa) {
                        $fail('绘本不支持问答功能');
                    }
                }
            ],
            'status' => ['integer', Rule::in([
                PicbookPage::STATUS_DRAFT,
                PicbookPage::STATUS_PUBLISHED,
                PicbookPage::STATUS_HIDDEN
            ])],
            // AI换脸相关验证
            'is_ai_face' => 'boolean',
            'mask_image' => 'required_if:is_ai_face,true|image|max:5120',
            // 可替换文字相关验证
            'has_replaceable_text' => 'boolean',
            'text_elements' => [
                'required_if:has_replaceable_text,true',
                'array',
                'min:1'
            ],
            'text_elements.*.id' => 'required|string',
            'text_elements.*.x' => 'required|numeric',
            'text_elements.*.y' => 'required|numeric',
            'text_elements.*.width' => 'required|numeric',
            'text_elements.*.height' => 'required|numeric',
            'text_elements.*.fontSize' => 'required|numeric',
            'text_elements.*.fontFamily' => 'required|string',
            'text_elements.*.color' => 'required|string',
            'text_elements.*.alignment' => 'required|in:left,center,right',
            'text_elements.*.defaultText' => 'required|string',
            'text_elements.*.replaceable' => 'required|boolean',
            'text_elements.*.style' => 'nullable|array',
            'text_elements.*.style.bold' => 'boolean',
            'text_elements.*.style.italic' => 'boolean',
            'text_elements.*.style.underline' => 'boolean',
            'text_elements.*.style.lineHeight' => 'numeric',
            'text_elements.*.style.letterSpacing' => 'numeric',
            // 翻译相关验证
            'translations' => 'array',
            'translations.*.language' => [
                'required',
                'string',
                'size:2',
                Rule::in($page->picbook->supported_languages ?? [])
            ],
            'translations.*.content' => 'required|string',
            'translations.*.question' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($page) {
                    if ($value && !$page->picbook->has_qa) {
                        $fail('绘本不支持问答功能，不能设置问题翻译');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '验证失败',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();

            // 处理新上传的图片
            if ($request->hasFile('image')) {
                // 删除旧图片
                if ($page->image_url) {
                    Storage::disk('public')->delete($page->image_url);
                }
                $data['image_url'] = Storage::disk('public')->put('picbooks', $request->file('image'));
            }

            // 处理AI换脸相关的遮罩图片
            if ($request->hasFile('mask_image')) {
                // 删除旧的遮罩图片
                if ($page->mask_image_url) {
                    Storage::disk('public')->delete($page->mask_image_url);
                }
                $data['mask_image_url'] = Storage::disk('public')->put('picbooks/masks', $request->file('mask_image'));
            }

            // 更新页面基本信息
            $page->update([
                'image_url' => $data['image_url'] ?? $page->image_url,
                'elements' => $data['elements'] ?? $page->elements,
                'is_choices' => $data['is_choices'] ?? $page->is_choices,
                'question' => $data['question'] ?? $page->question,
                'status' => $data['status'] ?? $page->status,
                'is_ai_face' => $data['is_ai_face'] ?? $page->is_ai_face,
                'mask_image_url' => $data['mask_image_url'] ?? $page->mask_image_url,
                'has_replaceable_text' => $data['has_replaceable_text'] ?? $page->has_replaceable_text,
                'text_elements' => $data['text_elements'] ?? $page->text_elements,
            ]);

            // 处理翻译
            if (isset($data['translations'])) {
                foreach ($data['translations'] as $translation) {
                    $page->translations()->updateOrCreate(
                        ['language' => $translation['language']],
                        [
                            'content' => $translation['content'],
                            'question' => $translation['question'] ?? null,
                            'is_choices' => $data['is_choices'] ?? $page->is_choices
                        ]
                    );
                }
            }

            DB::commit();

            // 重新加载关联数据
            $page->load('translations');

            return response()->json([
                'message' => '页面更新成功',
                'page' => $page
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '页面更新失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PicbookPage $page)
    {
        try {
            DB::beginTransaction();
            $page->translations()->delete();
            $page->delete();
            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '删除失败'], 500);
        }
    }

    /**
     * 恢复已删除的页面
     */
    public function restore(int $id)
    {
        try {
            DB::beginTransaction();
            
            $page = PicbookPage::withTrashed()->findOrFail($id);
            $page->restore();
            $page->translations()->withTrashed()->restore();
            
            DB::commit();
            return response()->json($page->load('translations'), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '恢复失败'], 500);
        }
    }

    /**
     * 永久删除页面
     */
    public function forceDelete(int $id)
    {
        try {
            DB::beginTransaction();
            
            $page = PicbookPage::withTrashed()->findOrFail($id);
            $page->translations()->forceDelete();
            $page->forceDelete();
            
            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '永久删除失败'], 500);
        }
    }

    public function updateTranslation(Request $request, PicbookPage $page, string $language)
    {
        // 检查语言是否被支持
        if (!$page->picbook->supportsLanguage($language)) {
            return response()->json(['message' => '不支持的语言'], 422);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'question' => $page->is_choices ? 'required|string' : 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $translation = $page->translations()->updateOrCreate(
            ['language' => $language],
            $validator->validated()
        );

        return $translation;
    }

    public function publish(PicbookPage $page)
    {
        // 检查是否所有支持的语言都有翻译
        $missingLanguages = array_diff(
            $page->picbook->supported_languages ?? [],
            $page->translations->pluck('language')->toArray()
        );

        if (!empty($missingLanguages)) {
            return response()->json([
                'message' => '缺少以下语言的翻译',
                'languages' => $missingLanguages
            ], 422);
        }

        $page->publish();
        return $page;
    }

    public function hide(PicbookPage $page)
    {
        $page->hide();
        return $page;
    }

    // 批量创建变体页面
    public function createVariants(Request $request, PicbookPage $page)
    {
        $picbook = $page->picbook;
        $variants = [];

        foreach ($picbook->supported_genders as $gender) {
            foreach ($picbook->supported_skincolors as $skincolor) {
                // 跳过原始页面的组合
                if ($gender == $page->gender && $skincolor == $page->skincolor) {
                    continue;
                }

                // 检查是否已存在
                $exists = $picbook->pages()
                    ->where('page_number', $page->page_number)
                    ->where('gender', $gender)
                    ->where('skincolor', $skincolor)
                    ->exists();

                if (!$exists) {
                    $variantPage = $page->replicate();
                    $variantPage->gender = $gender;
                    $variantPage->skincolor = $skincolor;
                    $variantPage->image_url = str_replace(
                        "_{$page->gender}_{$page->skincolor}.",
                        "_{$gender}_{$skincolor}.",
                        $page->image_url
                    );
                    $variantPage->save();

                    // 复制翻译
                    foreach ($page->translations as $translation) {
                        $variantPage->translations()->create($translation->toArray());
                    }

                    $variants[] = $variantPage;
                }
            }
        }

        return response()->json([
            'message' => '变体创建成功',
            'variants' => $variants
        ]);
    }
} 