<?php

namespace OpenXPort\DataAccess;

class HordeNotebookDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        // Only look at editable notebooks
        return $registry->call('notes/listNotepads', array(true, \Horde_Perms::EDIT));
    }

    public function get($ids, $accountId = null)
    {
        // TODO: Implement me
    }

    public function create($notebooksToCreate, $accountId = null)
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
