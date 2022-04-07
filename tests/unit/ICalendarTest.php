<?php

namespace OpenXPort\Test\ICalendar;

use PHPUnit\Framework\TestCase;
use OpenXPort\Adapter\HordeCalendarEventAdapter;

final class ICalendarTest extends TestCase
{
    /**
     * @var iCalendar
     */
    protected $vCard;

    /**
     * @var HoderICalendarAdapter
     */
    protected $adapter;

    /**
     * @var HoderICalendarMapper
     */
    protected $mapper;

    private function readICS($filename)
    {
        $handle = fopen($filename, 'r');
        $this->iCalendar = fread($handle, filesize($filename));

        fclose($handle);
    }

    public function setUp(): void
    {
        require_once __DIR__ . '/../../icalendar/zapcallib.php';

        $this->adapter = new \OpenXPort\Adapter\HordeCalendarEventAdapter();
        $this->mapper = new \OpenXPort\Mapper\HordeCalendarEventMapper();
    }

    public function testHordeICalendarToParticipants()
    {
        $this->readICS(__DIR__ . '/../resources/icalendar_with_attendees2.ics');

        $iCalData = array("1" => $this->iCalendar);

        $jmapEvent = $this->mapper->mapToJmap($iCalData, $this->adapter)[0];

        foreach ($jmapEvent->getParticipants() as $id => $participant) {
            $curSendTo = $participant->getSendTo();
            $this->assertArrayHasKey("imip", $curSendTo);

            # print_r($jmapEvent->jsonSerialize());
            if ($curSendTo["imip"] == "mailto:openxport@audriga.eu") {
                $this->assertArrayHasKey("owner", $participant->getRoles());
                $this->assertArrayHasKey("attendee", $participant->getRoles());
                $this->assertEquals("Newfullname", $participant->getName());
            } elseif ($curSendTo["imip"] == "mailto:joris@audriga.com") {
                $this->assertArrayHasKey("attendee", $participant->getRoles());
            } else {
                // This should not happen
                var_dump($participant);
                $this->assertEquals("somethingiswrong", $curSendTo["imip"]);
            }
        }

        $this->assertEquals("2021-12-06T12:53:12Z", $jmapEvent->getUpdated());
    }

    public function testBrokenHordeICalendarToParticipants()
    {
        $this->readICS(__DIR__ . '/../resources/icalendar_with_attendees.ics');

        $iCalData = array("1" => $this->iCalendar);

        $jmapEvent = $this->mapper->mapToJmap($iCalData, $this->adapter)[0];

        foreach ($jmapEvent->getParticipants() as $id => $participant) {
            $curSendTo = $participant->getSendTo();
            $this->assertArrayHasKey("imip", $curSendTo);

            if ($curSendTo["imip"] == "mailto:openxport@audriga.eu") {
                // openxport: sendTo has imip -> mailto:openxport@audriga.eu and owner and participant
                $this->assertArrayHasKey("owner", $participant->getRoles());
                $this->assertEquals("Newfullname", $participant->getName());
            } elseif ($curSendTo["imip"] == "mailto:m@migrator.de") {
                // migrator: sendTo has imip -> mailto:m@migrator.de and participant
                $this->assertArrayHasKey("attendee", $participant->getRoles());
            } else {
                // This should not happen
                var_dump($participant);
                $this->assertEquals("somethingiswrong", $curSendTo["imip"]);
            }
        }

        $this->assertEquals("2021-06-11T14:06:16Z", $jmapEvent->getUpdated());
    }

    // From example https://web.audriga.com/mantis/view.php?id=5675
    public function testParticipantWithWeirdSendToMethod()
    {
        $this->readICS(__DIR__ . '/../resources/icalendar_with_attendees2_edited.ics');

        $iCalData = array("1" => $this->iCalendar);

        $jmapEvent = $this->mapper->mapToJmap($iCalData, $this->adapter)[0];

        foreach ($jmapEvent->getParticipants() as $id => $participant) {
            $curSendTo = $participant->getSendTo();

            # print_r($jmapEvent->jsonSerialize());
            if ($curSendTo["imip"] == "mailto:openxport@audriga.eu") {
                $this->assertArrayHasKey("owner", $participant->getRoles());
                $this->assertEquals("Newfullname", $participant->getName());
            } elseif ($curSendTo["imip"] == "mailto:joris@audriga.com") {
                $this->assertArrayHasKey("attendee", $participant->getRoles());
            } elseif ($curSendTo["other"] == "somethingweird:wp13405851-openxport") {
                $this->assertArrayHasKey("attendee", $participant->getRoles());
            } else {
                // This should not happen
                var_dump($participant);
                $this->assertEquals("somethingiswrong", $curSendTo["imip"]);
            }
        }
    }

    // For https://web.audriga.com/mantis/view.php?id=5682
    public function testNoLastModified()
    {
        $this->readICS(__DIR__ . '/../resources/icalendar_with_attendees_no_last_modified.ics');

        $iCalData = array("1" => $this->iCalendar);

        $jmapEvent = $this->mapper->mapToJmap($iCalData, $this->adapter)[0];

        $this->assertEquals("2021-12-06T12:53:21Z", $jmapEvent->getUpdated());
    }

    // For https://web.audriga.com/mantis/view.php?id=5777
    public function testNegativeByDayInRRule()
    {
        $this->readICS(__DIR__ . '/../resources/icalendar_with_negative_byday_rrule.ics');

        $iCalData = array("1" => $this->iCalendar);

        $jmapEvent = $this->mapper->mapToJmap($iCalData, $this->adapter)[0];

        $this->assertEquals(-1, $jmapEvent->getRecurrenceRule()->getByDay()[0]->getNthOfPeriod());
    }

    public function testByDayWithPlusSignInRRule()
    {
        $this->readICS(__DIR__ . '/../resources/icalendar_with_byday_with_plus.ics');

        $iCalData = array("1" => $this->iCalendar);

        $jmapEvent = $this->mapper->mapToJmap($iCalData, $this->adapter)[0];

        $this->assertEquals(1, $jmapEvent->getRecurrenceRule()->getByDay()[0]->getNthOfPeriod());
    }
}
