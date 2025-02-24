<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookPageVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'page_id',
        'language',
        'gender',
        'skincolor',
        'image_url',
        'content',
        'choice_options',
        'question',
        'character_masks'
    ];

    // 将 choice_options 字段转换为数组
    protected $casts = [
        'choice_options' => 'array',
        'gender' => 'integer',
        'skincolor' => 'integer',
        'character_masks' => 'array'
    ];

    /**
     * 获取关联的绘本页面
     */
    public function page()
    {
        return $this->belongsTo(PicbookPage::class, 'page_id');
    }

    /**
     * 通过页面获取关联的绘本
     */
    public function picbook()
    {
        return $this->hasOneThrough(
            Picbook::class,
            PicbookPage::class,
            'id', // picbook_pages.id
            'id', // picbooks.id
            'page_id', // picbook_page_variants.page_id
            'picbook_id' // picbook_pages.picbook_id
        );
    }

    /**
     * 获取指定位置的角色蒙版
     */
    public function getCharacterMask($position)
    {
        return $this->character_masks[$position] ?? null;
    }

    /**
     * 验证角色蒙版数量是否与页面角色序列匹配
     * 
     * @return bool|string 验证通过返回true，否则返回错误信息
     */
    public function validateCharacterMasks()
    {
        // 如果页面没有角色序列，则不需要蒙版
        if (empty($this->page->character_sequence)) {
            return $this->character_masks ? __('picbook.page_variant.no_sequence_with_masks') : true;
        }

        // 如果页面有角色序列，但没有提供蒙版
        if (empty($this->character_masks)) {
            return __('picbook.page_variant.masks_required');
        }

        // 验证蒙版数量是否匹配
        $sequence_count = count($this->page->character_sequence);
        if (count($this->character_masks) !== $sequence_count) {
            return __('picbook.page_variant.masks_count_mismatch', [
                'masks' => count($this->character_masks),
                'sequence' => $sequence_count
            ]);
        }

        // 验证所有蒙版URL是否有效
        foreach ($this->character_masks as $mask) {
            if (empty($mask) || !is_string($mask)) {
                return __('picbook.page_variant.invalid_mask_url');
            }
        }

        return true;
    }

    /**
     * 本地化范围查询
     */
    public function scopeLocalized($query, $language = null, $gender = null, $skincolor = null)
    {
        $language = $language ?? app()->getLocale();
        $gender = $gender ?? 1;
        $skincolor = $skincolor ?? 1;

        return $query->where([
            'language' => $language,
            'gender' => $gender,
            'skincolor' => $skincolor
        ]);
    }
} 