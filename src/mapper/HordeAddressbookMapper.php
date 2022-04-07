<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\Contact\AddressBook;

class HordeAddressBookMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    /* Horde UI has the properties
     * Name: name
     * Description: description
     * TODO Sharing
     */
    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $addressBook) {
            $adapter->setAddressBook($addressBook);

            $jmapBook = new AddressBook();
            $jmapBook->setId($adapter->getId());
            $jmapBook->setRole($adapter->getRole());
            $jmapBook->setName($adapter->getName());
            $jmapBook->setDescription($adapter->getDescription());

            array_push($list, $jmapBook);
        }

        return $list;
    }
}
