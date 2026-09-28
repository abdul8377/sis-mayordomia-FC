<?php

namespace App\Modules\SmallGroups\Actions;

use App\Modules\Configuration\Models\SystemSetting;
use App\Modules\SmallGroups\Models\AbsenceAlert;
use App\Modules\SmallGroups\Models\GpAttendance;
use App\Modules\SmallGroups\Models\GroupMembership;

class RecalculateAbsences
{
    public function handle(int $groupId): void
    {
        $threshold = (int) (SystemSetting::where('key', 'absence_threshold')->value('value') ?? config('community.absence_threshold'));
        foreach (GroupMembership::where('group_id', $groupId)->whereNull('ends_at')->whereHas('person', fn ($q) => $q->where('status', 'active'))->get() as $membership) {
            $records = GpAttendance::where('membership_id', $membership->id)->whereHas('meeting', fn ($q) => $q->where('status', 'closed'))->with('meeting')->get()->sortByDesc(fn ($a) => $a->meeting->starts_at->timestamp);
            $missed = $records->takeWhile(fn ($a) => $a->status === 'absent');
            if ($missed->count() < $threshold) {
                AbsenceAlert::where('membership_id', $membership->id)->whereIn('status', ['open', 'contacted'])->update(['status' => 'resolved']);

                continue;
            }
            $first = $missed->last()->meeting_id;
            $last = $missed->first()->meeting_id;
            $alert = AbsenceAlert::firstOrCreate(['membership_id' => $membership->id, 'first_missed_meeting_id' => $first], ['last_missed_meeting_id' => $last, 'consecutive_count' => $missed->count(), 'status' => 'open']);
            $alert->update(['consecutive_count' => $missed->count(), 'last_missed_meeting_id' => $last]);
        }
    }
}
