<?php

namespace OpenXPort\DataAccess;

class HordeIdentityDataAccess extends AbstractDataAccess
{
    private $account;

    public function getAll($accountId = null)
    {
        // Use injector for access to IMP Identity instance
        global $injector;

        // Get the Identity container
        $identity = $injector->getInstance('IMP_Identity');


        $list = [];

        // There does not seem to be a usable list of signature objects, so we build our own
        // We try to use `getValue()` where possible.
        // NOTE: I am not 100% confident that `getSelectList` always provides correct ids. It looks like a plain array.
        foreach ($identity->getSelectList() as $id => $someString) {
            $currentIdentity = [];
            $currentIdentity["id"] = $id;
            $currentIdentity["name"] = $identity->getFullName($id);
            $currentIdentity["email"] = $identity->getValue('from_addr', $id);
            $currentIdentity["replyTo"] = $identity->getValue('replyto_addr', $id);
            $currentIdentity["bcc"] = $identity->getValue('bcc_addr', $id);
            $currentIdentity["textSignature"] = $identity->getValue('signature', $id);
            $currentIdentity["htmlSignature"] = $identity->getSignature('html', $id);

            // We need to filter identities and check if they actually contain any values
            // (see https://web.audriga.com/mantis/view.php?id=5596)
            // However, we need to be careful when filtering, since the id property can have value of zero
            // which is usually filtered out with a normal call to PHP's array_filter() function.
            // Since an id with the value of zero can be valid, we need to remove it and check the rest of
            // the identity's values without id when filtering. If filtering of the rest of the properties
            // gives us an empty array as an identity, then we know that this identity should not be taken
            // into consideration for the actual JMAP response containing identities from Horde. Thus, in
            // this case we just skip the current iteration of the foreach loop.
            $currentIdentityCopy = $currentIdentity;
            unset($currentIdentityCopy['id']);
            $currentIdentityCopy = array_filter(
                $currentIdentityCopy,
                function ($value) {
                    return !is_null($value) && $value !== "" && !empty($value);
                }
            );

            if (count($currentIdentityCopy) == 0) {
                continue;
            }

            array_push($list, $currentIdentity);
        }

        return $list;
    }

    public function get($ids, $accountId = null)
    {
        throw new BadMethodCallException("Get for specific IDs via Identity/get not implemented.");
    }

    public function create($contactsToCreate, $accountId = null)
    {
        throw new BadMethodCallException("Create via Identity/set not implemented.");
    }

    // Destroys specific entities
    public function destroy($ids, $accountId = null)
    {
        throw new BadMethodCallException("Destroy via Identity/set not implemented.");
    }

    // Collects multiple ids
    // TODO support multiple FilterConditions like in JMAP standard
    public function query($accountId, $filter = null)
    {
        throw new BadMethodCallException("Identity/query not implemented.");
    }
}
