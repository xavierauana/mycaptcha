<?php

namespace App\Providers;

use App\Events\NewCaptcha;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        NewCaptcha::class => [
            'App\Listeners\EventListener',
        ],
    ];
}
