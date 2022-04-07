<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\Task\Task;

class HordeTaskMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        require_once __DIR__ . '/../../icalendar/zapcallib.php';

        $list = [];

        foreach ($data as $taskListId => $iCalendarTask) {
            $icalObj = new \ZCiCal($iCalendarTask);

            foreach ($icalObj->tree->child as $node) {
                if ($node->getName() == "VTODO") {
                    $adapter->setICalTask($node);

                    $jt = new Task();
                    $jt->setType("jstask");
                    $jt->setStart($adapter->getDTStart());
                    $jt->setTitle($adapter->getSummary());
                    $jt->setDescription($adapter->getDescription());
                    $jt->setDue($adapter->getDue());
                    $jt->setProgress($adapter->getStatus());
                    $jt->setKeywords($adapter->getCategories());
                    $jt->setCreated($adapter->getCreated());
                    $jt->setUpdated($adapter->getLastModified());
                    $jt->setUid($adapter->getUid());
                    $jt->setTaskListId($taskListId);
                    $jt->setRelatedTo($adapter->getRelatedTo());
                    $jt->setReplyTo($adapter->getReplyTo());
                    $jt->setPriority($adapter->getPriority());
                    $jt->setAlerts($adapter->getAlerts());
                    $jt->setProgressUpdated($adapter->getProgressUpdated());
                    $jt->setPrivacy($adapter->getPrivacy());
                    $jt->setEstimatedDuration($adapter->getEstimatedDuration());

                    array_push($list, $jt);
                }
            }
        }

        return $list;
    }
}
