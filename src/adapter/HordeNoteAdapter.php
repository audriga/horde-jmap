<?php

namespace OpenXPort\Adapter;

use OpenXPort\Util\AdapterUtil;

class HordeNoteAdapter extends AbstractAdapter
{
    // We use the Horde_ICalendar library (see https://github.com/horde/Icalendar),
    // since we make use of the vNote format for working with notes in Horde and
    // this library provides functionality for parsing and working with vNote data.
    /** @var \Horde_ICalendar */
    private $note;

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($vNote)
    {
        // When we receive a new vNote, we parse it via Horde_ICalendar
        // and save the parsed vNote data in this class' $note property.
        // The Horde_ICalendar library despite its name is actually intended
        // to be able to deal with iCalendar (.ics), vCard (.vcf) and vNote (.vnt)
        // files, among others. That's why we currently make use of it here, since
        // it is the most convenient way to parse vNote data and is also utilized
        // in Horde itself for this purpose.
        // See here: https://github.com/horde/Icalendar/
        // For usage in Horde for vNote data, see here:
        // https://github.com/horde/mnemo/blob/2489d4f434f3ecd8cef7078f98fd2ba6f72c22bd/lib/Api.php#L612
        $this->note = new \Horde_ICalendar();
        $this->note->parsevCalendar($vNote);

        // Once the vNote was parsed, it is saved as a component within the entire iCalendar
        // object that was created during parsing. That's why we take this component with the
        // vNote data and set it as the value for $this->note
        $this->note = $this->note->getComponents()[0];
    }

    public function getId()
    {
        $uid = $this->note->getAttribute('UID');

        if (AdapterUtil::isSetAndNotNull($uid) && !empty($uid)) {
            return $uid;
        }

        return null;
    }

    public function getBody()
    {
        $body = $this->note->getAttribute('BODY');

        if (AdapterUtil::isSetAndNotNull($body) && !empty($body)) {
            return $body;
        }

        return null;
    }

    public function getName()
    {
        $name = $this->note->getAttribute('SUMMARY');

        if (AdapterUtil::isSetAndNotNull($name) && !empty($name)) {
            return $name;
        }

        return null;
    }

    public function getKeywords()
    {
        $jmapKeywords = [];

        // Since the property CATEGORIES is not always contained in a vNote
        // from Horde, it is possible that when we try to access it it's non-existent.
        // For this reason, we can use the getAttributeDefault function which returns
        // a default value passed to it for a given attribute in case that the attribute
        // does not exist.
        $vNoteKeywords = $this->note->getAttributeDefault('CATEGORIES', null);

        // If there are actually any keywords that we can obtain from the vNote
        // property 'CATEGORIES', then we take each of the keywords from this property
        // and set it as a key ponting to the value of 'true' in a map, which in turn
        // represents the JMAP property for keywords.
        if (AdapterUtil::isSetAndNotNull($vNoteKeywords) && !empty($vNoteKeywords)) {
            // $vNoteKeywords is a string and we should turn it into an array accordingly
            // If there are multiple values, then explode it via ','
            // Otherwise, take the single value in it and turn it into an array
            if (strpos($vNoteKeywords, ',') !== false) {
                $vNoteKeywords = explode(',', $vNoteKeywords);
            } else {
                $vNoteKeywords = array($vNoteKeywords);
            }

            foreach ($vNoteKeywords as $vNoteKeyword) {
                $jmapKeywords[$vNoteKeyword] = true;
            }
        }

        if (count($jmapKeywords) === 0) {
            return null;
        }

        return $jmapKeywords;
    }

    public function getCreated()
    {
        $vNoteCreated = $this->note->getAttribute('DCREATED');

        if (AdapterUtil::isSetAndNotNull($vNoteCreated) && !empty($vNoteCreated)) {
            $inputDateFormat = 'Ymd\THis\Z';
            $outputDateFormat = 'Y-m-d\TH:i:s\Z';
            $jmapCreated = AdapterUtil::parseDateTime($vNoteCreated, $inputDateFormat, $outputDateFormat);

            return $jmapCreated;
        }

        return null;
    }

    public function getUpdated()
    {
        // Just like CATEGORIES, LAST-MODIFIED is not always present in a vNote and
        // that's why we resort to the getAttributeDefault function here as well
        $vNoteUpdated = $this->note->getAttributeDefault('LAST-MODIFIED', null);

        if (AdapterUtil::isSetAndNotNull($vNoteUpdated) && !empty($vNoteUpdated)) {
            $inputDateFormat = 'Ymd\THis\Z';
            $outputDateFormat = 'Y-m-d\TH:i:s\Z';

            // Since LAST-MODIFIED is usually returned as a timestamp from Horde, we need to turn it
            // into a date string with the date() function from PHP
            $vNoteUpdated = date($inputDateFormat, $vNoteUpdated);
            $jmapUpdated = AdapterUtil::parseDateTime($vNoteUpdated, $inputDateFormat, $outputDateFormat);

            return $jmapUpdated;
        }

        return null;
    }
}
