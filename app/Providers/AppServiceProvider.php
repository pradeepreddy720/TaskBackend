<?php

namespace App\Providers;

use Laravel\Passport\Passport; 
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    protected $policies = [ 
        'App\Model' => 'App\Policies\ModelPolicy', 
    ];

    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies(); 
        Passport::routes(); 
        Schema::defaultStringLength(191);
    }
}
