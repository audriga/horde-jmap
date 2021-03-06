<?php

use OpenXPort\Util\HordeCalendarEventAdapterUtil;

use OpenXPort\Adapter\AbstractAdapter;

use Jmap\Calendar\Location;
use Jmap\Calendar\RecurrenceRule;

class HordeCalendarEventAdapter extends AbstractAdapter {

    // This is an iCal event component (and not an entire iCal object)
    private $iCalEvent;

    public function getICalEvent() {
        return $this->iCalEvent;
    }

    public function setICalEvent($iCalEvent) {
        $this->iCalEvent = $iCalEvent;
    }

    public function getDTStart() {
        $dtStart = $this->iCalEvent->data["DTSTART"];
        
        $date = \DateTime::createFromFormat("Ymd\THis\Z", $dtStart->getValues());
        $jmapStart = date_format($date, "Y-m-d\TH:i:s");
        return $jmapStart;
    }

    public function getDuration() {
        $dtStart = $this->iCalEvent->data["DTSTART"];
        $dtEnd = $this->iCalEvent->data["DTEND"];

        $format = "Ymd\THis";

        $dateStart = \DateTime::createFromFormat($format, $dtStart->getValues());
        $dateEnd = \DateTime::createFromFormat($format, $dtEnd->getValues());

        $interval = $dateEnd->diff($dateStart);
        return $interval->format('P%dDT%hH%IM');
        
    }

    public function getSummary() {
        return $this->iCalEvent->data["SUMMARY"]->getValues();
    }

    public function getDescription() {
        $description = $this->iCalEvent->data["DESCRIPTION"];

        if (is_null($description)) {
            return NULL;
        }

        return $description->getValues();
    }

    public function getStatus() {
        $status = $this->iCalEvent->data['STATUS'];

        if (is_null($status)) {
            return NULL;
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
                return NULL;
                break;
        }
    }

    public function getUid() {
        $uid = $this->iCalEvent->data['UID'];

        if (is_null($uid)) {
            return NULL;
        }

        return $uid->getValues();
    }

    public function getProdId() {
        $prodId = null;
        
        if (strcmp($this->iCalEvent->parentnode->name, 'VCALENDAR') !== 0) {
            $prodId = $this->iCalEvent->parentnode = $this->iCalEvent->parentnode->parentnode->data['PRODID'];
        } else {
            $prodId = $this->iCalEvent->parentnode->data['PRODID'];
        }

        if (is_null($prodId)) {
            return null;
        }

        return $prodId->getValues();
    }

    public function getCreated() {
        $created = $this->iCalEvent->data['CREATED'];

        if (is_null($created)) {
            return NULL;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateCreated = \DateTime::createFromFormat($iCalFormat, $created->getValues());
        $jmapCreated = date_format($dateCreated, $jmapFormat);

        return $jmapCreated;
    }

    public function getLastModified() {
        $lastModified = $this->iCalEvent->data['LAST-MODIFIED'];

        if (is_null($lastModified)) {
            return NULL;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateLastModified = \DateTime::createFromFormat($iCalFormat, $lastModified->getValues());
        $jmapLastModified = date_format($dateLastModified, $jmapFormat);

        return $jmapLastModified;
    }

    public function getSequence() {
        $sequence = $this->iCalEvent->data['SEQUENCE'];

        if (is_null($sequence)) {
            return 0;
        }

        return $sequence->getValues();
    }

    public function getLocation() {
        $location = $this->iCalEvent->data['LOCATION'];

        if (is_null($location)) {
            return NULL;
        }

        $jmapLocations = [];

        $locationValue = $location->getValues();

        $jmapLocation = new Location();
        $jmapLocation->setType("Location");
        $jmapLocation->setName($locationValue);
        
        // Create an ID as a key in the array via base64 (it should just be some random string; I'm picking base64 as a random option)
        $key = base64_encode($locationValue);
        $jmapLocations["$key"] = $jmapLocation;

        return $jmapLocations;
    }

    public function getCategories() {
        $categories = $this->iCalEvent->data['CATEGORIES'];

        if (is_null($categories)) {
            return NULL;
        }

        $jmapKeywords = [];

        $categoryValues = explode(",", $categories->getValues());

        foreach ($categoryValues as $c) {
            $jmapKeywords[$c] = true;
        }

        return $jmapKeywords;
    }

    public function getRRule() {
        $rRule = $this->iCalEvent->data['RRULE'];

        if (is_null($rRule)) {
            return NULL;
        }

        $rRuleValues = $rRule->getValues();

        // The library treats commas in RRULE as separator for rules and thus we need to fix this by putting the separated RRULE back together as one whole (and not as separate rules)
        if (!empty($rRule->getValues()) && count($rRule->getValues()) > 1) {
            $rRuleValues = implode(",", $rRule->getValues());
        }

        $jmapRecurrenceRule = new RecurrenceRule();
        $jmapRecurrenceRule->setType("RecurrenceRule");

        foreach (explode(";", $rRuleValues) as $r) {
            // Split each rule string by '=' and based upon its key (e.g. FREQ, COUNT, etc.), set the corresponding value to the JMAP RecurrenceRule object
            $splitRule = explode("=", $r);
            $key = $splitRule[0];
            $value = $splitRule[1];

            switch ($key) {
                case 'FREQ':
                    $jmapRecurrenceRule->setFrequency(HordeCalendarEventAdapterUtil::convertFromICalFreqToJmapFrequency($value));
                    break;
                
                case 'INTERVAL':
                    $jmapRecurrenceRule->setInterval(HordeCalendarEventAdapterUtil::convertFromICalIntervalToJmapInterval($value));
                    break;

                case 'RSCALE':
                    $jmapRecurrenceRule->setRscale(HordeCalendarEventAdapterUtil::convertFromICalRScaleToJmapRScale($value));
                    break;
                
                case 'SKIP':
                    $jmapRecurrenceRule->setSkip(HordeCalendarEventAdapterUtil::convertFromICalSkipToJmapSkip($value));
                    break;

                case 'WKST':
                    $jmapRecurrenceRule->setFirstDayOfWeek(HordeCalendarEventAdapterUtil::convertFromICalWKSTToJmapFirstDayOfWeek($value));
                    break;
                
                case 'BYDAY':
                    $jmapRecurrenceRule->setByDay(HordeCalendarEventAdapterUtil::convertFromICalByDayToJmapByDay($value));
                    break;

                case 'BYMONTHDAY':
                    $jmapRecurrenceRule->setByMonthDay(HordeCalendarEventAdapterUtil::convertFromICalByMonthDayToJmapByMonthDay($value));
                    break;
                
                case 'BYMONTH':
                    $jmapRecurrenceRule->setByMonth(HordeCalendarEventAdapterUtil::convertFromICalByMonthToJmapByMonth($value));
                    break;

                case 'BYYEARDAY':
                    $jmapRecurrenceRule->setByYearDay(HordeCalendarEventAdapterUtil::convertFromICalByYearDayToJmapByYearDay($value));
                    break;
                
                case 'BYWEEKNO':
                    $jmapRecurrenceRule->setByWeekNo(HordeCalendarEventAdapterUtil::convertFromICalByWeekNoToJmapByWeekNo($value));
                    break;

                case 'BYHOUR':
                    $jmapRecurrenceRule->setByHour(HordeCalendarEventAdapterUtil::convertFromICalByHourToJmapByHour($value));
                    break;
                
                case 'BYMINUTE':
                    $jmapRecurrenceRule->setByMinute(HordeCalendarEventAdapterUtil::convertFromICalByMinuteToJmapByMinute($value));
                    break;

                case 'BYSECOND':
                    $jmapRecurrenceRule->setBySecond(HordeCalendarEventAdapterUtil::convertFromICalBySecondToJmapBySecond($value));
                    break;
                
                case 'BYSETPOS':
                    $jmapRecurrenceRule->setBySetPosition(HordeCalendarEventAdapterUtil::convertFromICalBySetPositionToJmapBySetPos($value));
                    break;

                case 'COUNT':
                    $jmapRecurrenceRule->setCount(HordeCalendarEventAdapterUtil::convertFromICalCountToJmapCount($value));
                    break;
                
                case 'UNTIL':
                    $jmapRecurrenceRule->setUntil(HordeCalendarEventAdapterUtil::convertFromICalUntilToJmapUntil($value));
                    break;

                default:
                    // Maybe log something about an unexpected property/value in the parsed iCal RRULE?
                    break;
            }
        }

        return $jmapRecurrenceRule;
    }

    public function getExDate() {
        $exDate = $this->iCalEvent->data['EXDATE'];

        if (is_null($exDate)) {
            return NULL;
        }

        $splitExDateValues = explode(",", $exDate->getValues());
        
        $jmapRecurrenceOverrides = [];

        foreach ($splitExDateValues as $v) {
            $iCalFormat = 'Ymd\THis\Z';
            $jmapFormat = 'Y-m-d\TH:i:s';

            $dateExDate = \DateTime::createFromFormat($iCalFormat, $v);
            $jmapExcludedRecurrenceOverride = date_format($dateExDate, $jmapFormat);

            $jmapRecurrenceOverrides[$jmapExcludedRecurrenceOverride] = array("@type" => "jsevent", "excluded" => true);
        }

        return $jmapRecurrenceOverrides;
    }

    public function getPriority() {
        $priority = $this->iCalEvent->data['PRIORITY'];

        if (is_null($priority)) {
            return NULL;
        }

        return $priority->getValues();
    }

    public function getClass() {
        $class = $this->iCalEvent->data['CLASS'];

        if (is_null($class)) {
            return NULL;
        }

        switch ($class->getValues()) {
            case 'PUBLIC':
                return "public";

            case 'PRIVATE':
                return "private";

            case 'CONFIDENTIAL':
                return "secret";

            default:
                return NULL;
        }
    }

    public function getTimeZone() {
        $timezoneComponent = $this->iCalEvent->parentNode->tree->child['VTIMEZONE'];
        return $timezoneComponent;
    }

}