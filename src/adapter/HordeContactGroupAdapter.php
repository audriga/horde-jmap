<?php

namespace OpenXPort\Adapter;

class HordeContactGroupAdapter extends AbstractAdapter
{
    private $group;

    public function getContactGroup()
    {
        return $this->group;
    }

    public function setContactGroup($group)
    {
        $this->group = $group;
    }

    public function getName()
    {
        return $this->group["name"];
    }

    public function getContactIds()
    {
        return $this->group["contactIds"];
    }
}
