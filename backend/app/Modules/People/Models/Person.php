<?php

namespace App\Modules\People\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function talents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'person_talents')->wherePivot('kind', 'possesses');
    }

    public function learning(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'person_talents')->wherePivot('kind', 'wants_to_learn');
    }

    public function interests(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'person_interests');
    }

    public function availability(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AvailabilitySlot::class, 'person_availability');
    }

    public function membership(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Modules\SmallGroups\Models\GroupMembership::class)->whereNull('ends_at');
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Modules\Identity\Models\User::class);
    }
}
