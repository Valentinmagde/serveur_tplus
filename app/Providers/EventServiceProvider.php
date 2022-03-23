<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        'Illuminate\Auth\Events\Login' => ['App\Listeners\Login'],
        'App\Events\Evenement' => ['App\Listeners\Evenement'],
        'App\Events\Finance' => ['App\Listeners\Finance'],
        'App\Events\Nouvelle' => ['App\Listeners\Nouvelle'],
        'App\Events\Message' => ['App\Listeners\Message'],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
