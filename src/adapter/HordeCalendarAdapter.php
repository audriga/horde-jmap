<?php

namespace OpenXPort\Adapter;

class HordeCalendarAdapter extends AbstractAdapter
{
    private $calendar;
    private $id;

    public function getCalendar()
    {
        return $this->calendar;
    }

    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }

    public function getId()
    {
        $this->id = $this->calendar->share()->getName();

        return $this->id;
    }

    public function getName()
    {
        return $this->calendar->name();
    }

    public function getDescription()
    {
        return $this->calendar->description();
    }

    public function getColor()
    {
        return $this->calendar->background();
    }

    public function getIsVisible()
    {
        return $this->calendar->toHash()["show"];
    }

    public function getRole()
    {
        global $registry;

        if (!$this->id) {
            $this->getId();
        }

        return $registry->call('calendar/getDefaultShare') == $this->id ? "inbox" : null;
    }
}
