<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GpMeeting extends Model
{
    use HasFactory;

    protected $table = 'gp_meetings';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SmallGroup::class, 'group_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(GpAttendance::class, 'meeting_id');
    }
}
