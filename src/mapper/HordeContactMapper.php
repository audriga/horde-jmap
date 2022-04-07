<?php

namespace OpenXPort\Mapper;

use JeroenDesloovere\VCard\VCardParser;
use OpenXPort\Jmap\Contact\Contact;

class HordeContactMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        require_once __DIR__ . '/../../vcard/src/VCardParser.php';

        $list = [];

        foreach ($data as $addressBookId => $vCardEntries) {
            foreach ($vCardEntries as $id => $c) {
                $parser = new VCardParser($c);
                $vCard = $parser->getCardAtIndex(0);

                $adapter->setVCard($vCard);

                $jc = new Contact();
                $jc->setId($id);
                $jc->setAddressBookId($addressBookId);
                $jc->setPrefix($adapter->getPrefix());
                $jc->setFirstName($adapter->getFirstName());
                $jc->setLastName($adapter->getLastName());
                $jc->setSuffix($adapter->getSuffix());
                $jc->setNickname($adapter->getNickname());
                $jc->setBirthday($adapter->getBirthday());
                $jc->setAnniversary($adapter->getAnniversary());
                $jc->setCompany($adapter->getCompany());
                $jc->setDepartment($adapter->getDepartment());
                $jc->setJobTitle($adapter->getJobTitle());
                $jc->setEmails($adapter->getEmails());
                $jc->setPhones($adapter->getPhones());
                $jc->setOnline($adapter->getOnline());
                $jc->setAddresses($adapter->getAddresses());
                $jc->setNotes($adapter->getNotes());
                $jc->setMiddlename($adapter->getMiddlename());
                $jc->setRole($adapter->getRole());
                $jc->setRelatedTo($adapter->getRelatedTo());
                $jc->setAvatar($adapter->getAvatar());
                $jc->setDisplayname($adapter->getDisplayname());

                array_push($list, $jc);
            }
        }

        return $list;
    }
}
