<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\Contact\ContactGroup;

class HordeContactGroupMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $addressBookId => $groupIdMap) {
            foreach ($groupIdMap as $id => $group) {
                $adapter->setContactGroup($group);

                $jmapContactGroup = new ContactGroup();

                $jmapContactGroup->setId($id);
                $jmapContactGroup->setAddressBookId($addressBookId);
                $jmapContactGroup->setName($adapter->getName());
                $jmapContactGroup->setContactIds($adapter->getContactIds());

                array_push($list, $jmapContactGroup);
            }
        }

        return $list;
    }
}
