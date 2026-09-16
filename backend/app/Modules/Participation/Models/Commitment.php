<?php

namespace App\Modules\Participation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commitment extends Model
{
    use HasFactory;

    protected $table = 'commitments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\People\Models\Person::class);
    }

    public function opportunity(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\YouthMinistry\Models\Opportunity::class);
    }
}
