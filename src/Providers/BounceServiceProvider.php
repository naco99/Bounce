<?php

namespace NacAL\Bounce\Providers;

use Illuminate\Support\ServiceProvider;
use NacAL\Bounce\Commands\ClearAll;

class BounceServiceProvider extends ServiceProvider
{
    protected $commands = [
        ClearAll::class
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
