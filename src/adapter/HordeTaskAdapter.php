<?php

namespace OpenXPort\Adapter;

use OpenXPort\Adapter\AbstractAdapter;

class HordeTaskAdapter extends AbstractAdapter {

    private $iCalTask;

    public function getICalTask() {
        return $this->iCalTask;
    }

    public function setICalTask($iCalTask) {
        $this->iCalTask = $iCalTask;
    }

    public function getDTStart() {
        $dtStart = $this->iCalTask->data["DTSTART"];
        
        if (is_null($dtStart) || !isset($dtStart)) {
            return null;
        }

        $date = \DateTime::createFromFormat("Ymd\THis\Z", $dtStart->getValues());
        $jmapStart = date_format($date, "Y-m-d\TH:i:s");
        return $jmapStart;
    }


    public function getSummary() {
        $summary = $this->iCalTask->data["SUMMARY"];

        if (is_null($summary) || !isset($summary)) {
            return null;
        }

        return $summary->getValues();
    }

    public function getDescription() {
        $description = $this->iCalTask->data["DESCRIPTION"];

        if (is_null($description)) {
            return NULL;
        }

        return $description->getValues();
    }

    public function getDue() {
        $due = $this->iCalTask->data["DUE"];
        
        if (is_null($due) || !isset($due)) {
            return null;
        }

        $dueDate = \DateTime::createFromFormat("Ymd\THis\Z", $due->getValues());
        $jmapDue = date_format($dueDate, "Y-m-d\TH:i:s");
        return $jmapDue;
    }

    public function getStatus() {
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

    public function getCategories() {
        $categories = $this->iCalTask->data['CATEGORIES'];

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

    public function getCreated() {
        $created = $this->iCalTask->data['CREATED'];

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
        $lastModified = $this->iCalTask->data['LAST-MODIFIED'];

        if (is_null($lastModified)) {
            return NULL;
        }

        $iCalFormat = 'Ymd\THis\Z';
        $jmapFormat = 'Y-m-d\TH:i:s\Z';

        $dateLastModified = \DateTime::createFromFormat($iCalFormat, $lastModified->getValues());
        $jmapLastModified = date_format($dateLastModified, $jmapFormat);

        return $jmapLastModified;
    }

}