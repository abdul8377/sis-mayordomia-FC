<?php

namespace App\Modules\Reporting\Queries;

use App\Modules\Identity\Models\User;
use App\Modules\People\Http\Resources\PersonResource;
use App\Modules\People\Models\Person;
use App\Modules\SmallGroups\Http\Controllers\GroupController;
use App\Modules\SmallGroups\Models\AbsenceAlert;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Modules\SmallGroups\Models\WeeklyCycle;
use App\Modules\YouthMinistry\Http\Controllers\ActivityController;
use App\Modules\YouthMinistry\Http\Resources\ActivityResource;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Support\Access;
use Illuminate\Support\Facades\DB;

class DashboardQuery
{
    public function handle(User $user, ?string $from = null, ?string $to = null, ?int $filterGroup = null): array
    {
        $scope = Access::reportGroup($user);
        if ($filterGroup) {
            abort_unless(Access::manageJa($user) || $filterGroup === $scope, 403);
            $scope = $filterGroup;
        }
        $cycles = WeeklyCycle::where('saturday_date', '<=', now()->setTimezone(config('community.timezone'))->toDateString());
        if ($from) {
            $cycles->whereDate('saturday_date', '>=', $from);
        }if ($to) {
            $cycles->whereDate('saturday_date', '<=', $to);
        }
        $cycles = $cycles->latest('saturday_date')->limit($from ? 104 : 6)->get()->reverse()->values();
        $trend = [];
        $numerator = 0;
        $denominator = 0;
        $activeNumerator = 0;
        $gpUnique = collect();
        $jaUnique = collect();
        $activeUnique = collect();
        $latest = ['gp' => 0, 'ja' => 0, 'both' => 0, 'active' => 0];
        foreach ($cycles as $cycle) {
            $gp = DB::table('gp_attendances as a')->join('gp_meetings as m', 'm.id', '=', 'a.meeting_id')->where('m.cycle_id', $cycle->id)->where('m.status', 'closed')->where('a.status', 'present')->when($scope !== null, fn ($q) => $q->where('m.group_id', $scope))->distinct()->pluck('a.person_id');
            $activity = JaActivity::where('cycle_id', $cycle->id)->where('is_primary', true)->where('status', 'completed')->first();
            $ja = collect();
            $active = collect();
            if ($activity) {
                $ja = DB::table('ja_attendances as a')->leftJoin('group_memberships as g', 'g.id', '=', 'a.membership_id')->where('a.activity_id', $activity->id)->where('a.status', 'present')->when($scope !== null, fn ($q) => $q->where('g.group_id', $scope)->orWhere(function ($x) use ($activity, $gp) {
                    $x->where('a.activity_id', $activity->id)->where('a.status', 'present')->whereIn('a.person_id', $gp);
                }))->distinct()->pluck('a.person_id');
                $active = DB::table('participations as p')->join('ja_attendances as a', 'a.id', '=', 'p.ja_attendance_id')->where('a.activity_id', $activity->id)->where('a.status', 'present')->whereNull('p.revoked_at')->when($scope !== null, fn ($q) => $q->whereIn('a.person_id', $ja))->distinct()->pluck('a.person_id');
                $numerator += $gp->intersect($ja)->count();
                $denominator += $gp->count();
                $activeNumerator += $gp->intersect($active)->count();
                $latest = ['gp' => $gp->count(), 'ja' => $ja->count(), 'both' => $gp->intersect($ja)->count(), 'active' => $active->count()];
            }
            $gpUnique = $gpUnique->merge($gp);
            $jaUnique = $jaUnique->merge($ja);
            $activeUnique = $activeUnique->merge($active);
            $trend[] = ['label' => $cycle->saturday_date->translatedFormat('d M'), 'gp' => $gp->count(), 'ja' => $activity ? $ja->count() : null];
        }
        $groups = SmallGroup::with('leadership.user.person')->withCount('memberships')->where('status', 'active')->when($scope !== null, fn ($q) => $q->whereKey($scope))->get();
        $alerts = collect();
        if ($user->hasRole('admin', 'gp_leader')) {
            $alerts = AbsenceAlert::with(['membership.person', 'membership.group'])->where('status', 'open')->whereHas('membership', fn ($q) => $q->whereNull('ends_at')->when(! $user->hasRole('admin'), fn ($q) => $q->where('group_id', $user->leadership?->group_id ?? -1)))->limit(5)->get()->map(fn ($a) => ['id' => $a->id, 'person' => new PersonResource($a->membership->person), 'group_name' => $a->membership->group->name, 'consecutive_count' => $a->consecutive_count, 'status' => $a->status, 'last_contact_at' => null]);
        }
        $members = Person::where('status', 'active')->when($scope !== null, fn ($q) => $q->whereHas('membership', fn ($q) => $q->where('group_id', $scope)))->count();

        return ['scope_name' => $scope !== null ? ($groups->first()?->name ?? 'Mi comunidad') : 'Nuestra comunidad', 'stats' => ['members' => $members, 'gp_attendance' => $latest['gp'], 'connection_rate' => $denominator ? round($numerator / $denominator * 100, 2) : null, 'active_roles' => $latest['active']], 'trend' => $trend, 'next_activity' => ($next = JaActivity::with(ActivityController::RELATIONS)->where('status', 'published')->where('is_primary', true)->where('ends_at', '>=', now())->orderBy('starts_at')->first()) ? new ActivityResource($next) : null, 'groups' => $groups->map(fn ($g) => GroupController::present($g)), 'alerts' => $alerts, 'recent' => [], 'attendance_comparison' => $latest, 'totals' => ['gp_unique' => $gpUnique->unique()->count(), 'ja_unique' => $jaUnique->unique()->count(), 'active_unique' => $activeUnique->unique()->count(), 'connection_numerator' => $numerator, 'connection_denominator' => $denominator, 'active_connection_rate' => $denominator ? round($activeNumerator / $denominator * 100,2) : null]];
    }
}
