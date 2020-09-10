<?php

namespace Sokil\Mongo\Migrator\Event;

use Sokil\Mongo\Migrator\Revision;
use Symfony\Contracts\EventDispatcher\Event;

class ApplyRevisionEvent extends Event implements RevisionEventInterface
{
    /**
     *
     * @var \Sokil\Mongo\Migrator\Revision
     */
    private $revision;

    public function setRevision(Revision $revision)
    {
        $this->revision = $revision;
        return $this;
    }

    /**
     *
     * @return \Sokil\Mongo\Migrator\Revision
     */
    public function getRevision()
    {
        return $this->revision;
    }
}
