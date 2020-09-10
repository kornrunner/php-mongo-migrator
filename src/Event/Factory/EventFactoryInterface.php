<?php
declare(strict_types=1);

namespace Sokil\Mongo\Migrator\Event\Factory;

use Psr\EventDispatcher\StoppableEventInterface;
use Sokil\Mongo\Migrator\Event\RevisionEventInterface;

interface EventFactoryInterface
{
    public function createStartEvent() : StoppableEventInterface;
    public function createBeforeMigrateEvent() : StoppableEventInterface;
    public function createBeforeMigrateRevisionEvent() : RevisionEventInterface;
    public function createMigrateRevisionEvent() : RevisionEventInterface;
    public function createMigrateEvent() : StoppableEventInterface;
    public function createBeforeRollbackEvent() : StoppableEventInterface;
    public function createBeforeRollbackRevisionEvent() : RevisionEventInterface;
    public function createRollbackRevisionEvent() : RevisionEventInterface;
    public function createRollbackEvent() : StoppableEventInterface;
    public function createStopEvent() : StoppableEventInterface;
}