<?php

namespace App\Modules\SmallGroups\Actions;

use App\Modules\People\Models\Person;
use App\Modules\SmallGroups\Models\GroupMembership;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Support\Audit\Audit;
use Illuminate\Support\Facades\DB;

class AssignMembership
{
    public function handle(Person $person, ?int $groupId, int $actor): void
    {
        DB::transaction(function () use ($person, $groupId, $actor): void {
            Person::whereKey($person->id)->lockForUpdate()->firstOrFail();
            if ($groupId !== null) {
                abort_unless(SmallGroup::findOrFail($groupId)->status === 'active', 422, 'El grupo debe estar activo.');
            }
            $current = GroupMembership::where('person_id', $person->id)->whereNull('ends_at')->first();
            if ($current?->group_id === $groupId) {
                return;
            }
            if ($current) {
                $current->update(['ends_at' => now()]);
            }
            if ($groupId !== null) {
                GroupMembership::create(['person_id' => $person->id, 'group_id' => $groupId, 'starts_at' => now(), 'assigned_by' => $actor]);
            }
            Audit::record('membership.changed', $person, ['previous_group_id' => $current?->group_id, 'group_id' => $groupId], $actor);
        });
    }
}
