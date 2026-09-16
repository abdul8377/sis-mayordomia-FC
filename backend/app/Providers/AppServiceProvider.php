<?php
namespace App\Providers;
use App\Modules\People\Models\Person;
use App\Modules\People\Policies\PersonPolicy;
use App\Modules\SmallGroups\Models\SmallGroup;
use App\Modules\SmallGroups\Models\PrayerRequest;
use App\Modules\SmallGroups\Policies\GroupPolicy;
use App\Modules\SmallGroups\Policies\PrayerRequestPolicy;
use App\Modules\YouthMinistry\Models\JaActivity;
use App\Modules\YouthMinistry\Policies\ActivityPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        Gate::policy(Person::class, PersonPolicy::class); Gate::policy(SmallGroup::class, GroupPolicy::class); Gate::policy(PrayerRequest::class, PrayerRequestPolicy::class); Gate::policy(JaActivity::class, ActivityPolicy::class);
        RateLimiter::for('login', fn (Request $request) => [Limit::perMinute(5)->by(strtolower((string) $request->input('username')).'|'.$request->ip()), Limit::perMinute(20)->by($request->ip())]);
    }
}
