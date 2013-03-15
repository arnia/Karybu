<?php

namespace GlCMS\Event;

use Symfony\Component\EventDispatcher\Event;

class QueryEvent extends Event {
    // sql
    // connection_type

    /** @var string */
    private $query;

    public function setQuery($query) {
        $this->query = $query;
    }

    public function getQuery() {
        return $this->query;
    }
}