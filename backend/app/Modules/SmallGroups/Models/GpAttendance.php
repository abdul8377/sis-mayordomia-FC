<?php

namespace App\Modules\SmallGroups\Models;

use App\Modules\People\Models\Person;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpAttendance extends Model
{
    use HasFactory;

    protected $table = 'gp_attendances';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(GpMeeting::class, 'meeting_id');
    }
}
