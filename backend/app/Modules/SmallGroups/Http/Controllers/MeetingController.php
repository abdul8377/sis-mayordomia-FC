<?php
namespace App\Modules\SmallGroups\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\SmallGroups\Models\GpMeeting;
use App\Modules\SmallGroups\Models\GpAttendance;
use App\Modules\SmallGroups\Models\PrayerRequest;
use App\Modules\SmallGroups\Actions\SaveMeeting;
use App\Modules\SmallGroups\Http\Requests\SaveMeetingRequest;
use App\Modules\People\Http\Resources\PersonResource;
use App\Modules\People\Models\Person;
use App\Support\Access;
use App\Support\Audit\Audit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
class MeetingController extends Controller
{
    private function present(GpMeeting $meeting): array { return [...$meeting->only(['id','group_id','starts_at','status','next_activity_note']),'group'=>GroupController::present($meeting->group),'attendances'=>$meeting->attendances->map(fn ($a)=>['person_id'=>$a->person_id,'status'=>$a->status,'person'=>new PersonResource($a->person)])]; }
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin','gp_leader'),403);$q=GpMeeting::with(['group.leadership.user.person','attendances.person.membership.group','attendances.person.talents','attendances.person.interests','attendances.person.learning','attendances.person.availability']);
        if (!$request->user()->hasRole('admin')) { $q->where('group_id',$request->user()->leadership?->group_id??-1); }
        if ($request->filled('group_id')) { $q->where('group_id',$request->integer('group_id')); }
        return response()->json(['data'=>$q->latest('starts_at')->limit(50)->get()->map(fn ($m)=>$this->present($m))]);
    }
    public function store(SaveMeetingRequest $request, SaveMeeting $action): JsonResponse { return response()->json(['data'=>$this->present($action->handle($request->validated(),$request->user()))],201); }
    public function update(SaveMeetingRequest $request, GpMeeting $meeting, SaveMeeting $action): JsonResponse { return response()->json(['data'=>$this->present($action->handle($request->validated(),$request->user(),$meeting))]); }
    public function guest(Request $request, GpMeeting $meeting): JsonResponse
    {
        abort_unless(Access::group($request->user(),$meeting->group_id),403);$data=$request->validate(['person_id'=>['nullable','integer','exists:people,id'],'full_name'=>['required_without:person_id','nullable','string','max:150']]);
        DB::transaction(function () use ($data,$meeting,$request): void { $locked=GpMeeting::whereKey($meeting->id)->lockForUpdate()->firstOrFail();abort_unless($locked->status==='open',409);$person=isset($data['person_id'])?Person::findOrFail($data['person_id']):Person::create(['full_name'=>$data['full_name'],'status'=>'active']);abort_unless($person->status==='active',422);GpAttendance::firstOrCreate(['meeting_id'=>$meeting->id,'person_id'=>$person->id],['status'=>'present','recorded_by'=>$request->user()->id]);Audit::record('meeting.guest_added',$meeting,['person_id'=>$person->id]); });
        return response()->json(['data'=>$this->present($meeting->fresh(['group','attendances.person']))]);
    }
    public function prayers(Request $request, GpMeeting $meeting): JsonResponse
    {
        abort_unless($request->user()->hasRole('gp_leader') && $request->user()->leadership?->group_id===$meeting->group_id,403);
        return response()->json(['data'=>PrayerRequest::where('meeting_id',$meeting->id)->get()->map(fn ($p)=>['id'=>$p->id,'content'=>$p->encrypted_content,'created_at'=>$p->created_at])]);
    }
    public function prayer(Request $request, GpMeeting $meeting): JsonResponse
    {
        abort_unless($request->user()->hasRole('gp_leader') && $request->user()->leadership?->group_id===$meeting->group_id,403);$data=$request->validate(['content'=>['required','string','max:2000']]);
        $prayer=PrayerRequest::create(['meeting_id'=>$meeting->id,'encrypted_content'=>$data['content'],'created_by'=>$request->user()->id]);Audit::record('prayer.created',$prayer);
        return response()->json(['data'=>['id'=>$prayer->id]],201);
    }
}
