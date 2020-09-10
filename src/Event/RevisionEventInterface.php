<?php

namespace Sokil\Mongo\Migrator\Event;

use Sokil\Mongo\Migrator\Revision;

interface RevisionEventInterface
{
    public function setRevision(Revision $revision);
    public function getRevision();
}