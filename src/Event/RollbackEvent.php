<?php

namespace Sokil\Mongo\Migrator\Event;

use Symfony\Contracts\EventDispatcher\Event;

class RollbackEvent extends Event
{
    const NAME = 'rollback';
}
