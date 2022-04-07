<?php

namespace OpenXPort\Jmap\Core;

use OpenXPort\Util\HordeSieveScriptAccessUtil;

class HordeSieveScriptBlobAccess extends BlobAccess
{
    private $logger;

    public function __construct()
    {
        $this->logger = \OpenXPort\Util\Logger::getInstance();
    }

    public function downloadBlob($path = null)
    {
        global $registry;

        // Obtain the Sieve script from the Managesieve backend
        $script = HordeSieveScriptAccessUtil::getSieveScript();

        if (isset($script['script']) && !is_null($script['script']) && !empty($script['script'])) {
            // Inject vacation notice start and end dates only if the Sieve comment "# Vacation"
            // is in fact present in the script itself
            // See more info here: https://web.audriga.com/mantis/view.php?id=5521#27053
            if (strpos($script['script'], '# Vacation') !== false) {
                // Obtain vacation notice data from Horde in order to inject the vacation rule
                // start and end dates into the Sieve script below
                $vacationData = $registry->call('filter/getVacation', array());

                if (
                    isset($vacationData['start']) && !is_null($vacationData['start'])
                    && isset($vacationData['end']) && !is_null($vacationData['end'])
                ) {
                    // In case either one of start or end date is equal to zero, log a warning about it
                    if (strcmp($vacationData['start'], "0") === 0 || strcmp($vacationData['end'], "0") === 0) {
                        $this->logger->warning("Vacation notice start date or end date is equal to zero");
                    }
                    $vacationStartAndEndString = " - Horde: start="
                        . $vacationData['start']
                        . " / end="
                        . $vacationData['end'];

                    $script['script'] = substr(
                        $script['script'],
                        0,
                        strpos($script['script'], '# Vacation') + strlen('# Vacation')
                    )
                        . $vacationStartAndEndString
                        . substr($script['script'], strpos($script['script'], '# Vacation') + strlen('# Vacation'));
                } else {
                    $this->logger->warning("Could not inject vacation start and end dates into Sieve script");
                }
            }
            echo $script['script'];
        } else {
            $this->logger->error("The Sieve script was unset, null or empty");
        }
    }

    public function uploadBlob($accountId, $path = null)
    {
        //TODO implement
    }
}
