<?php

namespace Sokil\Mongo\Migrator\Event;

class BeforeMigrateRevisionEvent extends ApplyRevisionEvent
{
    const NAME = 'before_migrate_revision';
}
