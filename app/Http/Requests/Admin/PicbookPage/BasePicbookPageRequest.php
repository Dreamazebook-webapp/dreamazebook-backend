<?php

namespace App\Http\Requests\Admin\PicbookPage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Picbook;

abstract class BasePicbookPageRequest extends FormRequest
{
    // 文字对齐方式
    public const TEXT_ALIGN_LEFT = 'left';
    public const TEXT_ALIGN_CENTER = 'center';
    public const TEXT_ALIGN_RIGHT = 'right';

    // 文字样式默认值
    protected const DEFAULT_FONT_SIZE = 24;
    protected const DEFAULT_LINE_HEIGHT = 1.5;
    protected const DEFAULT_LETTER_SPACING = 0;
    protected const DEFAULT_FONT_FAMILY = 'Arial';
    protected const DEFAULT_COLOR = '#000000';

    public function authorize(): bool
    {
        return true;
    }

    protected function baseRules(): array
    {
        return [
            'image' => ['image', 'max:5120'], // 5MB
            'elements' => ['nullable', 'array'],
            'is_choices' => $this->getChoicesRule(),
            'question' => $this->getQuestionRule(),
            // AI换脸相关验证
            'is_ai_face' => ['boolean'],
            'mask_image' => ['required_if:is_ai_face,true', 'image', 'max:5120'],
            // 可替换文字相关验证
            'has_replaceable_text' => ['boolean'],
            'text_elements' => $this->getTextElementsRules(),
            // 翻译相关验证
            'translations' => $this->getTranslationsRules(),
        ];
    }

    protected function getChoicesRule(): array
    {
        return [
            'boolean',
            function ($attribute, $value, $fail) {
                $picbook = $this->getPicbook();
                if ($value && !$picbook->has_choices) {
                    $fail('绘本不支持选择功能');
                }
            }
        ];
    }

    protected function getQuestionRule(): array
    {
        return [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                $picbook = $this->getPicbook();
                if ($value && !$picbook->has_qa) {
                    $fail('绘本不支持问答功能');
                }
            }
        ];
    }

    protected function getTextElementsRules(): array
    {
        return [
            'required_if:has_replaceable_text,true',
            'array',
            'min:1',
            function ($attribute, $value, $fail) {
                foreach ($value as $element) {
                    if (!$this->validateTextElement($element)) {
                        $fail('文字元素格式不正确');
                        break;
                    }
                }
            }
        ];
    }

    protected function validateTextElement(array $element): bool
    {
        $required = ['id', 'x', 'y', 'width', 'height', 'fontSize', 'fontFamily', 'color', 'alignment', 'defaultText', 'replaceable'];
        foreach ($required as $field) {
            if (!isset($element[$field])) {
                return false;
            }
        }

        // 验证对齐方式
        if (!in_array($element['alignment'], [self::TEXT_ALIGN_LEFT, self::TEXT_ALIGN_CENTER, self::TEXT_ALIGN_RIGHT])) {
            return false;
        }

        // 验证样式
        if (isset($element['style'])) {
            $style = $element['style'];
            if (!$this->validateTextStyle($style)) {
                return false;
            }
        }

        return true;
    }

    protected function validateTextStyle(array $style): bool
    {
        $validFields = ['bold', 'italic', 'underline', 'lineHeight', 'letterSpacing'];
        foreach ($style as $key => $value) {
            if (!in_array($key, $validFields)) {
                return false;
            }
            if (in_array($key, ['bold', 'italic', 'underline']) && !is_bool($value)) {
                return false;
            }
            if (in_array($key, ['lineHeight', 'letterSpacing']) && !is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    protected function getTranslationsRules(): array
    {
        return [
            'translations' => ['array'],
            'translations.*.language' => [
                'required',
                'string',
                'size:2',
                function ($attribute, $value, $fail) {
                    $picbook = $this->getPicbook();
                    if (!in_array($value, $picbook->supported_languages ?? [])) {
                        $fail('不支持的语言');
                    }
                }
            ],
            'translations.*.content' => ['required', 'string'],
            'translations.*.question' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    $picbook = $this->getPicbook();
                    if ($value && !$picbook->has_qa) {
                        $fail('绘本不支持问答功能，不能设置问题翻译');
                    }
                }
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'gender.in' => '性别只能是1或2',
            'image.required' => '请上传图片',
            'image.image' => '文件必须是图片',
            'image.max' => '图片不能超过5MB',
            'mask_image.required_if' => '启用AI换脸时必须上传遮罩图片',
            'mask_image.image' => '遮罩文件必须是图片',
            'mask_image.max' => '遮罩图片不能超过5MB',
            'text_elements.required_if' => '启用可替换文字时必须提供文字元素配置'
        ];
    }

    protected function getPicbook(): Picbook
    {
        $picbook = $this->route('picbook');
        if (!$picbook) {
            \Log::error('Route parameter not found', [
                'route' => $this->route()->getName(),
                'parameters' => $this->route()->parameters(),
            ]);
            abort(500, 'Picbook not found in route parameters');
        }
        return $picbook;
    }

    /**
     * 获取默认的文字元素配置
     */
    public static function getDefaultTextElement(string $id, float $x, float $y): array
    {
        return [
            'id' => $id,
            'x' => $x,
            'y' => $y,
            'width' => 300,
            'height' => 50,
            'fontSize' => self::DEFAULT_FONT_SIZE,
            'fontFamily' => self::DEFAULT_FONT_FAMILY,
            'color' => self::DEFAULT_COLOR,
            'alignment' => self::TEXT_ALIGN_CENTER,
            'defaultText' => '',
            'replaceable' => true,
            'style' => [
                'bold' => false,
                'italic' => false,
                'underline' => false,
                'lineHeight' => self::DEFAULT_LINE_HEIGHT,
                'letterSpacing' => self::DEFAULT_LETTER_SPACING
            ]
        ];
    }
}