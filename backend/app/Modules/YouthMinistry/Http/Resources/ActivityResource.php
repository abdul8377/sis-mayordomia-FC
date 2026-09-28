<?php

namespace App\Modules\YouthMinistry\Http\Resources;

use App\Modules\People\Http\Resources\PersonResource;
use App\Modules\SmallGroups\Http\Controllers\GroupController;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $a = $this->resource;

        return [...$a->only(['id', 'title', 'description', 'place', 'starts_at', 'ends_at', 'status', 'is_primary']),
            'activity_type' => $a->activityType->only(['id', 'name']), 'group' => $a->assignment?->group ? GroupController::present($a->assignment->group) : null,
            'opportunities' => $a->opportunities->map(fn ($o) => [...$o->only(['id', 'title', 'description', 'capacity', 'closed_at', 'starts_at', 'ends_at']), 'talent' => $o->talent?->only(['id', 'name']), 'confirmed_count' => $o->commitments->where('status', 'confirmed')->count(), 'commitments' => $o->commitments->where('status', 'confirmed')->values()->map(fn ($c) => ['id' => $c->id, 'status' => $c->status, 'person' => new PersonResource($c->person)])]),
            'result' => $a->result?->only(['summary', 'beneficiary_count', 'final_note']),
            'attendees' => Access::manageJa($request->user()) ? $a->attendees->map->only(['person_id', 'status']) : [],
            'participations' => Access::manageJa($request->user()) ? $a->attendees->flatMap(fn ($row) => $row->participations->whereNull('revoked_at')->map(fn ($p) => ['person_id' => $row->person_id, 'opportunity_id' => $p->opportunity_id])) : [],
        ];
    }
}
