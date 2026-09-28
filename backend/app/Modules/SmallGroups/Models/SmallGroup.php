<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SmallGroup extends Model
{
    use HasFactory;

    protected $table = 'small_groups';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class, 'group_id')->whereNull('ends_at');
    }

    public function leadership(): HasOne
    {
        return $this->hasOne(GroupLeadership::class, 'group_id')->whereNull('ends_at');
    }
}
