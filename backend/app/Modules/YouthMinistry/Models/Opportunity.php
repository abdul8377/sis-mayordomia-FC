<?php

namespace App\Modules\YouthMinistry\Models;

use App\Modules\Participation\Models\Commitment;
use App\Modules\People\Models\Talent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    use HasFactory;

    protected $table = 'opportunities';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(JaActivity::class, 'activity_id');
    }

    public function talent(): BelongsTo
    {
        return $this->belongsTo(Talent::class);
    }

    public function commitments(): HasMany
    {
        return $this->hasMany(Commitment::class);
    }
}
