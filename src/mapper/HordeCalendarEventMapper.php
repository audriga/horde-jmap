<?php

namespace OpenXPort\Mapper;

use DateTime;
use OpenXPort\Jmap\Calendar\CalendarEvent;

class HordeCalendarEventMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    /**
     * The Horde calendar plugin returns master recurring calendar events and the corresponding modified exception
     * events as separate VEVENT entries in an iCalendar string. Thus, we take the following approach for mapping
     * iCalendar events to JMAP events regarding modified exceptions:
     *
     *  - Create two lists - one for master recurring events and one for modified exceptions
     * which are part of said master events.
     *
     *  - Note: Modified exceptions are distinguishable by the presence of the 'RECURRENCE-ID' property in iCalendar
     * and they have the same 'UID' as their corresponding master events.
     *
     *  - Then, put all modified exceptions together with their master events in the JMAP property 'recurrenceOverrides'
     */
    public function mapToJmap($data, $adapter)
    {
        require_once __DIR__ . '/../../icalendar/zapcallib.php';

        $list = [];

        $masterEvents = [];
        $modifiedExceptions = [];

        foreach ($data as $calendarFolderId => $iCalEvents) {
            // Parse the iCal events of the corresponding calendar folder
            $icalObj = new \ZCiCal($iCalEvents);
            $iCalChildren = $icalObj->curnode->child;

            // Split incoming iCalendar calendar events into master events and modified exceptions as explained above
            foreach ($iCalChildren as $node) {
                if ($node->getName() == "VEVENT") {
                    if (isset($node->data['RECURRENCE-ID']) && !is_null($node->data['RECURRENCE-ID'])) {
                        /**
                         * Save modified exceptions to an array of arrays
                         * The inner array is a map of calendar folder ID to modified exception
                         * This way we don't lose information regarding the calendar folder ID
                         */
                        array_push(
                            $modifiedExceptions,
                            array("folderId" => $calendarFolderId, "modifiedException" => $node)
                        );
                    } else {
                        // Same comment as for modified exceptions applies for master events as well
                        array_push($masterEvents, array("folderId" => $calendarFolderId, "masterEvent" => $node));
                    }
                }
            }
        }

        /**
         * Iterate through all master events and map them accordingly to JMAP format, only leaving out the
         * recurrenceOverrides property, which is handled separately in an inner foreach-loop
         */
        foreach ($masterEvents as $masterEvent) {
            $adapter->setICalEvent($masterEvent["masterEvent"]);

            $jmapMasterEvent = new CalendarEvent();
            $jmapMasterEvent->setType("jsevent");

            $jmapMasterEvent->setUid($adapter->getUid());
            $jmapMasterEvent->setProdId($adapter->getProdId());
            $jmapMasterEvent->setCalendarId($masterEvent["folderId"]);
            $jmapMasterEvent->setCreated($adapter->getCreated());
            $jmapMasterEvent->setUpdated($adapter->getUpdated());

            $jmapMasterEvent->setTitle($adapter->getSummary());
            $jmapMasterEvent->setDescription($adapter->getDescription());

            $jmapMasterEvent->setStart($adapter->getDTStart());
            $jmapMasterEvent->setDuration($adapter->getDuration());
            $jmapMasterEvent->setTimeZone($adapter->getTimeZone());

            $jmapMasterEvent->setStatus($adapter->getStatus());
            $jmapMasterEvent->setLocations($adapter->getLocation());
            $jmapMasterEvent->setKeywords($adapter->getCategories());
            $jmapMasterEvent->setRecurrenceRule($adapter->getRRule());
            $jmapMasterEvent->setRecurrenceOverrides($adapter->getExDate());
            $jmapMasterEvent->setShowWithoutTime($adapter->getShowWithoutTime());
            $jmapMasterEvent->setFreeBusyStatus($adapter->getFreeBusy());
            $jmapMasterEvent->setParticipants($adapter->getParticipants());
            $jmapMasterEvent->setAlerts($adapter->getAlerts());
            $jmapMasterEvent->setLinks($adapter->getLinks());

            // Take each master event's UID, since we're going to need it below in the foreach-loop
            $masterEventUid = $masterEvent["masterEvent"]->data['UID']->getValues();

            /**
             * In this foreach-loop we take all modified exceptions that match the currently iterated master event
             * from the outer foreach-loop (note: comparison between modified exception and master event is done via
             * the UID iCalendar property)
             */
            foreach ($modifiedExceptions as $modEx) {
                // Take each modified exception's UID, since we need it as described above
                $modifiedExceptionUid = $modEx["modifiedException"]->data['UID']->getValues();

                /**
                 * If we've found a corresponding modified exception to a master event, we first transform the
                 * modified exception to JMAP format (we only leave out the JMAP properties '@type',
                 * 'excludedRecurrenceRules', 'method', 'privacy', 'prodId', 'recurrenceId', 'recurrenceOverrides',
                 * 'recurrenceRules', 'relatedTo', 'replyTo' and 'uid', since they shouldn't be included in
                 * recurrenceOverrides entries.
                 * See here: https://datatracker.ietf.org/doc/html/draft-ietf-calext-jscalendar-32#section-4.3.4)
                 */
                if (strcmp($modifiedExceptionUid, $masterEventUid) === 0) {
                    $adapter->setICalEvent($modEx["modifiedException"]);

                    $jmapModifiedException = new CalendarEvent();

                    $jmapModifiedException->setCalendarId($modEx["folderId"]);
                    $jmapModifiedException->setCreated($adapter->getCreated());
                    $jmapModifiedException->setUpdated($adapter->getUpdated());

                    $jmapModifiedException->setTitle($adapter->getSummary());
                    $jmapModifiedException->setDescription($adapter->getDescription());

                    $jmapModifiedException->setStart($adapter->getDTStart());
                    $jmapModifiedException->setDuration($adapter->getDuration());
                    $jmapModifiedException->setTimeZone($adapter->getTimeZone());

                    $jmapModifiedException->setStatus($adapter->getStatus());
                    $jmapModifiedException->setLocations($adapter->getLocation());
                    $jmapModifiedException->setKeywords($adapter->getCategories());
                    $jmapModifiedException->setShowWithoutTime($adapter->getShowWithoutTime());
                    $jmapModifiedException->setFreeBusyStatus($adapter->getFreeBusy());
                    $jmapModifiedException->setParticipants($adapter->getParticipants());
                    $jmapModifiedException->setAlerts($adapter->getAlerts());
                    $jmapModifiedException->setLinks($adapter->getLinks());

                    /**
                     * Each JMAP modified exception is then set to be part of the 'recurrenceOverrides' property of its
                     * corresponding master event.
                     *
                     * For this, we first take the already present values in recurrenceOverrides from the master event
                     * (if any) and then merge the contents of the already existing recurrenceOverrides with the JMAP
                     * modified exception that we got from above.
                     */
                    $currentRecurrenceOverrides = $jmapMasterEvent->getRecurrenceOverrides();
                    if (is_null($currentRecurrenceOverrides)) {
                        $currentRecurrenceOverrides = [];
                    }

                    /**
                     * Since recurrenceOverrides is a map of the value of RECURRENCE-ID in iCalendar (formatted as a
                     * correct date as per JMAP) to the JMAP representation of the modified exception, holding that
                     * RECURRENCE-ID, we set the key of the map to be the value of the RECURRENCE-ID and the value of
                     * the map to be the JMAP modified exception itself
                     */
                    $recurrenceIdValueString = $modEx["modifiedException"]->data['RECURRENCE-ID']->getValues();
                    $recurrenceIdValueDate = DateTime::createFromFormat("Ymd\THis\Z", $recurrenceIdValueString);

                    $recurrenceIdOfModifiedException = date_format($recurrenceIdValueDate, "Y-m-d\TH:i:s");

                    $currentRecurrenceOverrides[$recurrenceIdOfModifiedException] = $jmapModifiedException;

                    // Finally set recurrenceOverrides of the master event to contain the modified exceptions from above
                    $jmapMasterEvent->setRecurrenceOverrides($currentRecurrenceOverrides);
                }
            }
            array_push($list, $jmapMasterEvent);
        }

        return $list;
    }
}
