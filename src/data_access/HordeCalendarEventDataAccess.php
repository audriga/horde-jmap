<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeCalendarEventDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        $result = [];

        // Read all calendar folders, belonging to the user, since we'll need the calendar folder IDs for setting
        // within the calendar events later
        $calendarIds = $registry->call('calendar/listCalendars', array(true));

        foreach ($calendarIds as $calendarId) {
        // Export all calendar events that belong to the currently iterated on calendar folder in a single
        // iCalendar string
            $iCalendar = $registry->call('calendar/exportCalendar', array($calendarId, 'text/calendar'));

            // Save each exported iCalendar as a value to a key which is its corresponding calendar folder's ID
            $result["$calendarId"] = $iCalendar;
        }

        return $result;
    }

    public function get($ids, $accountId = null)
    {
        // TODO: Implement me
    }

    public function create($contactsToCreate, $accountId = null)
    {
        // TODO: Implement me
    }

    public function destroy($ids, $accountId = null)
    {
        // TODO: Implement me
    }

    public function query($accountId, $filter = null)
    {
        // TODO: Implement me
    }
}
