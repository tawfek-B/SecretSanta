<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    protected $fillable = [
        'spinning_enabled',
    ];

    protected function casts(): array
    {
        return [
            'spinning_enabled' => 'boolean',
        ];
    }
}