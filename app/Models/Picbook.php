<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Picbook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'default_name',
        'default_cover',
        'pricesymbol',
        'price',
        'currencycode',
        'total_pages',
        'rating',
        'has_choices',
        'has_qa',
        'supported_languages',
        'supported_genders',
        'supported_skincolors',
        'none_skin',
        'tags',
        'status',
        'choices_type'
    ];

    protected $casts = [
        'supported_languages' => 'array',
        'supported_genders' => 'array',
        'supported_skincolors' => 'array',
        'tags' => 'array',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'has_choices' => 'boolean',
        'has_qa' => 'boolean',
        'choices_type' => 'integer'
    ];

    // 关联绘本变体
    public function variants()
    {
        return $this->hasMany(PicbookVariant::class);
    }

    // 关联绘本页面
    public function pages()
    {
        return $this->hasMany(PicbookPage::class);
    }

    // 关联价格
    public function prices()
    {
        return $this->hasMany(PicbookPrice::class);
    }

    // 关联封面价格
    public function coverPrices()
    {
        return $this->hasMany(PicbookCoverPrice::class);
    }

    /**
     * 获取选择页面数量
     */
    public function getChoicePagesCountAttribute()
    {
        switch ($this->choices_type) {
            case 1:
                return ['total' => 8, 'select' => 4];
            case 2:
                return ['total' => 16, 'select' => 8];
            default:
                return ['total' => 0, 'select' => 0];
        }
    }
} 