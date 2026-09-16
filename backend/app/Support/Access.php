<?php
namespace App\Support;

use App\Modules\Identity\Models\User;
use App\Modules\People\Models\Person;
use Illuminate\Database\Eloquent\Builder;

final class Access
{
    public static function manageJa(User $user): bool { return $user->hasRole('admin', 'ja_director'); }
    public static function group(User $user, int $groupId): bool { return $user->hasRole('admin') || ($user->hasRole('gp_leader') && $user->leadership?->group_id === $groupId); }
    public static function editPerson(User $user, Person $person): bool { return $user->hasRole('admin') || ($user->hasRole('gp_leader') && $person->membership?->group_id === $user->leadership?->group_id && $user->leadership !== null); }
    public static function people(User $user): Builder
    {
        $query = Person::query();
        if (self::manageJa($user)) { return $query; }
        if ($user->hasRole('gp_leader') && $user->leadership) { return $query->whereHas('membership', fn (Builder $q) => $q->where('group_id', $user->leadership->group_id)); }
        return $query->whereKey($user->person_id);
    }
    public static function reportGroup(User $user): ?int
    {
        if (self::manageJa($user)) { return null; }
        return $user->leadership?->group_id ?? $user->person->membership?->group_id ?? -1;
    }
}
