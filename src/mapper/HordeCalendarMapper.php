<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\Calendar\Calendar;

class HordeCalendarMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    /* Horde UI has the properties
     * Title: name
     * Color: color
     * Description: description
     * Tags: TODO add to JMAP
     * TODO Sharing
     * isVisible: isVisible
     */
    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $calendar) {
            $adapter->setCalendar($calendar);

            $jmapCalendar = new Calendar();
            $jmapCalendar->setId($adapter->getId());
            $jmapCalendar->setRole($adapter->getRole());
            $jmapCalendar->setName($adapter->getName());
            $jmapCalendar->setDescription($adapter->getDescription());
            $jmapCalendar->setColor($adapter->getColor());
            $jmapCalendar->setIsVisible($adapter->getIsVisible());

            array_push($list, $jmapCalendar);
        }

        return $list;
    }
}
