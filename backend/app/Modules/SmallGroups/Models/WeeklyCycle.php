<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
