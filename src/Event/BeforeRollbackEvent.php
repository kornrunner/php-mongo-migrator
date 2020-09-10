<?php

namespace Sokil\Mongo\Migrator\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeRollbackEvent extends Event
{
    const NAME = 'before_rollback';
}
