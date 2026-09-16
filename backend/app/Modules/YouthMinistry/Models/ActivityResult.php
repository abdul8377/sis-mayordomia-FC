<?php

namespace App\Modules\YouthMinistry\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityResult extends Model
{
    use HasFactory;

    protected $table = 'activity_results';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['closed_at' => 'datetime'];
    }
}
