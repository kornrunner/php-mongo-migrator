<?php

namespace Sokil\Mongo\Migrator\Event;

class BeforeMigrateRevisionEvent extends RevisionEvent
{
    const NAME = 'before_migrate_revision';
}
