<?php

require_once __DIR__ . '/../../icalendar/zapcallib.php';

use OpenXPort\Mapper\AbstractMapper;
use Jmap\Calendar\CalendarEvent;

class HordeCalendarEventMapper extends AbstractMapper {

    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];

        foreach ($data as $ce) {
            $icalObj = new \ZCiCal($ce);

            foreach ($icalObj->tree->child as $node) {
                if ($node->getName() == "VEVENT") {
                    $adapter->setICalEvent($node);

                    $jce = new CalendarEvent();
                    $jce->setType("jsevent");
                    $jce->setStart($adapter->getDTStart());
                    $jce->setDuration($adapter->getDuration());
                    $jce->setTitle($adapter->getSummary());
                    $jce->setDescription($adapter->getDescription());
                    $jce->setStatus($adapter->getStatus());
                    $jce->setUid($adapter->getUid());
                    $jce->setProdId($adapter->getProdId());
                    $jce->setCreated($adapter->getCreated());
                    $jce->setUpdated($adapter->getLastModified());
                    $jce->setLocations($adapter->getLocation());
                    $jce->setKeywords($adapter->getCategories());
                    $jce->setRecurrenceRule($adapter->getRRule());
                    $jce->setRecurrenceOverrides($adapter->getExDate());

                    array_push($list, $jce);
                }
            }
        }

        return $list;
    }

}