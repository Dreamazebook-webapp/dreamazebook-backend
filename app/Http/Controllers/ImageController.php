<?php

namespace App\Http\Controllers;

use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    private $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function addText(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'image' => 'required|image|max:10240', // 最大10MB
                'text' => 'required|string|max:255',
                'x' => 'nullable|integer|min:0',
                'y' => 'nullable|integer|min:0',
                'size' => 'nullable|integer|min:1|max:1000',
                'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/' // 十六进制颜色代码
            ], [
                'size.max' => '文字大小不能超过1000',
                'size.min' => '文字大小不能小于1',
                'image.required' => '请上传图片',
                'image.image' => '文件必须是图片',
                'image.max' => '图片不能超过10MB',
                'text.required' => '请输入要添加的文字',
                'text.max' => '文字长度不能超过255个字符',
                'x.integer' => 'X坐标必须是整数',
                'x.min' => 'X坐标不能小于0',
                'y.integer' => 'Y坐标必须是整数',
                'y.min' => 'Y坐标不能小于0',
                'color.regex' => '颜色格式必须是十六进制代码（如：#FF0000）'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 保存上传的图片
            $imagePath = $request->file('image')->store('temp', 'public');
            $fullPath = Storage::disk('public')->path($imagePath);

            // 处理文字选项
            $textOptions = array_filter([
                'x' => $request->input('x'),
                'y' => $request->input('y'),
                'size' => $request->input('size'),
                'color' => $request->input('color'),
            ]);

            // 处理图片
            $results = $this->imageService->addTextAndGenerateVersions(
                $fullPath,
                $request->input('text'),
                $textOptions
            );

            // 构建URL路径
            $paths = [];
            foreach ($results as $dpi => $path) {
                $paths[$dpi] = Storage::disk('public')->url($path);
            }

            // 清理临时文件
            Storage::disk('public')->delete($imagePath);

            return response()->json([
                'success' => true,
                'message' => '图片处理成功',
                'data' => $paths
            ]);

        } catch (\Exception $e) {
            // 如果有上传的临时文件，清理它
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => '图片处理失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 生成预览图
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|max:10240',
                'text' => 'required|string|max:255',
                'x' => 'nullable|integer|min:0',
                'y' => 'nullable|integer|min:0',
                'size' => 'nullable|integer|min:1|max:1000',
                'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/'
            ]);

            // 保存上传的图片
            $imagePath = $request->file('image')->store('temp', 'public');
            $fullPath = Storage::disk('public')->path($imagePath);

            // 处理文字选项
            $textOptions = array_filter([
                'x' => $request->input('x'),
                'y' => $request->input('y'),
                'size' => $request->input('size'),
                'color' => $request->input('color'),
            ]);

            try {
                // 生成预览
                $result = $this->imageService->generatePreview(
                    $fullPath,
                    $request->input('text'),
                    $textOptions
                );

                // 清理临时文件
                Storage::disk('public')->delete($imagePath);

                return response()->json($result);

            } catch (\Exception $e) {
                // 清理临时文件
                Storage::disk('public')->delete($imagePath);

                return response()->json([
                    'success' => false,
                    'message' => '预览生成失败: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '请求验证失败: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * 多图片叠加合并
     */
    public function merge(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'base_image' => 'required|string', // 基础图片路径
                'overlay_images' => 'required|array', // 叠加图片路径数组
                'overlay_images.*' => 'required|string', // 每个叠加图片的路径
            ], [
                'base_image.required' => '请提供基础图片路径',
                'overlay_images.required' => '请提供叠加图片数组',
                'overlay_images.array' => '叠加图片必须是数组格式',
                'overlay_images.*.required' => '叠加图片路径不能为空'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 获取验证后的数据
            $data = $validator->validated();

            // 调用图片服务处理多图片叠加
            $result = $this->imageService->mergeMultipleImages(
                $data['base_image'],
                $data['overlay_images']
            );

            return response()->json([
                'success' => true,
                'message' => '图片合并成功',
                'data' => [
                    'image_url' => $result
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '图片合并失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 