<?php

namespace Likewares\Firewall\Listeners;

use Likewares\Firewall\Events\AttackDetected as Event;
use Likewares\Firewall\Notifications\AttackDetected;
use Likewares\Firewall\Notifications\Notifiable;
use Throwable;

class NotifyUsers
{
    /**
     * Handle the event.
     *
     * @param Event $event
     *
     * @return void
     */
    public function handle(Event $event)
    {
        try {
            (new Notifiable)->notify(new AttackDetected($event->log));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
