<?php
namespace App\Modules\YouthMinistry\Policies;
use App\Modules\Identity\Models\User;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Support\Access;
class ActivityPolicy
{
    public function update(User $user, JaActivity $activity): bool { return Access::manageJa($user); }
}
