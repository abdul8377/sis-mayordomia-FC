<?php

namespace App\Modules\Service\Models;

use App\Modules\People\Models\Person;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceNeed extends Model
{
    use HasFactory;

    protected $table = 'service_needs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['reported_at' => 'datetime'];
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'responsible_person_id');
    }
}
