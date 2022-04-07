<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeCalendarDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        // Take all IDs of Calendar Events
        $calendarIds = $registry->call('calendar/listCalendars', array(true));

        $calendars = [];

        foreach ($calendarIds as $id) {
            array_push($calendars, $registry->call('calendar/getCalendar', array($id)));
        }

        return $calendars;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get for specific IDs via Calendar/get not implemented.");
    }

    public function create($calendarsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via Calendar/set not implemented.");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via Calendar/set not implemented.");
    }

    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Calendar/query not implemented.");
    }
}
