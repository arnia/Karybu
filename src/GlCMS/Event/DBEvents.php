<?php

namespace GlCMS\Event;

final class DBEvents
{
    const QUERY_STARTED = 'cms.db.query.start';
    const QUERY_ENDED = 'cms.db.query.end';
    const EXECUTE_QUERY_STARTED = 'cms.db.executequery.start';
    const EXECUTE_QUERY_ENDED = 'cms.db.executequery.end';
}