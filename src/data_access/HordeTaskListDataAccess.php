<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeTaskListDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        // Take all IDs of Calendar Events
        // TODO export smart tasklists?
        // Weirdly, horde seems to return taskList objects here and only Ids in calendar
        return $registry->call('tasks/listTasklists', array(true));
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get for specific IDs via Identity/get not implemented.");
    }

    public function create($calendarsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via Identity/set not implemented.");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via Identity/set not implemented.");
    }

    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Identity/query not implemented.");
    }
}
