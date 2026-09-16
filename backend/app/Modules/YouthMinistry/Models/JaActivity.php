<?php

namespace App\Modules\YouthMinistry\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JaActivity extends Model
{
    use HasFactory;

    protected $table = 'ja_activities';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_primary' => 'boolean', 'archived_at' => 'datetime'];
    }

    public function activityType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function assignment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ActivityGroupAssignment::class, 'activity_id')->whereNull('ended_at');
    }

    public function opportunities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Opportunity::class, 'activity_id');
    }

    public function result(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ActivityResult::class, 'activity_id');
    }

    public function attendees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Participation\Models\JaAttendance::class, 'activity_id');
    }
}
