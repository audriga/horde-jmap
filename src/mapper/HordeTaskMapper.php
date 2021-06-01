<?php

namespace OpenXPort\Mapper;

use OpenXPort\Mapper\AbstractMapper;
use Jmap\Task\Task;

class HordeTaskMapper extends AbstractMapper {

    public function mapFromJmap($jmapData, $adapter) {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter) {
        $list = [];

        foreach ($data as $t) {
            $icalObj = new \ZCiCal($t);

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

                    array_push($list, $jt);
                }
            }
        }

        return $list;
    }

}