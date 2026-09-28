<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\People\Models\Person;
use App\Support\Audit\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return response()->json(['data' => User::with(['person', 'roles'])->get()->map(fn ($u) => ['id' => $u->id, 'username' => $u->username, 'full_name' => $u->person->full_name, 'roles' => $u->roles->pluck('code'), 'status' => $u->status, 'person_id' => $u->person_id])]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return $this->save($request, new User);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return $this->save($request, $user);
    }

    private function save(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['person_id' => ['required', 'integer', 'exists:people,id', Rule::unique('users')->ignore($user->id)], 'username' => ['required', 'alpha_dash', 'max:80', Rule::unique('users')->ignore($user->id)], 'password' => [$user->exists ? 'nullable' : 'required', 'string', 'min:12'], 'roles' => ['required', 'array', 'min:1'], 'roles.*' => ['required', 'distinct', 'in:admin,gp_leader,ja_director,member'], 'status' => ['required', 'in:active,inactive']]);
        abort_unless(Person::findOrFail($data['person_id'])->status === 'active' || $data['status'] === 'inactive', 422, 'La persona está inactiva.');
        abort_if($user->exists && $user->person_id !== (int) $data['person_id'], 422, 'La persona vinculada no se puede reemplazar.');
        DB::transaction(function () use ($user, $data): void {
            User::orderBy('id')->lockForUpdate()->get();
            if ($user->exists && $user->hasRole('admin') && (! in_array('admin', $data['roles'], true) || $data['status'] === 'inactive')) {
                abort_if(User::where('status', 'active')->whereHas('roles', fn ($q) => $q->where('code', 'admin'))->count() <= 1, 422, 'Debe existir al menos un administrador activo.');
            }
            abort_if($user->exists && $user->leadership && (! in_array('gp_leader', $data['roles'], true) || $data['status'] === 'inactive'), 422, 'Reasigna su grupo antes de retirar el rol o desactivar la cuenta.');
            $values = collect($data)->except(['roles', 'password'])->all();
            if (! empty($data['password'])) {
                $values['password'] = $data['password'];
                $values['must_change_password'] = true;
            }
            $user->fill($values)->save();
            $user->roles()->sync(Role::whereIn('code', $data['roles'])->pluck('id'));
            Audit::record('account.saved', $user, ['roles' => $data['roles'], 'status' => $data['status']]);
            if ($data['status'] === 'inactive') {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        });

        return response()->json(['data' => ['id' => $user->id, 'username' => $user->username]]);
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        $data = $request->validate(['password' => ['required', 'string', 'min:12']]);
        DB::transaction(function () use ($data, $user): void {
            $user->update(['password' => $data['password'], 'must_change_password' => true]);
            DB::table('sessions')->where('user_id', $user->id)->delete();
            Audit::record('account.password_reset', $user);
        });

        return response()->json(['message' => 'Contraseña temporal actualizada.']);
    }
}
