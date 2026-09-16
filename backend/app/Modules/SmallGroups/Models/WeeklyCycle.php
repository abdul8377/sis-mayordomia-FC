<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeeklyCycle extends Model
{
    use HasFactory;

    protected $table = 'weekly_cycles';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['saturday_date' => 'date'];
    }
}
