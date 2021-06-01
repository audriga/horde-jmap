<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeTaskDataAccess extends AbstractDataAccess {

    public function getAll($accountId = null) {
        // Use registry for access to internal API which provides data access
        global $registry;

        $taskIds = [];
        $tasks = [];

        // Take all IDs of Tasks
        $taskIds = $registry->call('tasks/listUids');

        // Iterate through all IDs and export for each ID an iCal task
        foreach ($taskIds as $id) {
            $iCalTask = $registry->call('tasks/export', array($id, 'text/calendar'));
            array_push($tasks, $iCalTask);
        }

        return $tasks;
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