<?php
namespace App\Modules\Surveys\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Surveys\Models\Survey;
use App\Modules\Surveys\Models\SurveyResponse;
use App\Modules\People\Models\Person;
use App\Support\Access;
use App\Support\Audit\Audit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
class SurveyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data'=>Survey::with(['options','responses'])->latest()->get()->map(function ($s) use ($request) {
            $response=$s->responses->firstWhere('person_id',$request->user()->person_id);$responses=$s->responses;
            $group=$request->filled('group_id')?$request->integer('group_id'):null;
            if ($group) { abort_unless(Access::manageJa($request->user())||Access::group($request->user(),$group),403);$responses=$responses->filter(fn ($r)=>DB::table('group_memberships')->where('id',$r->membership_id)->where('group_id',$group)->exists()); }
            $total=$responses->count();$counts=DB::table('survey_response_options')->whereIn('response_id',$responses->pluck('id'))->selectRaw('option_id, COUNT(*) as total')->groupBy('option_id')->pluck('total','option_id');
            return [...$s->only(['id','title','description','closes_at']),'status'=>$s->status==='closed'||$s->closes_at->isPast()?'closed':($s->opens_at->isFuture()?'scheduled':'open'),'responses_count'=>$total,'options'=>$s->options->map(fn ($o)=>['id'=>$o->id,'label'=>$o->label,'count'=>(int)($counts[$o->id]??0),'percentage'=>$total?round(($counts[$o->id]??0)/$total*100,1):0]),'selected_options'=>$response?DB::table('survey_response_options')->where('response_id',$response->id)->pluck('option_id'):[]];
        })]);
    }
    public function store(Request $request): JsonResponse
    {
        abort_unless(Access::manageJa($request->user()),403);$data=$request->validate(['title'=>['required','string','max:180'],'description'=>['nullable','string','max:1000'],'closes_at'=>['required','date','after:now'],'options'=>['required','array','min:2','max:12'],'options.*'=>['required','string','distinct','max:100']]);
        $survey=DB::transaction(function () use ($data,$request) { $s=Survey::create([...collect($data)->except('options')->all(),'opens_at'=>now(),'status'=>'open','created_by'=>$request->user()->id]);foreach ($data['options'] as $i=>$label) { $s->options()->create(['label'=>$label,'position'=>$i]); }Audit::record('survey.created',$s);return $s; });
        return response()->json(['data'=>$survey],201);
    }
    public function respond(Request $request, Survey $survey): JsonResponse
    {
        $data=$request->validate(['option_ids'=>['required','array','min:1'],'option_ids.*'=>['required','integer','distinct']]);
        DB::transaction(function () use ($data,$survey,$request): void { $person=Person::whereKey($request->user()->person_id)->lockForUpdate()->firstOrFail();$survey=Survey::whereKey($survey->id)->lockForUpdate()->firstOrFail();abort_unless($survey->status==='open'&&$survey->opens_at->lte(now())&&$survey->closes_at->isFuture(),409,'Esta encuesta no está abierta.');abort_unless($survey->options()->whereIn('id',$data['option_ids'])->count()===count($data['option_ids']),422,'Las opciones deben pertenecer a esta encuesta.');$response=SurveyResponse::updateOrCreate(['survey_id'=>$survey->id,'person_id'=>$person->id],['membership_id'=>$person->membership?->id,'submitted_at'=>now()]);DB::table('survey_response_options')->where('response_id',$response->id)->delete();foreach ($data['option_ids'] as $id) { DB::table('survey_response_options')->insert(['response_id'=>$response->id,'option_id'=>$id]); }Audit::record('survey.responded',$response); });
        return response()->json(['message'=>'Gracias por compartir tus intereses.']);
    }
}
