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
        'question'
    ];

    // 将 choice_options 字段转换为数组
    protected $casts = [
        'choice_options' => 'array',
        'gender' => 'integer',
        'skincolor' => 'integer'
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