<?php
namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\People\Http\Resources\PersonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['username' => ['required', 'string', 'max:255'], 'password' => ['required', 'string']]);
        if (!Auth::attempt([...$data, 'status' => 'active'])) { throw ValidationException::withMessages(['username' => ['El usuario o la contraseña no son correctos.']]); }
        if ($request->user()->person->status !== 'active') { Auth::logout(); abort(403, 'Esta cuenta está desactivada.'); }
        $request->session()->regenerate();
        return $this->me($request);
    }
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json(['data' => ['id' => $user->id, 'username' => $user->username, 'person' => new PersonResource($user->person), 'roles' => $user->roles->pluck('code'), 'group_id' => $user->leadership?->group_id, 'must_change_password' => $user->must_change_password]]);
    }
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return response()->json(null, 204);
    }
    public function password(Request $request): JsonResponse
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:12', 'confirmed']]);
        $request->user()->update(['password' => $data['password'], 'must_change_password' => false]);
        $request->session()->regenerate();
        return response()->json(['message' => 'Contraseña actualizada.']);
    }
}
