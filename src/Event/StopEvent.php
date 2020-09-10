<?php

namespace Sokil\Mongo\Migrator\Event;

use Symfony\Contracts\EventDispatcher\Event;

class StopEvent extends Event
{
    const NAME = 'stop';
}
