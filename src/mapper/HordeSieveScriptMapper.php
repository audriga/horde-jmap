<?php

namespace OpenXPort\Mapper;

use OpenXPort\Jmap\SieveScript\SieveScript;
use OpenXPort\Util\AdapterUtil;

class HordeSieveScriptMapper extends AbstractMapper
{
    public function mapFromJmap($jmapData, $adapter)
    {
        // TODO: Implement me
    }

    public function mapToJmap($data, $adapter)
    {
        $list = [];

        // Since we receive only one Sieve script from Horde, the $data variable
        // contains only one associative array which represents said script
        // and which we pass to our adapter below.
        $adapter->setScript($data);

        // Since the script we obtain from Horde is always the one
        // which is set as active on the Sieve server, we set "isActive"
        // of the JMAP Sieve script object to be always true.
        // Note: we currently don't set any value for the "id" property
        // of the JMAP Sieve script object, since there's no ID
        // for Sieve scripts in Horde.
        $jmapSieveScript = new SieveScript();
        $jmapSieveScript->setIsActive(true);
        $jmapSieveScript->setName($adapter->getName());
        // Since we need to set "blobId" of the JMAP Sieve script object
        // to some value and since we only have one Sieve script object
        // anyways, we can choose a random value for "blobId"
        $jmapSieveScript->setBlobId("jmap-sieve-blobid");

        // Check if we have actually received anything from the ManageSieve server (can be detected by the script name)
        // Only if we have received something, should we add it to the response list of our JMAP response
        $jmapSieveScriptName = $jmapSieveScript->getName();

        if (AdapterUtil::isSetAndNotNull($jmapSieveScriptName) && !empty($jmapSieveScriptName)) {
            array_push($list, $jmapSieveScript);
        }

        return $list;
    }
}
