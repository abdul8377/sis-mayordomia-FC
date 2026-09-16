<?php
namespace App\Modules\Service\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Service\Models\ServiceNeed;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Modules\YouthMinistry\Models\ActivityType;
use App\Modules\People\Http\Resources\PersonResource;
use App\Support\Access;
use App\Support\Audit\Audit;
use App\Support\Dates\CycleDate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
class ServiceNeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin','ja_director','gp_leader'),403);$q=ServiceNeed::with('responsible');
        if (!Access::manageJa($request->user())) { $q->where('created_by',$request->user()->id); }
        return response()->json(['data'=>$q->latest()->get()->map(fn ($n)=>[...$n->only(['id','description','reported_at','status','activity_id']),'responsible'=>new PersonResource($n->responsible)])]);
    }
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin','ja_director','gp_leader'),403);$data=$request->validate(['description'=>['required','string','max:2000'],'responsible_person_id'=>['required','integer','exists:people,id']]);
        abort_unless(Access::people($request->user())->whereKey($data['responsible_person_id'])->exists(),403);
        $need=ServiceNeed::create([...$data,'reported_at'=>now(),'status'=>'open','created_by'=>$request->user()->id]);Audit::record('service_need.created',$need);return response()->json(['data'=>$need],201);
    }
    public function convert(Request $request, ServiceNeed $need): JsonResponse
    {
        abort_unless(Access::manageJa($request->user()),403);$data=$request->validate(['title'=>['required','string','max:180'],'starts_at'=>['required','date'],'ends_at'=>['required','date','after:starts_at'],'place'=>['nullable','string','max:255']]);
        $activity=DB::transaction(function () use ($request,$need,$data) { $n=ServiceNeed::whereKey($need->id)->lockForUpdate()->firstOrFail();if ($n->activity_id) { return JaActivity::findOrFail($n->activity_id); }$a=JaActivity::create([...$data,'starts_at'=>\Carbon\Carbon::parse($data['starts_at'])->utc(),'ends_at'=>\Carbon\Carbon::parse($data['ends_at'])->utc(),'description'=>$n->description,'activity_type_id'=>ActivityType::firstOrCreate(['name'=>'Servicio'])->id,'cycle_id'=>CycleDate::forDate($data['starts_at'])->id,'status'=>'draft','created_by'=>$request->user()->id]);$n->update(['activity_id'=>$a->id,'status'=>'planned']);Audit::record('service_need.converted',$n,['activity_id'=>$a->id]);return $a; });
        return response()->json(['data'=>['id'=>$activity->id]],201);
    }
}
