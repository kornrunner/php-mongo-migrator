<?php

namespace Sokil\Mongo\Migrator\Event;

class BeforeRollbackRevisionEvent extends ApplyRevisionEvent
{
    const NAME = 'before_rollback_revision';
}
