<?php

namespace App\Modules\SmallGroups\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrayerRequest extends Model
{
    use HasFactory;

    protected $table = 'prayer_requests';

    protected $guarded = ['id'];

    protected $hidden = ['encrypted_content'];

    protected function casts(): array
    {
        return ['encrypted_content' => 'encrypted'];
    }
}
