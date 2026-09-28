<?php

namespace App\Modules\YouthMinistry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Participation\Models\Commitment;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Modules\YouthMinistry\Actions\CloseActivity;
use App\Modules\YouthMinistry\Http\Requests\SaveActivityRequest;
use App\Modules\YouthMinistry\Http\Resources\ActivityResource;
use App\Modules\YouthMinistry\Models\ActivityGroupAssignment;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Modules\YouthMinistry\Models\Opportunity;
use App\Support\Access;
use App\Support\Audit\Audit;
use App\Support\Dates\CycleDate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    public const RELATIONS = ['activityType', 'assignment.group.leadership.user.person', 'opportunities.talent', 'opportunities.commitments.person.talents', 'opportunities.commitments.person.interests', 'opportunities.commitments.person.learning', 'opportunities.commitments.person.availability', 'opportunities.commitments.person.membership.group', 'result', 'attendees.participations'];

    public function index(Request $request): JsonResponse
    {
        $q = JaActivity::with(self::RELATIONS)->whereNull('archived_at');
        if (! Access::manageJa($request->user())) {
            $q->where('status', '!=', 'draft');
        }
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        return response()->json(['data' => ActivityResource::collection($q->orderBy('starts_at', 'desc')->limit(100)->get())]);
    }

    public function show(Request $request, JaActivity $activity): ActivityResource
    {
        abort_if($activity->status === 'draft' && ! Access::manageJa($request->user()), 403);

        return new ActivityResource($activity->load(self::RELATIONS));
    }

    public function store(SaveActivityRequest $request): ActivityResource
    {
        return $this->save($request, new JaActivity);
    }

    public function update(SaveActivityRequest $request, JaActivity $activity): ActivityResource
    {
        Gate::authorize('update', $activity);

        return $this->save($request, $activity);
    }

    private function save(SaveActivityRequest $request, JaActivity $activity): ActivityResource
    {
        $data = $request->validated();
        $start = Carbon::parse($data['starts_at'])->utc();
        $end = Carbon::parse($data['ends_at'])->utc();
        abort_if($data['is_primary'] && ! $start->copy()->setTimezone(config('community.timezone'))->isSaturday(), 422, 'La actividad principal debe realizarse un sábado.');
        if ($data['group_id'] ?? null) {
            abort_unless(SmallGroup::findOrFail($data['group_id'])->status === 'active', 422, 'El GP responsable debe estar activo.');
        }
        DB::transaction(function () use ($request, $activity, $data, $start, $end): void {
            if ($activity->exists) {
                JaActivity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
                abort_if($activity->status === 'completed', 409, 'Reabre la actividad antes de editarla.');
                $rescheduling = ! $activity->starts_at->equalTo($start) || ! $activity->ends_at->equalTo($end);
                if ($rescheduling) {
                    abort_if(Commitment::whereHas('opportunity', fn ($q) => $q->where('activity_id', $activity->id))->where('status', 'confirmed')->exists(), 409, 'Cancela los compromisos antes de cambiar el horario.');
                    abort_if($activity->opportunities()->exists(), 409, 'Las actividades con oportunidades conservan su horario. Crea otra actividad para reprogramar.');
                }
            }
            $cycle = CycleDate::forDate($data['starts_at']);
            if ($data['is_primary'] && $data['status'] !== 'cancelled') {
                $cycle->newQuery()->whereKey($cycle->id)->lockForUpdate()->first();
                abort_if(JaActivity::where('cycle_id', $cycle->id)->where('is_primary', true)->where('status', '!=', 'cancelled')->when($activity->exists, fn ($q) => $q->where('id', '!=', $activity->id))->exists(), 409, 'Ya existe una actividad principal para ese sábado.');
            }
            $activity->fill([...collect($data)->except('group_id')->all(), 'starts_at' => $start, 'ends_at' => $end, 'cycle_id' => $cycle->id, 'created_by' => $activity->created_by ?? $request->user()->id])->save();
            $current = $activity->assignment;
            $next = $data['group_id'] ?? null;
            if ($current?->group_id !== $next) {
                $current?->update(['ended_at' => now()]);
                if ($next) {
                    ActivityGroupAssignment::create(['activity_id' => $activity->id, 'group_id' => $next, 'assigned_at' => now(), 'assigned_by' => $request->user()->id]);
                }
            }
            if ($activity->status === 'cancelled') {
                foreach (Commitment::whereHas('opportunity', fn ($q) => $q->where('activity_id', $activity->id))->where('status', 'confirmed')->get() as $c) {
                    $c->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                    Audit::record('commitment.cancelled', $c, ['reason' => 'activity_cancelled']);
                }
            }
            Audit::record('activity.saved', $activity, ['title' => $activity->title, 'status' => $activity->status]);
        }, 3);

        return new ActivityResource($activity->fresh(self::RELATIONS));
    }

    public function opportunity(Request $request, JaActivity $activity): JsonResponse
    {
        Gate::authorize('update', $activity);
        $data = $request->validate(['title' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1500'], 'responsibility_id' => ['required', 'integer', 'exists:responsibilities,id'], 'capacity' => ['required', 'integer', 'min:1', 'max:500'], 'talent_id' => ['nullable', 'integer', 'exists:talents,id'], 'coordinator_person_id' => ['nullable', 'integer', 'exists:people,id'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at']]);
        $o = DB::transaction(function () use ($activity, $data) {
            $locked = JaActivity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status, ['draft', 'published']), 409, 'Esta actividad no admite nuevas oportunidades.');
            $start = isset($data['starts_at']) ? Carbon::parse($data['starts_at'])->utc() : $locked->starts_at;
            $end = isset($data['ends_at']) ? Carbon::parse($data['ends_at'])->utc() : $locked->ends_at;
            abort_if($start->lt($locked->starts_at) || $end->gt($locked->ends_at) || $end->lte($start), 422, 'El horario debe estar dentro de la actividad.');
            $o = Opportunity::create([...$data, 'activity_id' => $activity->id, 'starts_at' => $start, 'ends_at' => $end]);
            Audit::record('opportunity.created', $o);

            return $o;
        });

        return response()->json(['data' => $o], 201);
    }

    public function close(Request $request, JaActivity $activity, CloseActivity $action): ActivityResource
    {
        Gate::authorize('update', $activity);
        $data = $request->validate(['summary' => ['required', 'string', 'max:3000'], 'beneficiary_count' => ['nullable', 'integer', 'min:0'], 'final_note' => ['nullable', 'string', 'max:2000'], 'attendees' => ['required', 'array'], 'attendees.*.person_id' => ['required', 'integer', 'distinct', 'exists:people,id'], 'attendees.*.status' => ['required', 'in:present,absent'], 'participations' => ['present', 'array'], 'participations.*.person_id' => ['required', 'integer', 'exists:people,id'], 'participations.*.opportunity_id' => ['required', 'integer', 'exists:opportunities,id']]);

        return new ActivityResource($action->handle($activity, $data, $request->user())->load(self::RELATIONS));
    }

    public function reopen(Request $request, JaActivity $activity): JsonResponse
    {
        Gate::authorize('update', $activity);
        $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        DB::transaction(function () use ($activity, $request): void {
            $a = JaActivity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            abort_unless($a->status === 'completed', 409);
            $a->update(['status' => 'published']);
            Audit::record('activity.reopened', $a, ['reason' => $request->input('reason')]);
        });

        return response()->json(['message' => 'Actividad reabierta para corregir resultados.']);
    }

    public function rotation(Request $request): JsonResponse
    {
        abort_unless(Access::manageJa($request->user()), 403);
        $scheduled = ActivityGroupAssignment::whereNull('ended_at')->whereIn('activity_id', JaActivity::where('status', 'published')->where('starts_at', '>=', now())->select('id'))->pluck('group_id');
        $groups = SmallGroup::where('status', 'active')->whereNotIn('id', $scheduled)->get()->map(function ($g) {
            $last = DB::table('activity_group_assignments as a')->join('ja_activities as j', 'j.id', '=', 'a.activity_id')->where('a.group_id', $g->id)->whereNull('a.ended_at')->where('j.status', 'completed')->max('j.starts_at');

            return ['id' => $g->id, 'name' => $g->name, 'last_turn' => $last];
        })->sortBy(fn ($g) => $g['last_turn'] ?? '0000')->values();

        return response()->json(['data' => $groups]);
    }
}
