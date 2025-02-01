<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Exception;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    private $manager;

    public function __construct()
    {
        ini_set('memory_limit', '1G');  // 调整到 1GB
        ini_set('max_execution_time', 60);
        $this->manager = new ImageManager(
            new \Intervention\Image\Drivers\Imagick\Driver([
                'use_reentrant' => true, // 启用重入模式，提高内存使用效率
                'thread_limit' => 1      // 限制线程数，避免内存过度使用
            ])
        );
    }

    /**
     * 在图片上添加文字并生成不同版本
     *
     * @param string $imagePath 原始图片路径
     * @param string $text 要添加的文字
     * @param array $textOptions 文字选项 (x, y, size, color)
     * @return array 生成的图片路径数组
     * @throws Exception
     */
    public function addTextAndGenerateVersions(string $imagePath, string $text, array $textOptions = []): array
    {
        try {
            // 加载原始图片
            $image = $this->manager->read($imagePath);
            
            // 获取图片尺寸
            $width = $image->width();
            $height = $image->height();
            
            // 计算合适的文字大小限制
            $maxSize = min($width, $height) / 2; // 文字大小不超过图片较小边长的一半
            
            // 默认文字选项
            $defaultOptions = [
                'x' => 50,
                'y' => 50,
                'size' => 36,
                'color' => '#000000',
                'font' => public_path('fonts/msyh.ttc')
            ];
            
            $options = array_merge($defaultOptions, $textOptions);
            
            // 验证文字大小
            if ($options['size'] > $maxSize) {
                throw new Exception("文字大小过大，建议不要超过 {$maxSize}");
            }
            
            // 检查字体文件是否存在
            if (!file_exists($options['font'])) {
                throw new Exception('字体文件不存在');
            }
            
            // 检查原始图片是否存在
            if (!file_exists($imagePath)) {
                throw new Exception('原始图片不存在');
            }
            
            // 如果图片太大，先调整大小
            // if ($width > 2000 || $height > 2000) {
            //     $ratio = min(2000 / $width, 2000 / $height);
            //     $newWidth = round($width * $ratio);
            //     $newHeight = round($height * $ratio);
                
            //     $image->resize($newWidth, $newHeight, function ($constraint) {
            //         $constraint->aspectRatio();  // 保持宽高比
            //         $constraint->upsize();       // 防止小图被放大
            //     });
                
            //     // 更新宽高变量
            //     $width = $newWidth;
            //     $height = $newHeight;
            // }
            
            // 添加文字
            try {
                // 计算文字位置，确保不会超出图片边界
                $x = $options['x'];
                $y = $options['y'];
                
                // 如果没有指定位置，居中显示
                if (!isset($textOptions['x']) || !isset($textOptions['y'])) {
                    $x = $width / 2;
                    $y = $height / 2;
                    $image->text($text, $x, $y, function ($font) use ($options) {
                        $font->filename($options['font']);
                        $font->size($options['size']);
                        $font->color($options['color']);
                        $font->align('center');
                        $font->valign('middle');
                    });
                } else {
                    // 检查文字是否会超出边界
                    // 估算每个字符的平均宽度（中文字符约等于字体大小，英文字符约等于字体大小的一半）
                    $textWidth = 0;
                    for ($i = 0; $i < mb_strlen($text); $i++) {
                        $char = mb_substr($text, $i, 1);
                        // 使用正则表达式检查是否为ASCII字符（英文、数字等）
                        if (preg_match('/^[\x20-\x7E]+$/', $char)) {
                            $textWidth += $options['size'] * 0.6; // ASCII字符宽度
                        } else {
                            $textWidth += $options['size']; // 中文字符宽度
                        }
                    }
                    
                    // 添加字间距的影响
                    $textWidth += ($options['size'] * 0.1 * (mb_strlen($text) - 1));
                    
                    $rightEdge = $x + $textWidth;
                    $bottomEdge = $y + ($options['size'] * 1.2); // 考虑行高
                    
                    if ($rightEdge > $width) {
                        throw new Exception(sprintf('文字会超出图片右边界（文字宽度：%.0f，剩余空间：%.0f）', $textWidth, $width - $x));
                    }
                    if ($bottomEdge > $height) {
                        throw new Exception(sprintf('文字会超出图片下边界（文字高度：%.0f，剩余空间：%.0f）', $options['size'] * 1.2, $height - $y));
                    }
                    
                    $image->text($text, $x, $y, function ($font) use ($options) {
                        $font->filename($options['font']);
                        $font->size($options['size']);
                        $font->color($options['color']);
                        $font->align('left');
                        $font->valign('top');
                    });
                }
            } catch (Exception $e) {
                throw new Exception('文字添加失败：' . $e->getMessage());
            }

            // 生成文件名
            $pathInfo = pathinfo($imagePath);
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'];
            $directory = storage_path('app/public/processed');

            // 确保目录存在
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // 生成72dpi版本（实际上只是普通质量版本）
            try {
                $path72 = "{$directory}/{$filename}_72dpi.{$extension}";
                $image72 = clone $image;
                $image72->encode(new JpegEncoder(75))->save($path72);
                $image72 = null; // 释放内存
            } catch (Exception $e) {
                throw new Exception('72dpi版本生成失败：' . $e->getMessage());
            }

            // 生成300dpi版本（实际上是高质量版本）
            try {
                $path300 = "{$directory}/{$filename}_300dpi.{$extension}";
                $image300 = clone $image;
                $image300->encode(new JpegEncoder(100))->save($path300);
                $image300 = null; // 释放内存
            } catch (Exception $e) {
                throw new Exception('300dpi版本生成失败：' . $e->getMessage());
            }

            // 清理原始图片对象
            $image = null;

            // 强制垃圾回收
            gc_collect_cycles();

            return [
                '72dpi' => "processed/{$filename}_72dpi.{$extension}",
                '300dpi' => "processed/{$filename}_300dpi.{$extension}"
            ];
        } catch (Exception $e) {
            // 记录错误日志
            error_log('图片处理失败: ' . $e->getMessage());
            throw new Exception('图片处理失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成预览图
     *
     * @param string $imagePath 原始图片路径
     * @param string $text 要添加的文字
     * @param array $textOptions 文字选项 (x, y, size, color)
     * @return array 包含预览图base64数据和图片信息
     * @throws Exception
     */
    public function generatePreview(string $imagePath, string $text, array $textOptions = []): array
    {
        try {
            // 加载原始图片
            $image = $this->manager->read($imagePath);
            
            // 获取图片尺寸
            $width = $image->width();
            $height = $image->height();
            
            // 计算合适的文字大小限制
            $maxSize = min($width, $height) / 2;
            
            // 默认文字选项
            $defaultOptions = [
                'x' => 50,
                'y' => 50,
                'size' => 36,
                'color' => '#000000',
                'font' => public_path('fonts/msyh.ttc')
            ];
            
            $options = array_merge($defaultOptions, $textOptions);
            
            // 验证文字大小
            if ($options['size'] > $maxSize) {
                throw new Exception("文字大小过大，建议不要超过 {$maxSize}");
            }

            // 检查字体文件是否存在
            if (!file_exists($options['font'])) {
                throw new Exception('字体文件不存在');
            }
            
            // 添加文字
            $x = $options['x'];
            $y = $options['y'];
            
            // 计算文字边界
            $textWidth = 0;
            for ($i = 0; $i < mb_strlen($text); $i++) {
                $char = mb_substr($text, $i, 1);
                if (preg_match('/^[\x20-\x7E]+$/', $char)) {
                    $textWidth += $options['size'] * 0.6;
                } else {
                    $textWidth += $options['size'];
                }
            }
            $textWidth += ($options['size'] * 0.1 * (mb_strlen($text) - 1));
            
            // 检查是否会超出边界
            $rightEdge = $x + $textWidth;
            $bottomEdge = $y + ($options['size'] * 1.2);
            
            $willOverflow = false;
            $overflowMessage = '';
            
            if ($rightEdge > $width) {
                $willOverflow = true;
                $overflowMessage .= sprintf('文字会超出右边界（超出：%.0f像素）', $rightEdge - $width);
            }
            if ($bottomEdge > $height) {
                $willOverflow = true;
                $overflowMessage .= sprintf('文字会超出下边界（超出：%.0f像素）', $bottomEdge - $height);
            }

            // 添加文字到图片
            if (!isset($textOptions['x']) || !isset($textOptions['y'])) {
                $x = $width / 2;
                $y = $height / 2;
                $image->text($text, $x, $y, function ($font) use ($options) {
                    $font->filename($options['font']);
                    $font->size($options['size']);
                    $font->color($options['color']);
                    $font->align('center');
                    $font->valign('middle');
                });
            } else {
                $image->text($text, $x, $y, function ($font) use ($options) {
                    $font->filename($options['font']);
                    $font->size($options['size']);
                    $font->color($options['color']);
                    $font->align('left');
                    $font->valign('top');
                });
            }

            // 生成预览图（较小的尺寸和质量以加快加载速度）
            $previewImage = clone $image;
            if ($width > 800 || $height > 800) {
                $ratio = min(800 / $width, 800 / $height);
                $newWidth = round($width * $ratio);
                $newHeight = round($height * $ratio);
                $previewImage->resize($newWidth, $newHeight);
            }
            
            // 转换为base64
            $base64 = $previewImage->encode(new JpegEncoder(60))->toString();
            $base64Data = 'data:image/jpeg;base64,' . base64_encode($base64);
            
            // 清理内存
            $previewImage = null;
            $image = null;
            gc_collect_cycles();

            return [
                'success' => true,
                'preview' => $base64Data,
                'width' => $width,
                'height' => $height,
                'textWidth' => round($textWidth),
                'textHeight' => round($options['size'] * 1.2),
                'willOverflow' => $willOverflow,
                'overflowMessage' => $overflowMessage,
                'maxTextSize' => round($maxSize)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 合并两张图片
     *
     * @param string $baseImagePath 基础图片路径
     * @param string $overlayImagePath 叠加图片路径
     * @param int $x X坐标
     * @param int $y Y坐标
     * @param int|null $width 叠加图片宽度
     * @param int|null $height 叠加图片高度
     * @param float $opacity 透明度
     * @return string 合并后的图片路径
     */
    public function mergeImages(string $baseImagePath, string $overlayImagePath, int $x = 0, int $y = 0, ?int $width = null, ?int $height = null, float $opacity = 1): string
    {
        try {
            // 获取图片完整路径
            $baseImageFullPath = Storage::disk('public')->path($baseImagePath);
            $overlayImageFullPath = Storage::disk('public')->path($overlayImagePath);

            // 创建图片实例
            $baseImage = Image::make($baseImageFullPath);
            $overlayImage = Image::make($overlayImageFullPath);

            // 如果指定了尺寸，调整叠加图片大小
            if ($width && $height) {
                $overlayImage->resize($width, $height);
            }

            // 设置透明度并合并图片
            $baseImage->insert($overlayImage, 'top-left', $x, $y, function ($carry) use ($opacity) {
                if ($opacity < 1) {
                    $carry->opacity($opacity * 100);
                }
            });

            // 生成新的文件名
            $newFileName = 'merged/' . Str::random(40) . '.png';
            $newPath = Storage::disk('public')->path($newFileName);

            // 确保目录存在
            if (!Storage::disk('public')->exists('merged')) {
                Storage::disk('public')->makeDirectory('merged');
            }

            // 保存合并后的图片
            $baseImage->save($newPath);

            return $newFileName;
        } catch (\Exception $e) {
            \Log::error('Image merge failed', [
                'base_image' => $baseImagePath,
                'overlay_image' => $overlayImagePath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 使用GD库合并多张PNG图片
     *
     * @param string $baseImagePath 基础图片路径
     * @param array $overlayImages 叠加图片路径数组
     * @return string 合并后的图片路径
     */
    public function mergeMultipleImages(string $baseImagePath, array $overlayImages): string
    {
        try {
            // 检查基础图片是否存在
            if (!Storage::disk('public')->exists($baseImagePath)) {
                throw new \Exception("基础图片不存在: {$baseImagePath}");
            }

            // 获取基础图片完整路径
            $baseImageFullPath = Storage::disk('public')->path($baseImagePath);

            // 检查文件是否可读
            if (!is_readable($baseImageFullPath)) {
                throw new \Exception("基础图片不可读: {$baseImagePath}");
            }

            // 检查是否为PNG格式
            $extension = strtolower(pathinfo($baseImagePath, PATHINFO_EXTENSION));
            if ($extension !== 'png') {
                throw new \Exception("基础图片必须是PNG格式");
            }

            // 创建基础图片实例
            $baseImage = @imagecreatefrompng($baseImageFullPath);
            if (!$baseImage) {
                $error = error_get_last();
                throw new \Exception("基础图片加载失败: " . ($error['message'] ?? '未知错误'));
            }

            // 获取基础图片的尺寸
            $baseWidth = imagesx($baseImage);
            $baseHeight = imagesy($baseImage);

            // 创建一个新的真彩色图片
            $newBaseImage = imagecreatetruecolor($baseWidth, $baseHeight);
            imagealphablending($newBaseImage, true);
            imagesavealpha($newBaseImage, true);

            // 复制原图到新图
            imagecopy($newBaseImage, $baseImage, 0, 0, 0, 0, $baseWidth, $baseHeight);
            imagedestroy($baseImage);
            $baseImage = $newBaseImage;

            \Log::info('基础图片加载成功', [
                'width' => $baseWidth,
                'height' => $baseHeight,
                'path' => $baseImagePath
            ]);

            // 按顺序叠加每张图片
            foreach ($overlayImages as $index => $overlayPath) {
                try {
                    if (!Storage::disk('public')->exists($overlayPath)) {
                        \Log::warning("叠加图片不存在: {$overlayPath}");
                        continue;
                    }

                    $overlayFullPath = Storage::disk('public')->path($overlayPath);

                    if (!is_readable($overlayFullPath)) {
                        \Log::warning("叠加图片不可读: {$overlayPath}");
                        continue;
                    }

                    // 检查是否为PNG格式
                    $overlayExtension = strtolower(pathinfo($overlayPath, PATHINFO_EXTENSION));
                    if ($overlayExtension !== 'png') {
                        \Log::warning("叠加图片必须是PNG格式: {$overlayPath}");
                        continue;
                    }

                    // 加载叠加图片
                    $overlayImage = @imagecreatefrompng($overlayFullPath);
                    if (!$overlayImage) {
                        $error = error_get_last();
                        \Log::warning("叠加图片加载失败: {$overlayPath}, 错误: " . ($error['message'] ?? '未知错误'));
                        continue;
                    }

                    // 获取叠加图片尺寸
                    $overlayWidth = imagesx($overlayImage);
                    $overlayHeight = imagesy($overlayImage);

                    // 计算居中位置
                    $x = ($baseWidth - $overlayWidth) / 2;
                    $y = ($baseHeight - $overlayHeight) / 2;

                    // 合并图片
                    imagecopy($baseImage, $overlayImage, (int)$x, (int)$y, 0, 0, $overlayWidth, $overlayHeight);

                    // 清理内存
                    imagedestroy($overlayImage);
                    \Log::info("叠加图片 #{$index} 处理完成", [
                        'width' => $overlayWidth,
                        'height' => $overlayHeight,
                        'path' => $overlayPath
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("处理叠加图片失败 {$overlayPath}: " . $e->getMessage());
                    if (isset($overlayImage) && is_resource($overlayImage)) {
                        imagedestroy($overlayImage);
                    }
                    continue;
                }
            }

            // 生成新的文件名
            $newFileName = 'merged/' . Str::random(40) . '.png';
            $newPath = Storage::disk('public')->path($newFileName);

            // 确保目录存在
            if (!Storage::disk('public')->exists('merged')) {
                Storage::disk('public')->makeDirectory('merged');
            }

            // 保存合并后的图片
            if (!@imagepng($baseImage, $newPath, 9)) { // 9 是最高质量
                $error = error_get_last();
                throw new \Exception("保存合并图片失败: " . ($error['message'] ?? '未知错误'));
            }
            \Log::info('合并图片保存成功', ['path' => $newPath]);

            // 清理内存
            imagedestroy($baseImage);

            return $newFileName;
        } catch (\Exception $e) {
            if (isset($baseImage) && is_resource($baseImage)) {
                imagedestroy($baseImage);
            }
            \Log::error('Multiple images merge failed', [
                'base_image' => $baseImagePath,
                'overlay_images' => $overlayImages,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 