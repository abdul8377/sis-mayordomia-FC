<?php

namespace App\Modules\People\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Participation\Models\Commitment;
use App\Modules\People\Http\Requests\SavePersonRequest;
use App\Modules\People\Http\Resources\PersonResource;
use App\Modules\People\Models\Person;
use App\Modules\SmallGroups\Actions\AssignMembership;
use App\Support\Access;
use App\Support\Audit\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PersonController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Access::people($request->user())->with(['talents', 'learning', 'interests', 'availability', 'membership.group']);
        if ($request->filled('search')) {
            $query->where('full_name', 'like', '%'.mb_substr($request->string('search'), 0, 100).'%');
        }
        if ($request->filled('group_id')) {
            $query->whereHas('membership', fn ($q) => $q->where('group_id', $request->integer('group_id')));
        }
        $page = $query->orderBy('full_name')->paginate(min(100, max(1, $request->integer('per_page', 15))));

        return response()->json(['data' => PersonResource::collection($page->items()), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()]);
    }

    public function store(SavePersonRequest $request, AssignMembership $membership): PersonResource
    {
        $data = $request->validated();
        $groupId = $data['group_id'] ?? null;
        if (! $request->user()->hasRole('admin')) {
            abort_unless($groupId && Access::group($request->user(), (int) $groupId), 403);
        }
        $person = DB::transaction(function () use ($data, $groupId, $request, $membership) {
            $person = Person::create(collect($data)->except('group_id')->all());
            $membership->handle($person, $groupId, $request->user()->id);
            Audit::record('person.created', $person);

            return $person;
        });

        return new PersonResource($person);
    }

    public function update(SavePersonRequest $request, Person $person, AssignMembership $membership): PersonResource
    {
        Gate::authorize('update', $person);
        $data = $request->validated();
        $groupId = $data['group_id'] ?? null;
        if (! $request->user()->hasRole('admin')) {
            abort_unless($groupId && Access::group($request->user(), (int) $groupId), 403);
        }
        DB::transaction(function () use ($data, $person, $membership, $groupId, $request): void {
            Person::whereKey($person->id)->lockForUpdate()->firstOrFail();
            $person->update(collect($data)->except('group_id')->all());
            $membership->handle($person, $groupId, $request->user()->id);
            if ($person->status === 'inactive') {
                foreach (Commitment::where('person_id', $person->id)->where('status', 'confirmed')->whereHas('opportunity.activity', fn ($q) => $q->whereIn('status', ['draft', 'published']))->get() as $commitment) {
                    $commitment->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                    Audit::record('commitment.cancelled', $commitment, ['reason' => 'person_inactive']);
                }
                if ($person->account) {
                    $person->account->update(['status' => 'inactive']);
                    DB::table('sessions')->where('user_id', $person->account->id)->delete();
                }
            }
            Audit::record('person.updated', $person, ['status' => $person->status]);
        });

        return new PersonResource($person->fresh());
    }

    public function profile(Request $request, Person $person): PersonResource
    {
        Gate::authorize('profile', $person);
        $data = $request->validate(['talents' => ['present', 'array'], 'talents.*' => ['integer', 'distinct', 'exists:talents,id'], 'learning' => ['present', 'array'], 'learning.*' => ['integer', 'distinct', 'exists:talents,id'], 'interests' => ['present', 'array'], 'interests.*' => ['integer', 'distinct', 'exists:interests,id'], 'availability' => ['present', 'array'], 'availability.*' => ['integer', 'distinct', 'exists:availability_slots,id']]);
        abort_if(count(array_intersect($data['talents'], $data['learning'])) > 0, 422, 'Una habilidad debe estar en talentos o en aprendizaje.');
        DB::transaction(function () use ($person, $data): void {
            Person::whereKey($person->id)->lockForUpdate()->firstOrFail();
            DB::table('person_talents')->where('person_id', $person->id)->delete();
            foreach (['talents' => 'possesses', 'learning' => 'wants_to_learn'] as $key => $kind) {
                foreach ($data[$key] as $talent) {
                    DB::table('person_talents')->insert(['person_id' => $person->id, 'talent_id' => $talent, 'kind' => $kind]);
                }
            }
            $person->interests()->sync($data['interests']);
            $person->availability()->sync($data['availability']);
            Audit::record('profile.updated', $person);
        });

        return new PersonResource($person->fresh());
    }
}
