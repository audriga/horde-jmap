<?php

namespace OpenXPort\Adapter;

class HordeAddressBookAdapter extends AbstractAdapter
{
    private $addressBook;

    public function getAddressBook()
    {
        return $this->addressBook;
    }

    public function setAddressBook($addressBook)
    {
        $this->addressBook = $addressBook;
    }

    public function getId()
    {
        return $this->addressBook->getName();
    }

    public function getName()
    {
        return $this->addressBook->get('name');
    }

    public function getDescription()
    {
        return $this->addressBook->get('desc');
    }

    public function getRole()
    {
        global $registry;

        return $registry->call('contacts/getDefaultShare') == $this->getId() ? "inbox" : null;
    }
}
