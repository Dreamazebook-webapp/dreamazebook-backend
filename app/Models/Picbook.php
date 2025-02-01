<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Picbook extends Model
{
    use SoftDeletes;

    // 状态常量
    public const STATUS_DRAFT = 0;
    public const STATUS_PUBLISHED = 1;
    public const STATUS_ARCHIVED = 2;

    protected $table = 'picbooks';

    protected $fillable = [
        'default_name',
        'pricesymbol',
        'price',
        'currencycode',
        'total_pages',
        'default_cover',
        'rating',
        'supported_languages',
        'supported_genders',
        'supported_skincolors',
        'tags',
        'has_choices',
        'has_qa',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'supported_languages' => 'array',
        'supported_genders' => 'array',
        'supported_skincolors' => 'array',
        'tags' => 'array',
        'has_choices' => 'boolean',
        'has_qa' => 'boolean',
        'status' => 'integer'
    ];

    // 关联关系
    public function translations(): HasMany
    {
        return $this->hasMany(PicbookTranslation::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PicbookVariant::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(PicbookPage::class);
    }

    // 查询作用域
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->where('tags', 'like', "%$tag%");
    }

    public function scopeWithEightChoices(Builder $query): Builder
    {
        return $query->where('has_choices', true);
    }

    public function scopeWithQA(Builder $query): Builder
    {
        return $query->where('has_qa', true);
    }

    // 辅助方法
    public function getTranslation(string $language)
    {
        return $this->translations()->where('language', $language)->first();
    }

    public function getVariant(string $language, int $gender, int $skinColor)
    {
        return $this->variants()
            ->where('language', $language)
            ->where('gender', $gender)
            ->where('skincolor', $skinColor)
            ->first();
    }

    public function getPagesInOrder()
    {
        return $this->pages()->orderBy('page_number')->get();
    }

    public function hasTranslation(string $language): bool
    {
        return $this->translations()->where('language', $language)->exists();
    }

    public function getFormattedPrice(): string
    {
        return $this->pricesymbol . number_format($this->price, 2) . ' ' . $this->currencycode;
    }

    public function supportsLanguage(string $language): bool
    {
        return in_array($language, $this->supported_languages ?? []);
    }

    public function supportsGender(int $gender): bool
    {
        return in_array($gender, $this->supported_genders ?? []);
    }

    public function supportsSkinColor(int $skinColor): bool
    {
        return in_array($skinColor, $this->supported_skincolors ?? []);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function hasChoices(): bool
    {
        return $this->has_choices;
    }

    public function hasQA(): bool
    {
        return $this->has_qa;
    }
}