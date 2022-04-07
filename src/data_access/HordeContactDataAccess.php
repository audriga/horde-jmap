<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeContactDataAccess extends AbstractDataAccess
{
    private $logger;

    public function __construct()
    {
        $this->logger = \OpenXPort\Util\Logger::getInstance();
    }

    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        if (!$accountId) {
            $accountId = $registry->getAuth();
        }
        $this->logger->info("Getting contacts for user " . $accountId);

        $contactIds = [];
        $result = [];

        // Only look at editable address books
        $addressbooks = $registry->call('contacts/listShares', array(\Horde_Perms::EDIT));
        $this->logger->info("Got " . sizeof($addressbooks) . " address books.");

        $options = ["skip_empty" => true];

        foreach ($addressbooks as $id => $book) {
            // Take all IDs of Contacts
            $this->logger->info("Listing IDs for address book " . $id);
            $this->logger->info(
                "Address book has name \"" . $book->get('name') .
                "\", description \"" . $book->get('desc') .
                "\", params \"" .  $book->get('params') . "\"."
            );

            try {
                $contactIds = $registry->call('contacts/listUids', array($id));
            } catch (\Turba_Exception $e) {
                // Skip books that have a similar ID to username
                // See https://web.audriga.com/mantis/view.php?id=5651
                if (strtolower($id) == strtolower($accountId)) {
                    $this->logger->warning(
                        "Discarding contacts of address book " . $id . " due to Exception with Message: \"" .
                        $e->getMessage() .  "\" in File " . $e->getFile()
                    );
                    continue;
                } else {
                    throw $e;
                }
            }

            $addressbookContacts = [];

            // Iterate through all IDs and export for each ID a vCard contact
            foreach ($contactIds as $contactId) {
                try {
                    $vCardContact = $registry->call(
                        'contacts/export',
                        array($contactId, 'text/vcard', $id, null, $options)
                    );
                    $addressbookContacts[$contactId] = $vCardContact;
                } catch (\Turba_Exception $e) {
                    // Catch and log turba exceptions during export
                    // See https://web.audriga.com/mantis/view.php?id=5671
                    $this->logger->warning(
                        "Unable to export contact with ID " . $contactId . " of address book " . $id .
                        " due to Exception with Message: \"" .  $e->getMessage() .  "\" in File " . $e->getFile()
                    );
                }
            }

            $result[$id] = $addressbookContacts;
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
