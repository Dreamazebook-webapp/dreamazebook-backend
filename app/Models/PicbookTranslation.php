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
        'pricesymbol',
        'price',
        'currencycode',
        'cover',
        'tags'
    ];

    protected $casts = [
        'price' => 'float',
        'tags' => 'array'
    ];

    public function picbook(): BelongsTo
    {
        return $this->belongsTo(Picbook::class);
    }
}