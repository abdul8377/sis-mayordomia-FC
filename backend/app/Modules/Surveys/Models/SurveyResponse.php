<?php

namespace App\Modules\Surveys\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $table = 'survey_responses';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }
}
