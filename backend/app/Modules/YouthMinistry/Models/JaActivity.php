<?php

namespace App\Modules\YouthMinistry\Models;

use App\Modules\Participation\Models\JaAttendance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JaActivity extends Model
{
    use HasFactory;

    protected $table = 'ja_activities';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_primary' => 'boolean', 'archived_at' => 'datetime'];
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(ActivityGroupAssignment::class, 'activity_id')->whereNull('ended_at');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'activity_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(ActivityResult::class, 'activity_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(JaAttendance::class, 'activity_id');
    }
}
