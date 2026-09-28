<?php

namespace App\Modules\Participation\Models;

use App\Modules\People\Models\Person;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JaAttendance extends Model
{
    use HasFactory;

    protected $table = 'ja_attendances';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }
}
