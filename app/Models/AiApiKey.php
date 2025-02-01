<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiApiKey extends Model
{
    protected $fillable = ['api_key', 'current_tasks', 'is_active'];

    public static function getAvailableKey()
    {
        return static::where('is_active', true)
            ->orderBy('current_tasks')
            ->first();
    }
} 