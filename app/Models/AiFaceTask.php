<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiFaceTask extends Model
{
    protected $fillable = [
        'task_id', 'status', 'input_image', 'mask_image', 
        'face_image', 'result_image', 'error_message', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 