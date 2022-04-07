<?php

namespace OpenXPort\Adapter;

class HordeSieveScriptAdapter extends AbstractAdapter
{
    private $script;

    public function getScript()
    {
        return $this->script;
    }

    public function setScript($script)
    {
        $this->script = $script;
    }

    public function getName()
    {
        $scriptName = $this->script['name'];

        if (isset($scriptName) && !is_null($scriptName) && !empty($scriptName)) {
            return $scriptName;
        }

        return null;
    }
}
