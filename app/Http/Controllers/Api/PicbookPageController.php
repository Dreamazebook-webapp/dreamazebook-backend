<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picbook;
use App\Models\PicbookPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PicbookPageController extends Controller
{
    public function store(Request $request, Picbook $picbook)
    {
        $validator = Validator::make($request->all(), [
            'page_number' => 'required|integer|min:1',
            'gender' => ['required', Rule::in([Picbook::GENDER_MALE, Picbook::GENDER_FEMALE])],
            'skincolor' => 'required|integer|min:1',
            'image_url' => 'required|string',
            'elements' => 'nullable|array',
            'translations' => 'required|array',
            'translations.*.language' => 'required|string|size:2',
            'translations.*.content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $page = $picbook->pages()->create([
                'page_number' => $request->page_number,
                'gender' => $request->gender,
                'skincolor' => $request->skincolor,
                'image_url' => $request->image_url,
                'elements' => $request->elements,
                'status' => PicbookPage::STATUS_DRAFT,
            ]);

            // 创建翻译
            foreach ($request->translations as $translation) {
                $page->translations()->create($translation);
            }

            DB::commit();
            return response()->json($page->load('translations'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => '创建失败'], 500);
        }
    }

    public function update(Request $request, PicbookPage $page)
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'string',
            'elements' => 'array',
            'status' => ['integer', Rule::in([
                PicbookPage::STATUS_DRAFT,
                PicbookPage::STATUS_PUBLISHED,
                PicbookPage::STATUS_HIDDEN
            ])],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $page->update($validator->validated());
        return $page;
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

    public function updateTranslation(Request $request, PicbookPage $page, string $language)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $translation = $page->translations()->updateOrCreate(
            ['language' => $language],
            ['content' => $request->content]
        );

        return $translation;
    }

    public function publish(PicbookPage $page)
    {
        $page->publish();
        return $page;
    }

    public function hide(PicbookPage $page)
    {
        $page->hide();
        return $page;
    }
} 