<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spin extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_user_id',
        'spun_at',
        'revealed_at',
    ];

    protected function casts(): array
    {
        return [
            'revealed_at' => 'datetime',
            'spun_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
