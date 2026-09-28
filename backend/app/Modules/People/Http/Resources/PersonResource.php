<?php

namespace App\Modules\People\Http\Resources;

use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $person = $this->resource;
        $contact = $request->user() && ($request->user()->person_id === $person->id || Access::editPerson($request->user(), $person));

        return ['id' => $person->id, 'full_name' => $person->full_name, 'phone' => $contact ? $person->phone : null, 'email' => $contact ? $person->email : null, 'status' => $person->status, 'group' => $person->membership?->group?->only(['id', 'name']), 'talents' => $person->talents->map->only(['id', 'name']), 'interests' => $person->interests->map->only(['id', 'name']), 'learning' => $person->learning->map->only(['id', 'name']), 'availability' => $person->availability->map->only(['id', 'name'])];
    }
}
