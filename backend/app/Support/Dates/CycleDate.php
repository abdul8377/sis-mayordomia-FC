<?php

namespace App\Support\Dates;

use App\Modules\SmallGroups\Models\WeeklyCycle;
use Carbon\CarbonImmutable;

final class CycleDate
{
    public static function forDate(string $value): WeeklyCycle
    {
        $date = CarbonImmutable::parse($value)->setTimezone(config('community.timezone'));
        $saturday = $date->isSaturday() ? $date : $date->next('Saturday');

        return WeeklyCycle::firstOrCreate(['saturday_date' => $saturday->toDateString()]);
    }
}
