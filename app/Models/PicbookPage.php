<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookPage extends Model
{
    use SoftDeletes;

    // 状态常量
    public const STATUS_DRAFT = 0;
    public const STATUS_PUBLISHED = 1;
    public const STATUS_HIDDEN = 2;

    protected $table = 'picbook_pages';

    protected $fillable = [
        'picbook_id',
        'page_number',
        'gender',
        'skincolor',
        'image_url',
        'elements',
        'is_choices',
        'question',
        'status',
        'is_ai_face',
        'mask_image_url',
        'has_replaceable_text',
        'text_elements'
    ];

    protected $casts = [
        'gender' => 'integer',
        'skincolor' => 'integer',
        'status' => 'integer',
        'elements' => 'array',
        'is_choices' => 'boolean',
        'is_ai_face' => 'boolean',
        'has_replaceable_text' => 'boolean',
        'text_elements' => 'array'
    ];

    public function picbook(): BelongsTo
    {
        return $this->belongsTo(Picbook::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PicbookPageTranslation::class, 'page_id');
    }

    // 查询作用域
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeByCharacteristics(Builder $query, int $gender, int $skinColor): Builder
    {
        return $query->where('gender', $gender)
                    ->where('skincolor', $skinColor);
    }

    public function scopeChoicesPages(Builder $query): Builder
    {
        return $query->where('is_choices', true);
    }

    // 辅助方法
    public function getTranslation(string $language)
    {
        return $this->translations()->where('language', $language)->first();
    }

    public function getContent(string $language): ?string
    {
        $translation = $this->getTranslation($language);
        return $translation ? $translation->content : null;
    }

    public function getQuestion(string $language): ?string
    {
        if (!$this->is_choices) {
            return null;
        }
        $translation = $this->getTranslation($language);
        return $translation ? $translation->question : $this->question;
    }

    public function isChoicesPage(): bool
    {
        return $this->is_choices;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function publish(): bool
    {
        return $this->update(['status' => self::STATUS_PUBLISHED]);
    }

    public function hide(): bool
    {
        return $this->update(['status' => self::STATUS_HIDDEN]);
    }

    public function updateElements(array $elements): bool
    {
        return $this->update(['elements' => $elements]);
    }
}