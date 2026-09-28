<?php

namespace App\Modules\Configuration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Configuration\Models\SystemSetting;
use App\Modules\People\Models\AvailabilitySlot;
use App\Modules\People\Models\Interest;
use App\Modules\People\Models\Talent;
use App\Modules\YouthMinistry\Models\ActivityType;
use App\Modules\YouthMinistry\Models\Responsibility;
use App\Support\Audit\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private const CATALOGS = ['talents' => Talent::class, 'interests' => Interest::class, 'activity_types' => ActivityType::class, 'responsibilities' => Responsibility::class, 'availability_slots' => AvailabilitySlot::class];

    public function catalogs(Request $request): JsonResponse
    {
        $items = [];
        foreach (self::CATALOGS as $name => $model) {
            $q = $model::query();
            if (! $request->user()->hasRole('admin') || ! $request->boolean('include_inactive')) {
                $q->where('is_active', true);
            }$items[$name] = $q->orderBy('name')->get();
        }

return response()->json($items);
    }

    public function catalogStore(Request $request, string $catalog): JsonResponse
    {
        return $this->saveCatalog($request, $catalog);
    }

    public function catalogUpdate(Request $request, string $catalog, int $id): JsonResponse
    {
        return $this->saveCatalog($request, $catalog, $id);
    }

    private function saveCatalog(Request $request, string $catalog, ?int $id = null): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless(isset(self::CATALOGS[$catalog]), 404);
        $class = self::CATALOGS[$catalog];
        $model = $id ? $class::findOrFail($id) : new $class;
        $rules = ['name' => ['required', 'string', 'max:100', Rule::unique($catalog)->ignore($id)], 'is_active' => ['sometimes', 'boolean']];
        if ($catalog === 'availability_slots') {
            $rules += ['weekday' => ['required', 'integer', 'between:0,6'], 'starts_at' => ['required', 'date_format:H:i'], 'ends_at' => ['required', 'date_format:H:i', 'after:starts_at']];
        }
        $data = $request->validate($rules);
        $model->fill($data)->save();
        Audit::record('catalog.saved', $model, ['catalog' => $catalog, 'is_active' => $model->is_active]);

        return response()->json(['data' => $model]);
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return response()->json(['data' => ['absence_threshold' => (int) (SystemSetting::where('key', 'absence_threshold')->value('value') ?? 3), 'church_name' => SystemSetting::where('key', 'church_name')->first()?->value ?? 'Iglesia local']]);
    }

    public function update(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        $data = $request->validate(['absence_threshold' => ['required', 'integer', 'between:1,12'], 'church_name' => ['required', 'string', 'max:120']]);
        foreach ($data as $key => $value) {
            $s = SystemSetting::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $request->user()->id]);
            Audit::record('settings.updated', $s, ['key' => $key]);
        }

return $this->index($request);
    }
}
