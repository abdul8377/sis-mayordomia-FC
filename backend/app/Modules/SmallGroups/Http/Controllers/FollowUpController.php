<?php
namespace App\Modules\SmallGroups\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\SmallGroups\Models\AbsenceAlert;
use App\Modules\SmallGroups\Models\AbsenceFollowUp;
use App\Modules\People\Http\Resources\PersonResource;
use App\Support\Access;
use App\Support\Audit\Audit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
class FollowUpController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin','gp_leader'),403);$q=AbsenceAlert::with(['membership.person','membership.group','followUps'])->whereIn('status',['open','contacted'])->whereHas('membership',fn ($q)=>$q->whereNull('ends_at')->whereHas('person',fn ($p)=>$p->where('status','active')));
        if (!$request->user()->hasRole('admin')) { $q->whereHas('membership',fn ($q)=>$q->where('group_id',$request->user()->leadership?->group_id??-1)); }
        return response()->json(['data'=>$q->get()->map(fn ($a)=>['id'=>$a->id,'consecutive_count'=>$a->consecutive_count,'status'=>$a->status,'person'=>new PersonResource($a->membership->person),'group_name'=>$a->membership->group->name,'last_contact_at'=>$a->followUps->max('performed_at')])]);
    }
    public function store(Request $request, AbsenceAlert $alert): JsonResponse
    {
        abort_unless(Access::group($request->user(),$alert->membership->group_id),403);$data=$request->validate(['action'=>['required','in:contacted,message_opened']]);
        $phone=preg_replace('/\D/','',$alert->membership->person->phone??'');if ($data['action']==='message_opened') { abort_unless(strlen($phone)>=8,422,'Esta persona no tiene un teléfono válido.'); }
        DB::transaction(function () use ($alert,$request,$data): void { $locked=AbsenceAlert::whereKey($alert->id)->lockForUpdate()->firstOrFail();abort_if($locked->status==='resolved',409,'La alerta ya se resolvió.');AbsenceFollowUp::create(['alert_id'=>$alert->id,'action'=>$data['action'],'performed_by'=>$request->user()->id,'performed_at'=>now()]);$locked->update(['status'=>'contacted']);Audit::record('follow_up.recorded',$locked,['action'=>$data['action']]); });
        $name=explode(' ',$alert->membership->person->full_name)[0];
        return response()->json(['message'=>'Contacto registrado.','contact_url'=>$data['action']==='message_opened'?'https://wa.me/'.$phone.'?text='.rawurlencode("Hola, {$name}. ¿Cómo estás? Queríamos saludarte y saber cómo te encuentras."):null]);
    }
}
