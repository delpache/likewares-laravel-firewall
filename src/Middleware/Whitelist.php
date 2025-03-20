<?php

namespace Likewares\Firewall\Middleware;

use Likewares\Firewall\Abstracts\Middleware;

class Whitelist extends Middleware
{
    public function check($patterns)
    {
        return ($this->isWhitelist() === false);
    }
}
