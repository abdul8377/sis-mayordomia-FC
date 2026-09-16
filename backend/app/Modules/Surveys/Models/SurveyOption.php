<?php

namespace App\Modules\Surveys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyOption extends Model
{
    use HasFactory;

    protected $table = 'survey_options';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
