<?php

namespace OpenXPort\DataAccess;

class HordeContactGroupDataAccess extends AbstractDataAccess
{
    private $logger;

    public function __construct()
    {
        $this->logger = \OpenXPort\Util\Logger::getInstance();
    }

    /* Add members of other Address Books to Group */
    private function collectAlienMembers($members)
    {
        global $registry;

        $contactIds = [];

        foreach ($members as $member) {
            list($source, $id) = explode(':', $member);
            if ($id) {
                try {
                    $tmpContact = $registry->call('contacts/getContact', array($source, $id));
                    # TODO recurse into other groups
                    if ($tmpContact["__type"] != "Group") {
                        $this->logger->debug(
                            "Got contact with ID " . $id . " from source " . $source .
                            " it looks like: " .  print_r($tmpContact, true)
                        );
                        if (isset($tmpContact["__uid"]) && !empty($tmpContact["__uid"])) {
                            array_push($contactIds, $tmpContact["__uid"]);
                        }
                    }
                } catch (\Horde_Exception_NotFound $e) {
                    // Catch and log Horde NotFound exception during export
                    // See https://web.audriga.com/mantis/view.php?id=5672
                    $this->logger->warning(
                        "Unable to get contact with ID " . $id . " from source " . $source .
                        " due to Exception with Message: \"" .  $e->getMessage() .  "\" in File " . $e->getFile()
                    );
                }
            }
        }

        return $contactIds;
    }

    public function getAll($accountId = null)
    {
        // Use registry for access to internal API which provides data access
        global $registry;

        $result = [];

        if (!$accountId) {
            $accountId = $registry->getAuth();
        }

        $this->logger->info("Getting contact groups for user " . $accountId);

        // get All group objects, editable shares as well as the default Address Book
        $groupIdMap = $registry->call('contacts/listUserGroupObjects');

        $this->logger->info("Got " . sizeof(array_keys($groupIdMap)) . " groups.");

        $books = $registry->call('contacts/listShares', array(\Horde_Perms::EDIT));
        $defaultBookId = $registry->call('contacts/getDefaultShare');

        $newGroupIdMap = [];

        // Contact Groups are weird since they can contain contacts of other addressbook. Their ID then container a ":".
        // Also, there seems to be no way to filter by Address Book, which would make life much easier
        // For all non-empty contact Groups,
        // * Search in each AddressBook
        // * If contacts are in this addressbook, get their UIDs and UIDs of contacts in other addressbooks
        // * then break
        // * If contactIds is still empty all entries must come from another source.
        foreach ($groupIdMap as $id => $name) {
            $group = $registry->call('contacts/getGroupObject', array($id));

            $bookId = '';
            $group["contactIds"] = [];

            $members = unserialize($group["members"]);

            // Make sure deserialization has worked, see https://web.audriga.com/mantis/view.php?id=5764
            if (!$members) {
                $this->logger->notice("Skipping group \"" . $group["name"] . "\", because deserializing its
                member property failed.");
                continue;
            }

            foreach ($books as $book) {
                $bookId = $book->getName();
                $currentContacts = $registry->call('contacts/getContacts', array($bookId, $members));
                if (!empty($currentContacts)) {
                    // currentContacts is only not empty for Contacts with normal IDs (without :) from the same folder.
                    // So this should be the folder they are in.
                    foreach ($currentContacts as $contact) {
                        array_push($group["contactIds"], $contact["__uid"]);
                    }

                    $this->logger->debug(
                        "Collecting for ContactGroup " . $id . " the following members: " . implode(", ", $members)
                    );

                    $group["contactIds"] = array_merge($group["contactIds"], $this->collectAlienMembers($members));
                    $result[$bookId][$id] = $group;

                    break;
                }
            }
            if (empty($group["contactIds"])) {
                // Group must only contain contacts from alien Address Books now
                $group["contactIds"] = array_merge($group["contactIds"], $this->collectAlienMembers($members));
                $result[$defaultBookId][$id] = $group;
            }
        }

        return $result;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get for specific IDs via ContactGroup/get not implemented.");
    }

    public function create($calendarsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via ContactGroup/set not implemented.");
    }

    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via ContactGroup/set not implemented.");
    }

    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("ContactGroup/query not implemented.");
    }
}
