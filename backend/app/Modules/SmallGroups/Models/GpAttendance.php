<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GpAttendance extends Model
{
    use HasFactory;

    protected $table = 'gp_attendances';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\People\Models\Person::class);
    }

    public function meeting(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GpMeeting::class, 'meeting_id');
    }
}
