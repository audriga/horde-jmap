<?php

namespace OpenXPort\Adapter;

use DateTime;
use OpenXPort\Jmap\Calendar\Alert;
use OpenXPort\Jmap\Calendar\OffsetTrigger;
use OpenXPort\Adapter\AbstractAdapter;

class HordeTaskAdapter extends AbstractAdapter
{
    private $iCalTask;

    public function getICalTask()
    {
        return $this->iCalTask;
    }

    public function setICalTask($iCalTask)
    {
        $this->iCalTask = $iCalTask;
    }

    public function getDTStart()
    {
        $dtStart = $this->iCalTask->data["DTSTART"];

        if (is_null($dtStart) || !isset($dtStart)) {
            return null;
        }

        $date = \DateTime::createFromFormat("Ymd\THis\Z", $dtStart->getValues());
        $jmapStart = date_format($date, "Y-m-d\TH:i:s");
        return $jmapStart;
    }


    public function getSummary()
    {
        $summary = $this->iCalTask->data["SUMMARY"];

        if (is_null($summary) || !isset($summary)) {
            return null;
        }

        return $summary->getValues();
    }

    public function getDescription()
    {
        $description = $this->iCalTask->data["DESCRIPTION"];

        if (is_null($description)) {
            return null;
        }

        return $description->getValues();
    }

    public function getDue()
    {
        $due = $this->iCalTask->data["DUE"];

        if (is_null($due) || !isset($due)) {
            return null;
        }

        $dueDate = \DateTime::createFromFormat("Ymd\THis\Z", $due->getValues());
        $jmapDue = date_format($dueDate, "Y-m-d\TH:i:s");
        return $jmapDue;
    }

    public function getStatus()
    {
        $status = $this->iCalTask->data['STATUS'];

        if (is_null($status)) {
            return null;
        }

        switch ($status->getValues()) {
            case 'NEEDS-ACTION':
                return "needs-action";
                break;

            case 'COMPLETED':
                return "completed";
                break;

            case 'IN-PROCESS':
                return "in-process";
                break;

            case 'CANCELLED':
                return "cancelled";
                break;

            default:
                return null;
                break;
        }
    }

    public function getCategories()
    {
        $categories = $this->iCalTask->data['CATEGORIES'];

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

    public function getCreated()
    {
        $created = $this->iCalTask->data['CREATED'];

        if (is_null($created)) {
            return null;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateCreated = \DateTime::createFromFormat($iCalFormat, $created->getValues());
        $jmapCreated = date_format($dateCreated, $jmapFormat);

        return $jmapCreated;
    }

    public function getLastModified()
    {
        $lastModified = $this->iCalTask->data['LAST-MODIFIED'];

        if (is_null($lastModified)) {
            return null;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateLastModified = \DateTime::createFromFormat($iCalFormat, $lastModified->getValues());
        $jmapLastModified = date_format($dateLastModified, $jmapFormat);

        return $jmapLastModified;
    }

    public function getUid()
    {
        $uid = $this->iCalTask->data['UID'];

        if (is_null($uid)) {
            return null;
        }

        return $uid->getValues();
    }

    public function getRelatedTo()
    {
        $relatedTo = $this->iCalTask->data['RELATED-TO'];

        if (is_null($relatedTo)) {
            return null;
        }

        $relatedToValue = $relatedTo->getValues();

        return array(
            $relatedToValue => array(
                "@type" => "Relation",
                "relation" => array(
                    "parent" => true
                )
            )
        );
    }

    public function getReplyTo()
    {
        $organizer = $this->iCalTask->data['ORGANIZER'];

        if (is_null($organizer)) {
            return null;
        }

        $organizerValue = $organizer->getValues();

        // If ORGANIZER does not contain a 'mailto:' string, indicating an iMIP URL scheme, set the replyTo prop to be
        // of type 'other'
        // Otherwise, we set it to be of type 'imip'
        if (!strpos($organizerValue, "mailto")) {
            return array(
                "other" => $organizerValue
            );
        }

        return array(
            "imip" => $organizerValue
        );
    }

    public function getPriority()
    {
        $priority = $this->iCalTask->data['PRIORITY'];

        // Return 0 as default value
        // See https://datatracker.ietf.org/doc/html/draft-ietf-calext-jscalendar-32#section-4.4.1
        if (is_null($priority)) {
            return 0;
        }

        // Cast to int, since priority is an int value in JMAP
        // See https://datatracker.ietf.org/doc/html/draft-ietf-calext-jscalendar-32#section-4.4.1
        return (int) $priority->getValues();
    }

    public function getAlerts()
    {
        if (sizeof($this->iCalTask->child) == 0) {
            return null;
        }

        $jmapAlerts = [];

        foreach ($this->iCalTask->child as $childNode) {
            if ($childNode->getName() == 'VALARM') {
                $trigger = new OffsetTrigger();
                $trigger->setType("OffsetTrigger");
                $trigger->setOffset($childNode->data["TRIGGER"]->getValues());

                $alert = new Alert();
                // TODO current default
                $alert->setAction("display");
                $alert->setTrigger($trigger);

                // Create an ID as a key in the array via base64 (it should be some random string; I'm picking
                // base64 as a random option)
                $key = \base64_encode($childNode->data["TRIGGER"]->getValues());
                $jmapAlerts["$key"] = $alert;
            }
        }

        if (sizeof($jmapAlerts) == 0) {
            return null;
        }

        return $jmapAlerts;
    }

    public function getProgressUpdated()
    {
        $completed = $this->iCalTask->data['COMPLETED'];

        if (is_null($completed)) {
            return null;
        }

        $hordeFormat = "Ymd\THis\Z";
        $jmapFormat = "Y-m-d\TH:i:s\Z";

        $completedDate = DateTime::createFromFormat($hordeFormat, $completed->getValues());
        return date_format($completedDate, $jmapFormat);
    }

    public function getPrivacy()
    {
        $class = $this->iCalTask->data['CLASS'];

        if (is_null($class)) {
            return null;
        }

        $classValue = $class->getValues();

        switch ($classValue) {
            case 'CONFIDENTIAL':
                return 'secret';
                break;

            case 'PRIVATE':
                return 'private';
                break;

            case 'PUBLIC':
                return 'public';
                break;

            default:
                return $classValue;
                break;
        }
    }

    // TODO https://web.audriga.com/mantis/view.php?id=5394#c25485
    public function getEstimatedDuration()
    {
        $estimatedDuration = $this->iCalTask->data['X-HORDE-ESTIMATE'];

        if (is_null($estimatedDuration)) {
            return null;
        }

        //return $estimatedDuration->getValues();
        return null;
    }
}
