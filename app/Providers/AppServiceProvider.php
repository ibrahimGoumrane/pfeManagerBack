<?php

namespace App\Providers;

use App\Models\Report;
use App\Models\Sector;
use App\Models\Tag;
use App\Models\User;
use App\Policies\ReportPolicy;
use App\Policies\SectorPolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Gate::policy(User::class, UserPolicy::class);
       Gate::policy(Tag::class, TagPolicy::class);
       Gate::policy(Report::class, ReportPolicy::class);
       Gate::policy(Sector::class, SectorPolicy::class);
    }
}
