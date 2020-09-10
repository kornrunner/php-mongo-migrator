<?php
declare(strict_types=1);

namespace Sokil\Mongo\Migrator\Event\Factory;

use Psr\EventDispatcher\StoppableEventInterface;
use Sokil\Mongo\Migrator\Event\BeforeRollbackRevisionEvent;
use Sokil\Mongo\Migrator\Event\BeforeMigrateRevisionEvent;
use Sokil\Mongo\Migrator\Event\RollbackEvent;
use Sokil\Mongo\Migrator\Event\StopEvent;
use Sokil\Mongo\Migrator\Event\MigrateEvent;
use Sokil\Mongo\Migrator\Event\StartEvent;
use Sokil\Mongo\Migrator\Event\BeforeRollbackEvent;
use Sokil\Mongo\Migrator\Event\BeforeMigrateEvent;
use Sokil\Mongo\Migrator\Event\MigrateRevisionEvent;
use Sokil\Mongo\Migrator\Event\RollbackRevisionEvent;
use Sokil\Mongo\Migrator\Event\RevisionEventInterface;

class EventFactory implements EventFactoryInterface
{
    public function createBeforeRollbackRevisionEvent(): RevisionEventInterface
    {
        return new BeforeRollbackRevisionEvent();
    }

    public function createBeforeMigrateRevisionEvent(): RevisionEventInterface
    {
        return new BeforeMigrateRevisionEvent();
    }

    public function createRollbackEvent(): StoppableEventInterface
    {
        return new RollbackEvent();
    }

    public function createStopEvent(): StoppableEventInterface
    {
        return new StopEvent();
    }

    public function createMigrateEvent(): StoppableEventInterface
    {
        return new MigrateEvent();
    }

    public function createStartEvent(): StoppableEventInterface
    {
        return new StartEvent();
    }

    public function createBeforeRollbackEvent(): StoppableEventInterface
    {
        return new BeforeRollbackEvent();
    }

    public function createBeforeMigrateEvent(): StoppableEventInterface
    {
        return new BeforeMigrateEvent();
    }

    public function createMigrateRevisionEvent(): RevisionEventInterface
    {
        return new MigrateRevisionEvent();
    }

    public function createRollbackRevisionEvent(): RevisionEventInterface
    {
        return new RollbackRevisionEvent();
    }
}
