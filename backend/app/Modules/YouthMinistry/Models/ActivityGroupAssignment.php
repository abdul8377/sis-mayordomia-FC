<?php

namespace App\Modules\YouthMinistry\Models;

use App\Modules\SmallGroups\Models\SmallGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityGroupAssignment extends Model
{
    use HasFactory;

    protected $table = 'activity_group_assignments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SmallGroup::class, 'group_id');
    }
}
