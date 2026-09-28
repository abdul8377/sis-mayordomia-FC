<?php

namespace App\Modules\SmallGroups\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Support\Access;

class GroupPolicy
{
    public function update(User $user, SmallGroup $group): bool
    {
        return Access::group($user, $group->id);
    }
}
