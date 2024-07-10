<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\UserJoinedRoom;
use App\Events\UserLeftRoom;
use App\Events\MessageSent;
use App\Listeners\NotifyUserJoinedRoom;
use App\Listeners\NotifyUserLeftRoom;
use App\Listeners\NotifyMessageSent;
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserJoinedRoom::class => [
            NotifyUserJoinedRoom::class,
        ],
        UserLeftRoom::class => [
            NotifyUserLeftRoom::class,
        ],
        MessageSent::class => [
            NotifyMessageSent::class,
        ],
		UserBanned::class => [
			HandleUserBanned::class,
		],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
