<?php

namespace OpenXPort\Adapter;

use OpenXPort\Util\AdapterUtil;

class HordeNotebookAdapter extends AbstractAdapter
{
    private $notebook;

    public function getNotebook()
    {
        return $this->notebook;
    }

    public function setNotebook($notebook)
    {
        $this->notebook = $notebook;
    }

    public function getId()
    {
        $id = $this->notebook->data['share_name'];
        return (AdapterUtil::isSetAndNotNull($id) && !empty($id)) ? $id : null;
    }

    public function getName()
    {
        $name = $this->notebook->data['attribute_name'];
        return (AdapterUtil::isSetAndNotNull($name) && !empty($name)) ? $name : null;
    }

    public function getDescription()
    {
        $description = $this->notebook->data['attribute_desc'];
        return (AdapterUtil::isSetAndNotNull($description) && !empty($description)) ? $description : null;
    }

    public function getShareWith()
    {
        $jmapShareWith = [];

        // Notebook sharing data in Horde is found the 'perm' property.
        // If the perm property's 'users' property (a key-value map) is set => we read
        // the keys from it as the usernames of the users that the notebook
        // is shared with and we read the values as the permissions set for a given user
        $notebookShares = $this->notebook->data['perm'];
        if (
            AdapterUtil::isSetAndNotNull($notebookShares)
            && !empty($notebookShares)
            && AdapterUtil::isSetAndNotNull($notebookShares['users']
            && !empty($notebookShares['users']))
        ) {
            // Permissions are powers of two and are denoted as follows in Horde:
            // 2 - show permission (user is able to see that the shared object exists)
            // 4 - read permission (user is able to read the contents of the shared object)
            // 8 - write permission (user is able to modify the shared object)
            // 16 - delete permission (user is able to delete the shared object)
            // Mixed permissions are represented as sums (e.g., 24 means 8 + 16,
            // i.e. write and delete permission)
            foreach ($notebookShares['users'] as $username => $permissions) {
                $canRead = false;
                $canEdit = false;
                $canDelete = false;
                // Because the show permission has no equivalent in JMAP, we take it into
                // consideration alongside with each other permission (that's why 4 and 6 (4 + 2)
                // are both valid read permissions)
                switch ($permissions) {
                    case 4:
                    case 6:
                        $canRead = true;
                        break;

                    case 8:
                    case 10:
                        $canEdit = true;
                        break;

                    case 16:
                    case 18:
                        $canDelete = true;
                        break;

                    case 12:
                    case 14:
                        $canRead = true;
                        $canEdit = true;
                        break;

                    case 20:
                    case 22:
                        $canRead = true;
                        $canDelete = true;
                        break;

                    case 24:
                    case 26:
                        $canEdit = true;
                        $canDelete = true;
                        break;

                    case 28:
                    case 30:
                        $canRead = true;
                        $canEdit = true;
                        $canDelete = true;
                        break;

                    default:
                        break;
                }

                $jmapUserPermissions = [
                    "mayReadFreeBusy" => false,
                    "mayReadItems" => $canRead,
                    "mayAddItems" => $canEdit,
                    "mayUpdatePrivate" => false,
                    "mayRSVP" => false,
                    "mayUpdateOwn" => $canEdit,
                    "mayUpdateAll" => false,
                    "mayRemoveOwn" => $canDelete,
                    "mayRemoveAll" => false,
                    "mayAdmin" => false,
                    "mayDelete" => $canDelete
                ];

                $jmapShareWith[$username] = $jmapUserPermissions;
            }
        }

        if (count($jmapShareWith) === 0) {
            return null;
        }

        return $jmapShareWith;
    }
}
