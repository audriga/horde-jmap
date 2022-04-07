<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeTaskDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        $result = [];

        // Obtain all task list IDs that the user owns
        $lists = $registry->call('tasks/listTasklists', array(true));

        /**
         * For each task list ID export all belonging tasks of the task list with this ID as iCalendar
         * Then, put the task list UID as key in $result and the belonging iCalendar as value in $result
         */
        foreach (array_keys($lists) as $list) {
            $iCalendarTasks = $registry->call('tasks/exportTasklist', array($list, 'text/calendar'));
            $result["$list"] = $iCalendarTasks;
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
