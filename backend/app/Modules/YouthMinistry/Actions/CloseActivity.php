<?php

namespace App\Modules\YouthMinistry\Actions;

use App\Modules\Identity\Models\User;
use App\Modules\Participation\Models\Commitment;
use App\Modules\Participation\Models\JaAttendance;
use App\Modules\Participation\Models\Participation;
use App\Modules\SmallGroups\Models\GroupMembership;
use App\Modules\YouthMinistry\Models\ActivityResult;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Support\Audit\Audit;
use Illuminate\Support\Facades\DB;

class CloseActivity
{
    public function handle(JaActivity $activity, array $data, User $actor): JaActivity
    {
        return DB::transaction(function () use ($activity, $data, $actor): JaActivity {
            $activity = JaActivity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            abort_unless($activity->status === 'published', 409, 'La actividad debe estar publicada para registrar el cierre.');
            abort_if($activity->starts_at->isFuture(), 422, 'No se puede cerrar una actividad futura.');
            $present = collect($data['attendees'])->where('status', 'present')->pluck('person_id');
            $opportunities = $activity->opportunities->keyBy('id');
            foreach ($data['participations'] as $p) {
                abort_unless($present->contains($p['person_id']) && $opportunities->has($p['opportunity_id']), 422, 'Cada participación debe corresponder a un asistente presente y a una oportunidad de esta actividad.');
            }
            $old = JaAttendance::where('activity_id', $activity->id)->get();
            foreach ($old as $row) {
                $row->update(['status' => 'absent']);
                $row->participations()->whereNull('revoked_at')->update(['revoked_at' => now()]);
            }
            foreach ($data['attendees'] as $row) {
                $membership = GroupMembership::where('person_id', $row['person_id'])->where('starts_at', '<=', $activity->starts_at)->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $activity->starts_at))->first();
                JaAttendance::updateOrCreate(['activity_id' => $activity->id, 'person_id' => $row['person_id']], ['status' => $row['status'], 'membership_id' => $membership?->id, 'recorded_by' => $actor->id]);
            }
            foreach (collect($data['participations'])->unique(fn ($p) => $p['person_id'].'-'.$p['opportunity_id']) as $row) {
                $attendance = JaAttendance::where('activity_id', $activity->id)->where('person_id', $row['person_id'])->firstOrFail();
                $commitment = Commitment::where('opportunity_id', $row['opportunity_id'])->where('person_id', $row['person_id'])->first();
                $participation = Participation::updateOrCreate(['ja_attendance_id' => $attendance->id, 'opportunity_id' => $row['opportunity_id']], ['commitment_id' => $commitment?->id, 'confirmed_by' => $actor->id, 'confirmed_at' => now(), 'revoked_at' => null]);
                $talent = $opportunities[$row['opportunity_id']]->talent_id;
                if ($talent && DB::table('person_talents')->where('person_id', $row['person_id'])->where('talent_id', $talent)->where('kind', 'possesses')->exists()) {
                    DB::table('participation_talents')->insertOrIgnore(['participation_id' => $participation->id, 'talent_id' => $talent]);
                }
            }
            ActivityResult::updateOrCreate(['activity_id' => $activity->id], [...collect($data)->only(['summary', 'beneficiary_count', 'final_note'])->all(), 'closed_by' => $actor->id, 'closed_at' => now()]);
            $activity->update(['status' => 'completed']);
            Audit::record('activity.closed', $activity, ['title' => $activity->title, 'present' => $present->count(), 'participations' => count($data['participations'])]);

            return $activity;
        }, 3);
    }
}
