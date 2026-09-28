<?php

namespace App\Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reporting\Queries\DashboardQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardQuery $query): JsonResponse
    {
        return response()->json($query->handle($request->user()));
    }

    public function reports(Request $request, DashboardQuery $query): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin', 'ja_director', 'gp_leader'), 403);
        $data = $request->validate(['from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'], 'group_id' => ['nullable', 'integer', 'exists:small_groups,id']]);

        return response()->json($query->handle($request->user(), $data['from'] ?? null, $data['to'] ?? null, $data['group_id'] ?? null));
    }
}
