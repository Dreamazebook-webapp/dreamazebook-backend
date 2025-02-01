<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookPageTranslation extends Model
{
    use SoftDeletes;

    protected $table = 'picbook_page_translations';

    protected $fillable = [
        'page_id',
        'language',
        'content',
        'is_choices',
        'question'
    ];

    protected $casts = [
        'is_choices' => 'boolean'
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(PicbookPage::class, 'page_id');
    }

    // 辅助方法
    public function isChoicesPage(): bool
    {
        return $this->is_choices;
    }
}