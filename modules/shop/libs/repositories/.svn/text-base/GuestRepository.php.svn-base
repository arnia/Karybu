<?php

/**
 * Handles database operations for Guests table
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class GuestRepository extends BaseRepository
{
    /**
     * create or retrieve guest
     * @return Guest
     */
    public function createOrRetrieve()
    {
        return new Guest(array(
            'guest_srl' => getNextSequence(),
            'address_srl' => null,
            'ip' => '43.46.62.212',
            'session_id' => session_id(),
            'regdate' => time(),
            'last_update' => time()
        ));
    }

}