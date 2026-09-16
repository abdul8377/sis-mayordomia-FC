<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbsenceAlert extends Model
{
    use HasFactory;

    protected $table = 'absence_alerts';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function membership(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GroupMembership::class);
    }

    public function followUps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AbsenceFollowUp::class, 'alert_id');
    }
}
