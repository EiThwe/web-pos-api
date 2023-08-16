<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Photo;
use App\Models\User;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Photo::class => PhotoPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
        Gate::define('isAdmin', function () {
            return Auth::user()->role == "admin";
        });
    }
}
