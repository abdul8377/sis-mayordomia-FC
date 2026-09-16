<?php

namespace App\Modules\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceNeed extends Model
{
    use HasFactory;

    protected $table = 'service_needs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['reported_at' => 'datetime'];
    }

    public function responsible(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\People\Models\Person::class, 'responsible_person_id');
    }
}
