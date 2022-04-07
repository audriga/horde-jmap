<?php

namespace OpenXPort\Mapper;

class HordeTaskListMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    /* Horde UI has the properties
     * Name: name
     * Color: color
     * System Task List: Not relevant
     * Description: description
     * TODO Sharing
     * TODO isVisible: isVisible
     */
    public function mapToJmap($data, $adapter)
    {
        $list = [];

        foreach ($data as $taskList) {
            $adapter->setTaskList($taskList->toHash());

            $jmapTaskList = new \OpenXPort\Jmap\Task\TaskList();
            $jmapTaskList->setId($adapter->getId());
            $jmapTaskList->setRole($adapter->getRole());
            $jmapTaskList->setName($adapter->getName());
            $jmapTaskList->setDescription($adapter->getDescription());
            $jmapTaskList->setColor($adapter->getColor());

            array_push($list, $jmapTaskList);
        }

        return $list;
    }
}
