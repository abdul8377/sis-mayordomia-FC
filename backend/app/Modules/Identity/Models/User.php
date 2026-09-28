<?php

namespace App\Modules\Identity\Models;

use App\Modules\People\Models\Person;
use App\Modules\SmallGroups\Models\GroupLeadership;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['person_id', 'username', 'password', 'status', 'must_change_password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'must_change_password' => 'boolean'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function leadership(): HasOne
    {
        return $this->hasOne(GroupLeadership::class)->whereNull('ends_at');
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->roles->pluck('code')->intersect($roles)->isNotEmpty();
    }
}
