<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeCalendarEventDataAccess extends AbstractDataAccess {

    public function getAll($accountId = null) {
        // Use registry for access to internal API which provides data access
        global $registry;

        $eventIds = [];
        $events = [];

        // Take all IDs of Calendar Events
        $eventIds = $registry->call('calendar/listUids');

        // Iterate through all IDs and export for each ID an iCal calendar event
        foreach ($eventIds as $id) {
            $iCalEvent = $registry->call('calendar/export', array($id, 'text/calendar'));
            array_push($events, $iCalEvent);
        }

        return $events;
    }

    public function get($ids, $accountId = null) {
        // TODO: Implement me
    }

    public function create($contactsToCreate, $accountId = null) {
        // TODO: Implement me
    }

    public function destroy($ids, $accountId = null) {
        // TODO: Implement me
    }
    
    public function query($accountId, $filter = null) {
        // TODO: Implement me
    }

}