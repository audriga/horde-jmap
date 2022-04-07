<?php

namespace OpenXPort\DataAccess;

use OpenXPort\Util\HordeSieveScriptAccessUtil;

class HordeSieveScriptDataAccess extends AbstractDataAccess
{
    public function getAll($accountId = null)
    {
        // Obtain the Sieve script from the Managesieve backend
        $script = HordeSieveScriptAccessUtil::getSieveScript();

        if (isset($script) && !is_null($script) && !empty($script)) {
            return $script;
        }

        return null;
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
