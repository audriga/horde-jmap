<?php

namespace OpenXPort\Mapper;

use JeroenDesloovere\VCard\Parser\Parser;
use JeroenDesloovere\VCard\Parser\VcfParser;

use OpenXPort\Mapper\AbstractMapper;
use Jmap\Contact\Contact;

class HordeContactMapper extends AbstractMapper {

    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];

        foreach ($data as $c) {
            $vcfParser = new VcfParser();
            $parser = new Parser($vcfParser, $c);
            
            $vCard = $parser->getVCards()[0];

            $adapter->setVCard($vCard);

            $jc = new Contact();
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

            array_push($list, $jc);
        }

        return $list;
    }

}