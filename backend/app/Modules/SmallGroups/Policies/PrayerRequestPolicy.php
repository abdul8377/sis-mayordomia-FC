<?php

namespace App\Modules\SmallGroups\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\SmallGroups\Models\GpMeeting;
use App\Modules\SmallGroups\Models\PrayerRequest;

class PrayerRequestPolicy
{
    public function view(User $user, PrayerRequest $prayer): bool
    {
        return $user->hasRole('gp_leader') && $user->leadership?->group_id === GpMeeting::findOrFail($prayer->meeting_id)->group_id;
    }
}
