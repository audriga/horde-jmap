<?php

namespace OpenXPort\Adapter;

class HordeTaskListAdapter extends AbstractAdapter
{
    /**
     * A hash of tasklist properties.
     *
     * @var array
     * **/
    private $taskList;

    public function getTaskList()
    {
        return $this->taskList;
    }

    public function setTaskList($taskList)
    {
        $this->taskList = $taskList;
    }

    public function getId()
    {
        return $this->taskList["name"];
    }

    public function getName()
    {
        return $this->taskList["attributes"]["name"];
    }

    public function getDescription()
    {
        return $this->taskList["attributes"]["desc"];
    }

    public function getColor()
    {
        return $this->taskList["attributes"]["color"];
    }

    public function getIsVisible()
    {
        // TODO unclear how to get this from Horde
    }

    public function getRole()
    {
        global $registry;

        return $registry->call('tasks/getDefaultShare') == $this->taskList["name"] ? "inbox" : null;
    }
}
