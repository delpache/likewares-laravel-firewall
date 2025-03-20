<?php

namespace Likewares\Firewall\Listeners;

use Likewares\Firewall\Events\AttackDetected;
use Likewares\Firewall\Traits\Helper;
use Illuminate\Auth\Events\Failed as Event;

class CheckLogin
{
    use Helper;

    public function handle(Event $event): void
    {
        $this->request = request();
        $this->middleware = 'login';
        $this->user_id = 0;

        if ($this->skip($event)) {
            return;
        }

        $this->request['password'] = '******';

        $log = $this->log();

        event(new AttackDetected($log));
    }

    public function skip($event): bool
    {
        if ($this->isDisabled()) {
            return true;
        }

        if ($this->isWhitelist()) {
            return true;
        }

        return false;
    }
}
