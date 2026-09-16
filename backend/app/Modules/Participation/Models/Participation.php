<?php

namespace App\Modules\Participation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Participation extends Model
{
    use HasFactory;

    protected $table = 'participations';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['confirmed_at' => 'datetime', 'revoked_at' => 'datetime'];
    }
}
