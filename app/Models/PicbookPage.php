<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PicbookPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'picbook_id',
        'page_number',
        'image_url',
        'none_skin',
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
        'elements' => 'array',
        'text_elements' => 'array',
        'is_choices' => 'boolean',
        'is_ai_face' => 'boolean',
        'has_replaceable_text' => 'boolean'
    ];

    public function picbook()
    {
        return $this->belongsTo(Picbook::class);
    }

    public function variants()
    {
        return $this->hasMany(PicbookPageVariant::class, 'page_id');
    }
} 