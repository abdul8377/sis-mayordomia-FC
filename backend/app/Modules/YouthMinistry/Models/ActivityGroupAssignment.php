<?php

namespace App\Modules\YouthMinistry\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityGroupAssignment extends Model
{
    use HasFactory;

    protected $table = 'activity_group_assignments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\SmallGroups\Models\SmallGroup::class, 'group_id');
    }
}
