<?php

namespace Sokil\Mongo\Migrator\Event;

class RollbackRevisionEvent extends RevisionEvent
{
    const NAME = 'rollback_revision';
}
