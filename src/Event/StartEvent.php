<?php

namespace Sokil\Mongo\Migrator\Event;

use Symfony\Contracts\EventDispatcher\Event;

class StartEvent extends Event
{
    const NAME = 'start';
}
