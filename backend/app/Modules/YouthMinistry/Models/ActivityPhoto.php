<?php

namespace App\Modules\YouthMinistry\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityPhoto extends Model
{
    use HasFactory;

    protected $table = 'activity_photos';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
