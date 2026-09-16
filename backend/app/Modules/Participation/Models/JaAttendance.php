<?php

namespace App\Modules\Participation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JaAttendance extends Model
{
    use HasFactory;

    protected $table = 'ja_attendances';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\People\Models\Person::class);
    }

    public function participations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Participation::class);
    }
}
