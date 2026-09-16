<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'active.account'])->group(function (): void {
    foreach (['identity', 'people', 'groups', 'meetings', 'activities', 'participation', 'surveys', 'service', 'reports', 'settings'] as $module) {
        require __DIR__."/api/v1/{$module}.php";
    }
});
