<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceFollowUp extends Model
{
    use HasFactory;

    protected $table = 'absence_follow_ups';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['performed_at' => 'datetime'];
    }
}
