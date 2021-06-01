<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeContactDataAccess extends AbstractDataAccess {

    public function getAll($accountId = null) {
        // Use registry for access to internal API which provides data access
        global $registry;

        $contactIds = [];
        $contacts = [];

        // Take all IDs of Contacts
        $contactIds = $registry->call('contacts/listUids');

        // Iterate through all IDs and export for each ID a vCard contact
        foreach ($contactIds as $id) {
            $vCardContact = $registry->call('contacts/export', array($id, 'text/vcard'));
            array_push($contacts, $vCardContact);
        }

        return $contacts;
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