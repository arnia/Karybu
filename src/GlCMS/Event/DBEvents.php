<?php

namespace GlCMS\Event;

final class DBEvents
{
    const QUERY_STARTED = 1;
    const QUERY_ENDED = 2;
    const EXECUTE_QUERY_STARTED = 4;
    const EXECUTE_QUERY_ENDED = 8;
}