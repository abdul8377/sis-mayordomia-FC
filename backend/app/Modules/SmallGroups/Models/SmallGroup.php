<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmallGroup extends Model
{
    use HasFactory;

    protected $table = 'small_groups';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function memberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupMembership::class, 'group_id')->whereNull('ends_at');
    }

    public function leadership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GroupLeadership::class, 'group_id')->whereNull('ends_at');
    }
}
