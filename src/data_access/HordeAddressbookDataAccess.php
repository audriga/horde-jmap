<?php

namespace OpenXPort\DataAccess;

use OpenXPort\DataAccess\AbstractDataAccess;

class HordeAddressBookDataAccess extends AbstractDataAccess
{
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
        $this->logger->info("Getting address books for user " . $accountId);

        // Only look at editable address books
        $books = $registry->call('contacts/listShares', array(\Horde_Perms::EDIT));
        $this->logger->info("Got " . sizeof($books) . " address books.");

        $result = [];

        foreach ($books as $id => $book) {
            $this->logger->info(
                "Address book has name \"" . $book->get('name') .
                "\", description \"" . $book->get('desc') .
                "\", params \"" .  $book->get('params') . "\"."
            );

            $this->logger->info("Adding address book " . $id);
            $result[$id] = $book;
        }

        return $result;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get for specific IDs via AddressBook/get not implemented.");
    }

    public function create($calendarsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via AddressBook/set not implemented.");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via AddressBook/set not implemented.");
    }

    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("AddressBook/query not implemented.");
    }
}
