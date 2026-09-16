<?php
namespace App\Modules\Participation\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Participation\Actions\ConfirmCommitment;
use App\Modules\Participation\Http\Requests\CommitmentRequest;
use App\Modules\Participation\Models\Commitment;
use App\Modules\Participation\Models\Participation;
use App\Modules\People\Models\Person;
use App\Modules\People\Http\Resources\PersonResource;
use App\Modules\YouthMinistry\Models\Opportunity;
use App\Support\Access;
use App\Support\Audit\Audit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
class CommitmentController extends Controller
{
    public function store(CommitmentRequest $request, Opportunity $opportunity, ConfirmCommitment $action): JsonResponse { $c=$action->handle($opportunity,$request->integer('person_id'),$request->user());return response()->json(['data'=>$c],201); }
    public function cancel(Request $request, Commitment $commitment): JsonResponse
    {
        abort_unless($request->user()->person_id===$commitment->person_id||Access::editPerson($request->user(),$commitment->person),403);
        DB::transaction(function () use ($commitment): void { Person::whereKey($commitment->person_id)->lockForUpdate()->firstOrFail();$c=Commitment::whereKey($commitment->id)->lockForUpdate()->firstOrFail();abort_unless($c->opportunity->activity->status==='published',409,'Solo se pueden cancelar compromisos de actividades abiertas.');$c->update(['status'=>'cancelled','cancelled_at'=>now()]);Audit::record('commitment.cancelled',$c); });
        return response()->json(['message'=>'Cupo liberado.']);
    }
    public function recommendations(Request $request, Opportunity $opportunity): JsonResponse
    {
        abort_unless($opportunity->activity->status==='published',404);
        $interestIds=DB::table('activity_type_interests')->where('activity_type_id',$opportunity->activity->activity_type_id)->pluck('interest_id');
        $people=Access::people($request->user())->where('status','active')->with(['talents','interests','learning','availability','membership.group'])->get();
        $items=$people->map(function ($person) use ($opportunity,$interestIds) {
            $reasons=[];
            if ($opportunity->talent_id && $person->talents->contains('id',$opportunity->talent_id)) { $reasons[]='Su talento coincide con esta oportunidad'; }
            if ($person->interests->pluck('id')->intersect($interestIds)->isNotEmpty()) { $reasons[]='Esta actividad coincide con sus intereses'; }
            if ($opportunity->talent_id && $person->learning->contains('id',$opportunity->talent_id)) { $reasons[]='Quiere aprender esta habilidad; considera acompañamiento'; }
            $recent=DB::table('participations as p')->join('ja_attendances as a','a.id','=','p.ja_attendance_id')->where('a.person_id',$person->id)->whereNull('p.revoked_at')->where('p.confirmed_at','>=',now()->subDays(90))->count();
            if ($recent===0) { $reasons[]='Una oportunidad para su primera participación reciente'; }
            $conflict=Commitment::where('person_id',$person->id)->where('status','confirmed')->whereHas('opportunity',fn ($q)=>$q->where('starts_at','<',$opportunity->ends_at)->where('ends_at','>',$opportunity->starts_at)->whereHas('activity',fn ($q)=>$q->where('status','published')))->exists();
            $localStart=$opportunity->starts_at->copy()->setTimezone(config('community.timezone'));$localEnd=$opportunity->ends_at->copy()->setTimezone(config('community.timezone'));
            $available=$person->availability->isEmpty()?null:$person->availability->contains(fn ($slot)=>(int)$slot->weekday===$localStart->dayOfWeek && $slot->starts_at<=$localStart->format('H:i:s') && $slot->ends_at>=$localEnd->format('H:i:s'));
            return ['person'=>new PersonResource($person),'reasons'=>$reasons,'recent_participations'=>$recent,'schedule_conflict'=>$conflict,'available'=>$available];
        })->filter(fn ($row)=>!$row['schedule_conflict']&&$row['available']!==false&&count($row['reasons'])>0)->sortBy('recent_participations')->values();
        return response()->json(['data'=>$items]);
    }
}
