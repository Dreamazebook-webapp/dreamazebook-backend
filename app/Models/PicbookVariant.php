<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookVariant extends Model
{
    use SoftDeletes;

    // 状态常量
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PROCESSING = 2;

    protected $table = 'picbook_variants';

    protected $fillable = [
        'picbook_id',
        'language',
        'bookname',
        'gender',
        'skincolor',
        'cover',
        'status'
    ];

    protected $casts = [
        'gender' => 'integer',
        'skincolor' => 'integer',
        'status' => 'integer'
    ];

    public function picbook(): BelongsTo
    {
        return $this->belongsTo(Picbook::class);
    }

    // 查询作用域
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    public function scopeByCharacteristics(Builder $query, int $gender, int $skinColor): Builder
    {
        return $query->where('gender', $gender)
                    ->where('skincolor', $skinColor);
    }

    // 辅助方法
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => self::STATUS_INACTIVE]);
    }
}