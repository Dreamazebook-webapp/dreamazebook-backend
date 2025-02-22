<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'picbook_id',
        'language',
        'gender',
        'skincolor',
        'bookname',
        'intro',
        'description',
        'cover',
        'price',
        'pricesymbol',
        'currencycode',
        'tags',
        'status'
    ];

    protected $casts = [
        'gender' => 'integer',
        'skincolor' => 'integer',
        'price' => 'decimal:2',
        'tags' => 'array',
        'status' => 'integer'
    ];

    public function picbook()
    {
        return $this->belongsTo(Picbook::class);
    }
} 