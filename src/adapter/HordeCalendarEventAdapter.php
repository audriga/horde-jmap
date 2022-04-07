<?php

namespace OpenXPort\Adapter;

use OpenXPort\Util\HordeCalendarEventAdapterUtil;
use OpenXPort\Adapter\AbstractAdapter;
use OpenXPort\Jmap\Calendar\Alert;
use OpenXPort\Jmap\Calendar\Link;
use OpenXPort\Jmap\Calendar\Location;
use OpenXPort\Jmap\Calendar\Participant;
use OpenXPort\Jmap\Calendar\RecurrenceRule;
use OpenXPort\Jmap\Calendar\OffsetTrigger;
use OpenXPort\Util\AdapterUtil;

class HordeCalendarEventAdapter extends AbstractAdapter
{
    // This is an iCal event component (and not an entire iCal object)
    private $iCalEvent;

    public function getICalEvent()
    {
        return $this->iCalEvent;
    }

    public function setICalEvent($iCalEvent)
    {
        $this->iCalEvent = $iCalEvent;
    }

    public function getDTStart()
    {
        $dtStart = $this->iCalEvent->data["DTSTART"];

        $jmapStart = null;
        $date = \DateTime::createFromFormat("Ymd\THis\Z", $dtStart->getValues());

        // If there's no 'Z' at the end of the date, try to parse the date without it
        if ($date === false) {
            $date = \DateTime::createFromFormat("Ymd\THis", $dtStart->getValues());
        }

        // If the date still can't be parsed, try parsing it without a time component
        if ($date === false) {
            $date = \DateTime::createFromFormat("Ymd", $dtStart->getValues());
            $jmapStart = \date_format($date, "Y-m-d");

            // Add default values for time in the 'start' JMAP property
            $jmapStart .= "T00:00:00";

            return $jmapStart;
        }

        $jmapStart = date_format($date, "Y-m-d\TH:i:s");
        return $jmapStart;
    }

    public function getDuration()
    {
        $dtStart = $this->iCalEvent->data["DTSTART"];
        $dtEnd = $this->iCalEvent->data["DTEND"];

        $format = "Ymd\THis";
        $formatWithZ = "Ymd\THis\Z";

        $interval = null;

        $dateStart = \DateTime::createFromFormat($formatWithZ, $dtStart->getValues());
        $dateEnd = \DateTime::createFromFormat($formatWithZ, $dtEnd->getValues());

        // Analogically to getDTStart(), try different parsing strategy for dates, in case they didn't parse correctly
        // at first
        if ($dateStart === false || $dateEnd === false) {
            $dateStart = \DateTime::createFromFormat($format, $dtStart->getValues());
            $dateEnd = \DateTime::createFromFormat($format, $dtEnd->getValues());
        }

        if ($dateStart === false || $dateEnd === false) {
            $dateStart = \DateTime::createFromFormat("Ymd", $dtStart->getValues());
            $dateEnd = \DateTime::createFromFormat("Ymd", $dtEnd->getValues());
        }

        $interval = $dateEnd->diff($dateStart);
        return $interval->format('P%dDT%hH%IM');
    }

    public function getSummary()
    {
        return $this->iCalEvent->data["SUMMARY"]->getValues();
    }

    public function getDescription()
    {
        $description = $this->iCalEvent->data["DESCRIPTION"];

        if (is_null($description)) {
            return null;
        }

        return $description->getValues();
    }

    public function getStatus()
    {
        $status = $this->iCalEvent->data['STATUS'];

        if (is_null($status)) {
            return null;
        }

        switch ($status->getValues()) {
            case 'TENTATIVE':
                return "tentative";
                break;

            case 'CONFIRMED':
                return "confirmed";
                break;

            case 'CANCELLED':
                return "cancelled";
                break;

            default:
                return null;
                break;
        }
    }

    public function getUid()
    {
        $uid = $this->iCalEvent->data['UID'];

        if (is_null($uid)) {
            return null;
        }

        return $uid->getValues();
    }

    public function getProdId()
    {
        if (strcmp($this->iCalEvent->parentnode->name, 'VCALENDAR') !== 0) {
            $prodId = $this->iCalEvent->parentnode->parentnode->data['PRODID'];
        } else {
            $prodId = $this->iCalEvent->parentnode->data['PRODID'];
        }

        if (is_null($prodId)) {
            return null;
        }

        return $prodId->getValues();
    }

    public function getCreated()
    {
        $created = $this->iCalEvent->data['CREATED'];

        if (is_null($created)) {
            return null;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateCreated = \DateTime::createFromFormat($iCalFormat, $created->getValues());
        $jmapCreated = date_format($dateCreated, $jmapFormat);

        return $jmapCreated;
    }

    public function getUpdated()
    {
        $updated = null;
        $lastModified = $this->iCalEvent->data['LAST-MODIFIED'];
        $dtStamp = $this->iCalEvent->data['DTSTAMP'];

        if (!is_null($lastModified)) {
            $updated = $this->iCalEvent->data['LAST-MODIFIED'];
        } elseif (!is_null($dtStamp)) {
            $updated = $this->iCalEvent->data['DTSTAMP'];
        } else {
            return null;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateUpdated = \DateTime::createFromFormat($iCalFormat, $updated->getValues());
        $jmapUpdated = date_format($dateUpdated, $jmapFormat);

        return $jmapUpdated;
    }

    public function getSequence()
    {
        $sequence = $this->iCalEvent->data['SEQUENCE'];

        if (is_null($sequence)) {
            return 0;
        }

        return $sequence->getValues();
    }

    public function getLocation()
    {
        $location = $this->iCalEvent->data['LOCATION'];

        if (is_null($location)) {
            return null;
        }

        $jmapLocations = [];

        $locationValue = $location->getValues();

        $jmapLocation = new Location();
        $jmapLocation->setType("Location");
        $jmapLocation->setName($locationValue);

        // Create an ID as a key in the array via base64 (it should just be some random string; I'm picking base64 as
        // a random option)
        $key = base64_encode($locationValue);
        $jmapLocations["$key"] = $jmapLocation;

        return $jmapLocations;
    }

    public function getLinks()
    {

        if (!array_key_exists("URL", $this->iCalEvent->data)) {
            return null;
        }

        $url = $this->iCalEvent->data["URL"];

        $jmapLinks = [];

        $urlValue = $url->getValues();

        $jmapLink = new Link();
        $jmapLink->setType("Link");
        $jmapLink->setHref($urlValue);

        // Create an ID as a key in the array via base64 (it should just be some random string; I'm picking base64 as
        // a random option)
        $key = base64_encode($urlValue);
        $jmapLinks["$key"] = $jmapLink;

        return $jmapLinks;
    }

    public function getFreeBusy()
    {

        if (!array_key_exists("TRANSP", $this->iCalEvent->data)) {
            return null;
        }

        $transp = $this->iCalEvent->data["TRANSP"];

        switch ($transp->getValues()) {
            case 'OPAQUE':
                return "busy";

            case 'TRANSPARENT':
                return "free";

            default:
                return null;
        }
    }

    public function getAlerts()
    {

        if (sizeof($this->iCalEvent->child) == 0) {
            return null;
        }

        $jmapAlerts = [];

        foreach ($this->iCalEvent->child as $childNode) {
            if ($childNode->getName() == 'VALARM') {
                // TODO actual conversion is more complex

                $trigger = new OffsetTrigger();
                $trigger->setType("OffsetTrigger");
                $trigger->setOffset($childNode->data["TRIGGER"]->getValues());

                $alert = new Alert();
                // TODO current default
                $alert->setAction("display");
                $alert->setTrigger($trigger);

                // Create an ID as a key in the array via base64 (it should just be some random string; I'm picking
                // base64 as a random option)
                $key = base64_encode($urlValue);
                $jmapAlerts["$key"] = $alert;
            }
        }

        if (sizeof($jmapAlerts) == 0) {
            return null;
        }

        return $jmapAlerts;
    }

    public function getCategories()
    {
        $categories = $this->iCalEvent->data['CATEGORIES'];

        if (is_null($categories)) {
            return null;
        }

        $jmapKeywords = [];

        $categoryValues = explode(",", $categories->getValues());

        foreach ($categoryValues as $c) {
            $jmapKeywords[$c] = true;
        }

        return $jmapKeywords;
    }

    public function getRRule()
    {
        $rRule = $this->iCalEvent->data['RRULE'];

        if (is_null($rRule)) {
            return null;
        }

        $rRuleValues = $rRule->getValues();

        if (empty($rRuleValues)) {
            return null;
        }

        // The library treats commas in RRULE as separator for rules and thus we need to fix this by putting the
        // separated RRULE back together as one whole (and not as separate rules)
        if (is_array($rRuleValues)) {
            $rRuleValues = implode(",", $rRuleValues);
        }

        $jmapRecurrenceRule = new RecurrenceRule();
        $jmapRecurrenceRule->setType("RecurrenceRule");

        foreach (explode(";", $rRuleValues) as $r) {
            // Split each rule string by '=' and based upon its key (e.g. FREQ, COUNT, etc.), set the corresponding
            // value to the JMAP RecurrenceRule object
            $splitRule = explode("=", $r);
            $key = $splitRule[0];
            $value = $splitRule[1];

            switch ($key) {
                case 'FREQ':
                    $jmapRecurrenceRule->setFrequency(
                        HordeCalendarEventAdapterUtil::convertFromICalFreqToJmapFrequency($value)
                    );
                    break;

                case 'INTERVAL':
                    $jmapRecurrenceRule->setInterval(
                        HordeCalendarEventAdapterUtil::convertFromICalIntervalToJmapInterval($value)
                    );
                    break;

                case 'RSCALE':
                    $jmapRecurrenceRule->setRscale(
                        HordeCalendarEventAdapterUtil::convertFromICalRScaleToJmapRScale($value)
                    );
                    break;

                case 'SKIP':
                    $jmapRecurrenceRule->setSkip(
                        HordeCalendarEventAdapterUtil::convertFromICalSkipToJmapSkip($value)
                    );
                    break;

                case 'WKST':
                    $jmapRecurrenceRule->setFirstDayOfWeek(
                        HordeCalendarEventAdapterUtil::convertFromICalWKSTToJmapFirstDayOfWeek($value)
                    );
                    break;

                case 'BYDAY':
                    $jmapRecurrenceRule->setByDay(
                        HordeCalendarEventAdapterUtil::convertFromICalByDayToJmapByDay($value)
                    );
                    break;

                case 'BYMONTHDAY':
                    $jmapRecurrenceRule->setByMonthDay(
                        HordeCalendarEventAdapterUtil::convertFromICalByMonthDayToJmapByMonthDay($value)
                    );
                    break;

                case 'BYMONTH':
                    $jmapRecurrenceRule->setByMonth(
                        HordeCalendarEventAdapterUtil::convertFromICalByMonthToJmapByMonth($value)
                    );
                    break;

                case 'BYYEARDAY':
                    $jmapRecurrenceRule->setByYearDay(
                        HordeCalendarEventAdapterUtil::convertFromICalByYearDayToJmapByYearDay($value)
                    );
                    break;

                case 'BYWEEKNO':
                    $jmapRecurrenceRule->setByWeekNo(
                        HordeCalendarEventAdapterUtil::convertFromICalByWeekNoToJmapByWeekNo($value)
                    );
                    break;

                case 'BYHOUR':
                    $jmapRecurrenceRule->setByHour(
                        HordeCalendarEventAdapterUtil::convertFromICalByHourToJmapByHour($value)
                    );
                    break;

                case 'BYMINUTE':
                    $jmapRecurrenceRule->setByMinute(
                        HordeCalendarEventAdapterUtil::convertFromICalByMinuteToJmapByMinute($value)
                    );
                    break;

                case 'BYSECOND':
                    $jmapRecurrenceRule->setBySecond(
                        HordeCalendarEventAdapterUtil::convertFromICalBySecondToJmapBySecond($value)
                    );
                    break;

                case 'BYSETPOS':
                    $jmapRecurrenceRule->setBySetPosition(
                        HordeCalendarEventAdapterUtil::convertFromICalBySetPositionToJmapBySetPos($value)
                    );
                    break;

                case 'COUNT':
                    $jmapRecurrenceRule->setCount(
                        HordeCalendarEventAdapterUtil::convertFromICalCountToJmapCount($value)
                    );
                    break;

                case 'UNTIL':
                    $jmapRecurrenceRule->setUntil(
                        HordeCalendarEventAdapterUtil::convertFromICalUntilToJmapUntil($value)
                    );
                    break;

                default:
                    // Maybe log something about an unexpected property/value in the parsed iCal RRULE?
                    break;
            }
        }

        return $jmapRecurrenceRule;
    }

    public function getExDate()
    {
        $exDate = $this->iCalEvent->data['EXDATE'];

        if (is_null($exDate)) {
            return null;
        }

        $splitExDateValues = explode(",", $exDate->getValues());

        $jmapRecurrenceOverrides = [];

        foreach ($splitExDateValues as $v) {
            $iCalFormat = 'Ymd\THis\Z';
            $alternativeICalFormat = 'Ymd';
            $jmapFormat = 'Y-m-d\TH:i:s';

            $jmapExcludedRecurrenceOverride = AdapterUtil::parseDateTime(
                $v,
                $iCalFormat,
                $jmapFormat,
                $alternativeICalFormat
            );

            $jmapRecurrenceOverrides[$jmapExcludedRecurrenceOverride] = array("@type" => "jsevent", "excluded" => true);
        }

        return $jmapRecurrenceOverrides;
    }

    public function getPriority()
    {
        $priority = $this->iCalEvent->data['PRIORITY'];

        if (is_null($priority)) {
            return null;
        }

        return $priority->getValues();
    }

    public function getClass()
    {
        $class = $this->iCalEvent->data['CLASS'];

        if (is_null($class)) {
            return null;
        }

        switch ($class->getValues()) {
            case 'PUBLIC':
                return "public";

            case 'PRIVATE':
                return "private";

            case 'CONFIDENTIAL':
                return "secret";

            default:
                return null;
        }
    }

    public function getParticipants()
    {

        if (!array_key_exists("ATTENDEE", $this->iCalEvent->data)) {
            return null;
        }

        $attendee = $this->iCalEvent->data["ATTENDEE"];

        // Make sure to flatten $attendee, since it could be a multi-dimensional array due to a
        // potential issue in the iCalendar library that we use for iCalendar parsing.
        // See more info here: https://web.audriga.com/mantis/view.php?id=5476
        $attendee = HordeCalendarEventAdapterUtil::flattenMultiDimensionalArray($attendee);

        $organizer = $this->iCalEvent->data["ORGANIZER"];

        $jmapParticipants = [];

        if (!is_null($attendee)) {
            // 'ATTENDEE' can either be array (if more than one attendees set) or an object
            // In order to avoid code duplication for attendee data migration, if 'ATTENDEE' is not array, we turn it
            // into a single-element array
            if (!is_array($attendee)) {
                $attendee = array($attendee);
            }

            foreach ($attendee as $a) {
                $aValue = $a->getValues();

                // Do not use attendees with empty values
                if ($aValue == "") {
                    continue;
                }

                // Split value from ATTENDEE by ':'
                $aValueSplit = explode(":", $aValue);

                $jmapParticipant = new Participant();

                $jmapParticipant->setType("Participant");
                if ($aValueSplit[0] == "mailto") {
                    $jmapParticipant->setSendTo(array("imip" => $aValue));
                } else {
                    $jmapParticipant->setSendTo(array("other" => $aValue));
                }
                $jmapParticipant->setName($a->getParameters()["cn"]);

                $jmapKind = HordeCalendarEventAdapterUtil::convertFromICalCUTypeToJmapKind(
                    $a->getParameters()["cutype"]
                );

                $jmapParticipant->setKind($jmapKind);

                $role = $a->getParameters()["role"];
                $jmapRoles = HordeCalendarEventAdapterUtil::convertFromICalRoleToJmapRole($role);
                $jmapParticipant->setRoles($jmapRoles);

                $partStat = $a->getParameters()["partstat"];
                if (!is_null($partStat)) {
                    $jmapParticipant->setParticipationStatus(\strtolower($partStat));
                }

                $rsvp = $a->getParameters()["rsvp"];
                if (strcmp($rsvp, "TRUE") === 0) {
                    $jmapParticipant->setExpectReply(true);
                }

                // Generate participant ID from string representation of participant:
                // iCalendar has no IDs so we assume each participant with different values is its own participant
                $participantId = md5(print_r($jmapParticipant, true));

                $jmapParticipants["$participantId"] = $jmapParticipant;
            }
        }

        if (!is_null($organizer)) {
            $oValue = $organizer->getValues();

            // Split value from ORGANIZER by ':'
            $oValueSplit = explode(":", $oValue);

            // Only add new role if organizer has already been added as participant
            $jmapParticipant = null;
            $participantId = null;

            foreach ($jmapParticipants as $id => $participant) {
                $curSendTo = $participant->getSendTo();
                if (
                    $oValueSplit[0] == "mailto" &&
                    array_key_exists("imip", $curSendTo) &&
                    $curSendTo["imip"] == $oValue
                ) {
                    $curRoles = $participant->getRoles();

                    $curRoles["owner"] = true;
                    $participant->setRoles($curRoles);
                    $jmapParticipant = $participant;
                    $participantId = $id;
                } elseif (array_key_exists("other", $curSendTo) && $curSendTo["other"] == $oValue) {
                    $curRoles = $participant->getRoles();

                    $curRoles["owner"] = true;
                    $participant->setRoles($curRoles);
                    $jmapParticipant = $participant;
                    $participantId = $id;
                }
            }

            if (is_null($jmapParticipant)) {
                $jmapParticipant = new Participant();
                $jmapParticipant->setType("Participant");

                if ($oValueSplit[0] == "mailto") {
                    $jmapParticipant->setSendTo(array("imip" => $oValue));
                } else {
                    $jmapParticipant->setSendTo(array("other" => $oValue));
                }

                $jmapParticipant->setRoles(array("owner" => true));

                // Generate participant ID from string representation of participant:
                // iCalendar has no IDs so we assume each participant with different values is its own participant
                $participantId = md5(print_r($jmapParticipant, true));
            }

            if (!empty($organizer->getParameters()["cn"])) {
                $jmapParticipant->setName($organizer->getParameters()["cn"]);
            }

            if (!empty($organizer->getParameters()["cutype"])) {
                $jmapKind = HordeCalendarEventAdapterUtil::convertFromICalCUTypeToJmapKind(
                    $organizer->getParameters()["cutype"]
                );
                $jmapParticipant->setKind($jmapKind);
            }

            if (!empty($organizer->getParameters()["cutype"])) {
                $partStat = $organizer->getParameters()["partstat"];
                if (!is_null($partStat)) {
                    $jmapParticipant->setParticipationStatus(\strtolower($partStat));
                }
            }

            $jmapParticipant->setExpectReply(false);

            $jmapParticipants["$participantId"] = $jmapParticipant;
        }

        return $jmapParticipants;
    }

    public function getTimeZone()
    {
        //$timezoneComponent = $this->iCalEvent->parentNode->tree->child['VTIMEZONE'];
        //return $timezoneComponent;

        if (!array_key_exists("DTSTART", $this->iCalEvent->data)) {
            return null;
        }

        $dtStart = $this->iCalEvent->data["DTSTART"];
        if (array_key_exists("tzid", $dtStart->getParameters())) {
            return $dtStart->getParameter("tzid");
        }

        return null;
    }

    public function getShowWithoutTime()
    {
        $dtStart = $this->iCalEvent->data["DTSTART"];
        $dtEnd = $this->iCalEvent->data["DTEND"];

        // Full day format for dates, e.g. 20210615, where 'Y' is year (2021), 'm' month (06) and 'd' day (15)
        // See https://www.php.net/manual/en/datetime.createfromformat.php
        $fullDayDateFormat = "Ymd";

        $dateStart = \DateTime::createFromFormat($fullDayDateFormat, $dtStart->getValues());
        $dateEnd = \DateTime::createFromFormat($fullDayDateFormat, $dtEnd->getValues());

        /**
         * If createFromFormat() above does not return false (i.e. parses successfully) for the full day format for
         * 'DTSTART' and 'DTEND', this means that both of these dates do not include time, i.e. are formatted without
         * time. Based on this, we set the JMAP property 'showWithoutTime' to true to indicate a full day event.
         */
        if ($dateStart !== false && $dateEnd !== false) {
            return true;
        }

        return false;
    }
}
