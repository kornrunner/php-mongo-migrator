<?php

namespace Sokil\Mongo\Migrator\Event;

class BeforeRollbackRevisionEvent extends RevisionEvent
{
    const NAME = 'before_rollback_revision';
}
