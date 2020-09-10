<?php

namespace Sokil\Mongo\Migrator\Event;

class RollbackRevisionEvent extends ApplyRevisionEvent
{
    const NAME = 'rollback_revision';
}
