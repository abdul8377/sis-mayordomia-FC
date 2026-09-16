<?php
namespace App\Modules\SmallGroups\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Modules\SmallGroups\Models\GroupLeadership;
use App\Support\Access;
use App\Support\Audit\Audit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
class GroupController extends Controller
{
    public static function present(SmallGroup $group): array { return [...$group->only(['id','name','description','meeting_place','usual_weekday','usual_time','status','color']), 'members_count'=>$group->memberships_count ?? $group->memberships()->count(), 'leader'=>$group->leadership?->user?->person?->only('full_name')]; }
    public function index(Request $request): JsonResponse
    {
        $q=SmallGroup::with('leadership.user.person')->withCount('memberships');
        if (!Access::manageJa($request->user())) { $q->whereKey(Access::reportGroup($request->user())); }
        return response()->json(['data'=>$q->orderBy('name')->get()->map(fn ($g)=>self::present($g))]);
    }
    public function store(Request $request): JsonResponse { abort_unless($request->user()->hasRole('admin'),403); return $this->save($request,new SmallGroup); }
    public function update(Request $request, SmallGroup $group): JsonResponse { Gate::authorize('update',$group); return $this->save($request,$group); }
    private function save(Request $request, SmallGroup $group): JsonResponse
    {
        $data=$request->validate(['name'=>['required','string','max:150',\Illuminate\Validation\Rule::unique('small_groups')->ignore($group->id)],'description'=>['nullable','string','max:1000'],'meeting_place'=>['nullable','string','max:255'],'usual_weekday'=>['required','integer','between:0,6'],'usual_time'=>['required','date_format:H:i'],'status'=>['required','in:active,inactive'],'leader_user_id'=>['nullable','integer','exists:users,id']]);
        DB::transaction(function () use ($request,$group,$data): void {
            if ($group->exists) { SmallGroup::whereKey($group->id)->lockForUpdate()->firstOrFail(); }
            if ($data['status']==='inactive') { abort_if($group->memberships()->exists(),422,'Traslada a los miembros antes de desactivar el grupo.'); }
            $group->fill(collect($data)->except('leader_user_id')->all())->save();
            if (array_key_exists('leader_user_id',$data)) {
                abort_unless($request->user()->hasRole('admin'),403);
                $current=GroupLeadership::where('group_id',$group->id)->whereNull('ends_at')->first();
                if (($current?->user_id)!==($data['leader_user_id']??null)) {
                    if ($data['leader_user_id']) { $leader=User::whereKey($data['leader_user_id'])->lockForUpdate()->firstOrFail(); abort_unless($leader->hasRole('gp_leader') && $leader->status==='active',422,'Selecciona una cuenta activa con rol de líder.'); abort_if($leader->leadership,422,'Este líder ya tiene un grupo asignado.'); }
                    $current?->update(['ends_at'=>now()]);
                    if ($data['leader_user_id']) { GroupLeadership::create(['group_id'=>$group->id,'user_id'=>$data['leader_user_id'],'starts_at'=>now(),'assigned_by'=>$request->user()->id]); }
                }
            }
            Audit::record('group.saved',$group);
        });
        return response()->json(['data'=>self::present($group->fresh())]);
    }
}
