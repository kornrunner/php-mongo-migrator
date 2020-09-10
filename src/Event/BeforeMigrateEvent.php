<?php

namespace Sokil\Mongo\Migrator\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeMigrateEvent extends Event
{
    const NAME = 'before_migrate';
}
