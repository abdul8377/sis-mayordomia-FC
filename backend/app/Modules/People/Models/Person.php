<?php

namespace App\Modules\People\Models;

use App\Modules\Identity\Models\User;
use App\Modules\SmallGroups\Models\GroupMembership;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'person_talents')->wherePivot('kind', 'possesses');
    }

    public function learning(): BelongsToMany
    {
        return $this->belongsToMany(Talent::class, 'person_talents')->wherePivot('kind', 'wants_to_learn');
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'person_interests');
    }

    public function availability(): BelongsToMany
    {
        return $this->belongsToMany(AvailabilitySlot::class, 'person_availability');
    }

    public function membership(): HasOne
    {
        return $this->hasOne(GroupMembership::class)->whereNull('ends_at');
    }

    public function account(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
