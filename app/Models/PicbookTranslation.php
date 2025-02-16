<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookTranslation extends Model
{
    use SoftDeletes;

    protected $table = 'picbook_translations';

    protected $fillable = [
        'picbook_id',
        'language',
        'bookname',
        'intro',
        'description',
        'cover',
        'tags'
    ];

    protected $casts = [
        'tags' => 'array'
    ];

    // 设置默认值
    protected $attributes = [
        'tags' => '[]'  // 默认为空数组
    ];

    public function picbook(): BelongsTo
    {
        return $this->belongsTo(Picbook::class);
    }
}