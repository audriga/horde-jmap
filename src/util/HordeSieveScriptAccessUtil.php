<?php

namespace OpenXPort\Util;

use Ingo;

class HordeSieveScriptAccessUtil
{
    /**
     * Accesses the Managesieve backend configured in Horde and returns
     * the Sieve script that can be obtained from there if any.
     *
     * @return array|null $script The Sieve script to be returned
     */
    public static function getSieveScript()
    {
        $logger = \OpenXPort\Util\Logger::getInstance();

        global $injector;

        $script = array();

        // Obtain the driver name and the params we need for instantiating the transport driver
        // that talks to the backend Sieve server
        $transportName = reset(Ingo::loadBackends())['transport'][0]['driver'];
        $transportParams = reset(Ingo::loadBackends())['transport'][0]['params'];

        // Obtain the password used by JMAP and use it for authentication against Managesieve as well
        $transportParams['password'] = $_SERVER['PHP_AUTH_PW'];

        // Obtain the username used by JMAP
        $receivedUsername = $_SERVER['PHP_AUTH_USER'];

        // If the username is used for admin auth (i.e., it contains "*"), then set 'username' as the admin username
        // for authentication purposes against Managesieve and set 'euser' (the effective user that we want to read
        // Sieve scripts for) as the user's username that we're acting on behalf of while authenticated in
        // admin auth mode
        if (strpos($receivedUsername, "*") !== false) {
            $splitUsernames = explode("*", $receivedUsername);
            $transportParams['username'] = $splitUsernames[0];
            $transportParams['euser'] = $splitUsernames[1];
        // If no admin auth, then just set 'username' and 'euser' to be the same username
        // and pass them over for authentication to Managesieve
        } else {
            $transportParams['username'] = $transportParams['euser'] = $receivedUsername;
        }

        // Set the transport driver's name together with the other parameters from above in an array that we pass
        // for the actual instantiation of the transport driver
        $transport = array('driver' => $transportName, 'params' => $transportParams);

        try {
            $transportDriver = $injector->getInstance('Ingo_Factory_Transport')->create($transport);
            if (method_exists($transportDriver, 'getScript')) {
                $script = $transportDriver->getScript();
            }
        } catch (\Throwable $th) {
            $logger->error("Could not get Sieve scripts: " . $th->getMessage());
        }

        if (is_null($script) || !isset($script) || empty($script)) {
            return null;
        }

        return $script;
    }
}
