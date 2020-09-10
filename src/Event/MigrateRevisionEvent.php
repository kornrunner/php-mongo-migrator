<?php

namespace Sokil\Mongo\Migrator\Event;

class MigrateRevisionEvent extends ApplyRevisionEvent
{
    const NAME = 'migrate_revision';
}
