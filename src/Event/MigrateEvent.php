<?php

namespace Sokil\Mongo\Migrator\Event;

use Symfony\Contracts\EventDispatcher\Event;

class MigrateEvent extends Event
{
    const NAME = 'migrate';
}
