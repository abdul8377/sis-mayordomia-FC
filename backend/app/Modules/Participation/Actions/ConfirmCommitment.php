<?php
namespace App\Modules\Participation\Actions;
use App\Modules\Identity\Models\User;
use App\Modules\People\Models\Person;
use App\Modules\YouthMinistry\Models\Opportunity;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Modules\Participation\Models\Commitment;
use App\Support\Access;
use App\Support\Audit\Audit;
use Illuminate\Support\Facades\DB;
class ConfirmCommitment
{
    public function handle(Opportunity $opportunity, int $personId, User $actor): Commitment
    {
        return DB::transaction(function () use ($opportunity,$personId,$actor): Commitment {
            $person=Person::whereKey($personId)->lockForUpdate()->firstOrFail();
            abort_unless($actor->person_id===$personId || Access::editPerson($actor,$person),403);
            abort_unless($person->status==='active',422,'La persona debe estar activa.');
            $activity=JaActivity::whereKey($opportunity->activity_id)->lockForUpdate()->firstOrFail();
            $opportunity=Opportunity::whereKey($opportunity->id)->lockForUpdate()->firstOrFail();
            abort_unless($activity->status==='published' && !$opportunity->closed_at && $opportunity->ends_at->isFuture(),409,'Esta oportunidad ya no admite compromisos.');
            $existing=Commitment::where('opportunity_id',$opportunity->id)->where('person_id',$personId)->first();
            if ($existing?->status==='confirmed') { return $existing; }
            abort_if(Commitment::where('opportunity_id',$opportunity->id)->where('status','confirmed')->count()>=$opportunity->capacity,409,'El último cupo ya fue reservado. Elige otra oportunidad.');
            $conflict=Commitment::where('person_id',$personId)->where('status','confirmed')->whereHas('opportunity',fn ($q)=>$q->where('starts_at','<',$opportunity->ends_at)->where('ends_at','>',$opportunity->starts_at)->whereHas('activity',fn ($a)=>$a->where('status','published')))->exists();
            abort_if($conflict,409,'Ya existe un compromiso que coincide con este horario.');
            $commitment=Commitment::updateOrCreate(['opportunity_id'=>$opportunity->id,'person_id'=>$personId],['status'=>'confirmed','accepted_at'=>now(),'acceptance_source'=>$actor->person_id===$personId?'self':'verbal','recorded_by'=>$actor->id,'cancelled_at'=>null]);
            Audit::record('commitment.confirmed',$commitment,['person_id'=>$personId,'opportunity_id'=>$opportunity->id],$actor->id);return $commitment;
        },3);
    }
}
