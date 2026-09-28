<?php

namespace App\Modules\People\Policies;

use App\Modules\Identity\Models\User;
use App\Modules\People\Models\Person;
use App\Support\Access;

class PersonPolicy
{
    public function update(User $user, Person $person): bool
    {
        return Access::editPerson($user, $person);
    }

    public function profile(User $user, Person $person): bool
    {
        return $user->person_id === $person->id || Access::editPerson($user, $person);
    }
}
