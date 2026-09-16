<?php
namespace App\Modules\SmallGroups\Actions;
use App\Modules\Identity\Models\User;
use App\Modules\SmallGroups\Models\GpMeeting;
use App\Modules\SmallGroups\Models\GpAttendance;
use App\Modules\SmallGroups\Models\GroupMembership;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Support\Access;
use App\Support\Audit\Audit;
use App\Support\Dates\CycleDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaveMeeting
{
    public function handle(array $data, User $actor, ?GpMeeting $meeting = null): GpMeeting
    {
        abort_unless(Access::group($actor,(int)$data['group_id']),403);
        abort_unless(SmallGroup::findOrFail($data['group_id'])->status==='active',422,'Este grupo está inactivo.');
        return DB::transaction(function () use ($data,$actor,$meeting): GpMeeting {
            SmallGroup::whereKey($data['group_id'])->lockForUpdate()->firstOrFail();
            if ($meeting) { $meeting=GpMeeting::whereKey($meeting->id)->lockForUpdate()->firstOrFail();abort_unless($meeting->group_id===(int)$data['group_id'],422);abort_unless($meeting->status==='open',409,'La reunión ya está cerrada.'); }
            $date=Carbon::parse($data['starts_at'])->utc();
            if ($meeting) { abort_unless($meeting->starts_at->equalTo($date),422,'La fecha de una reunión abierta no se puede cambiar.'); }
            if (!$meeting) {
                $meeting=GpMeeting::firstOrCreate(['group_id'=>$data['group_id'],'starts_at'=>$date],['cycle_id'=>CycleDate::forDate($data['starts_at'])->id,'status'=>'open']);
                abort_unless($meeting->status==='open',409,'Ya existe una reunión cerrada en ese horario.');
                $members=GroupMembership::where('group_id',$data['group_id'])->where('starts_at','<=',$date)->where(fn ($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>',$date))->whereHas('person',fn ($q)=>$q->where('status','active'))->get();
                foreach ($members as $member) { GpAttendance::firstOrCreate(['meeting_id'=>$meeting->id,'person_id'=>$member->person_id],['membership_id'=>$member->id,'status'=>'unrecorded','recorded_by'=>$actor->id]); }
            }
            foreach ($data['attendances']??[] as $attendance) {
                $row=GpAttendance::where('meeting_id',$meeting->id)->where('person_id',$attendance['person_id'])->first();
                abort_unless($row,422,'Agrega a los invitados antes de registrar su asistencia.');
                $row->update(['status'=>$attendance['status'],'recorded_by'=>$actor->id]);
            }
            $meeting->next_activity_note=$data['next_activity_note']??null;
            if ($data['close']??false) { abort_if($meeting->attendances()->where('status','unrecorded')->exists(),422,'Completa la asistencia antes de cerrar la reunión.');abort_if($date->isFuture(),422,'No puedes cerrar una reunión futura.');$meeting->status='closed';$meeting->closed_at=now();$meeting->closed_by=$actor->id; }
            $meeting->save();Audit::record($meeting->status==='closed'?'meeting.closed':'meeting.saved',$meeting);
            if ($meeting->status==='closed') { app(RecalculateAbsences::class)->handle($meeting->group_id); }
            return $meeting->fresh(['group','attendances.person']);
        },3);
    }
}
