<?php

// Define version
$oxpVersion = '1.2.1';

// Print debug output via API on error
// NOTE: Do not use on public-facing setups
// Shutdown function must be registered before the one coming from horde
$handler = new \OpenXPort\Jmap\Core\ErrorHandler();
$handler->setHandlers();

if (file_exists(__DIR__ . '/config/config.default.php')) {
    // Build config
    $configDefault = include(__DIR__ . '/config/config.default.php');
    $configFile = __DIR__ . '/config/config.php';
    $oxpConfig = $configDefault;

    if (file_exists($configFile)) {
        $configUser = include($configFile);
        if (is_array($configUser)) {
            $oxpConfig = array_merge($configDefault, $configUser);
        }
    };
} else {
    // Fall back to legacy config
    require_once('config/config.php');

    $oxpConfig = [];

    $oxpConfig['adminUsers'] = Horde\Config::ADMIN_USERS;
    $oxpConfig['capabilities'] = array('calendars', 'contacts', 'mail', 'tasks', 'notes', 'sieve');
    $oxpConfig['allowFileLog'] = Horde\Config::ALLOW_FILE_LOG;
    $oxpConfig['logLevel'] = Horde\Config::LOG_LEVEL;
    $oxpConfig['fileLogPath'] = Horde\Config::LOG_PATH;
}

// Initialize File-based logging
OpenXPort\Util\Logger::init($oxpConfig, $jmapRequest);
$logger = \OpenXPort\Util\Logger::getInstance();

// Require mailer file
require_once('bridge.php');

// Exception handler must be set after the one from Horde
// TODO Split setHandlers into shutdown and exception handlers
$handler->setHandlers();

$logger->notice("Running PHP v" . phpversion() . ", Horde v" . $registry->getVersion('horde') . ", Plugin v" . $oxpVersion);

// Get user login credentials from Basic Aut
$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

// Try to get user and password from env if basic auth is empty
// This is required for cPanel
if (!isset($user) || $user == '') {
    $user = $_ENV['REMOTE_USER'];
    $pass = $_ENV['REMOTE_PASSWORD'];
}

// Handle admin auth if any
// If user contains * explode it
$users = [];

if (mb_strpos($user, '*')) {
    $users = explode("*", $user);

    // Use first part for login
    $user = $users[0];
}

/* START of auth code mostly taken from login.php */
$auth_params = array(
    'password' => $pass
);

try {
    $result = $auth->getLoginParams();

    foreach (array_keys($result['params']) as $val) {
        $auth_params[$val] = Horde_Util::getPost($val);
    }
} catch (Horde_Exception $e) {
}

$isAuthSuccessful = $auth->authenticate($user, $auth_params);

if (!$isAuthSuccessful) {
    http_response_code(401);

    if (!($logout_reason = $auth->getError())) {
        $logout_reason = $vars->logout_reason;
    }
    if ($logout_reason) {
        $reason = null;

        switch ($logout_reason) {
            case Horde_Auth::REASON_SESSION:
                $reason = _("Your session has expired. Please login again.");
                break;

            case Horde_Core_Auth_Application::REASON_SESSIONIP:
                $reason = _("Your Internet Address has changed since the beginning of your session. To protect your security, you must login again.");
                break;

            case Horde_Core_Auth_Application::REASON_BROWSER:
                $reason = _("Your browser appears to have changed since the beginning of your session. To protect your security, you must login again.");
                break;

            case Horde_Core_Auth_Application::REASON_SESSIONMAXTIME:
                $reason = _("Your session length has exceeded the maximum amount of time allowed. Please login again.");
                break;

            case Horde_Auth::REASON_LOGOUT:
                $reason = _("You have been logged out.");
                break;

            case Horde_Auth::REASON_FAILED:
                $reason = _("Login failed.");
                break;

            case Horde_Auth::REASON_BADLOGIN:
                $reason = _("Login failed because your username or password was entered incorrectly.");
                break;

            case Horde_Auth::REASON_EXPIRED:
                $reason = _("Your login has expired.");
                break;

            case Horde_Auth::REASON_LOCKED:
            case Horde_Auth::REASON_MESSAGE:
                if (!($reason = $auth->getError(true))) {
                    $reason = $vars->logout_msg;
                }
                break;
        }
    }

    $logger->warning(
        "Auth unsuccessful." .
        "\n  User: $user" .
        "\n  Pass: $pass" .
        "\n  isAuthenticated: " . print_r($is_auth, true) .
        "\n  Remote Address: " . $_SERVER['REMOTE_ADDR'] .
        "\n  Reason: " . $reason
    );

    die("401 Unauthorized");
}
/* END of auth code mostly taken from login.php */

// Admin auth: If admin authed successfully, use second part as well
// The second part of $users contains the username of the user that
// we want to read data for
// That's why if $users[1] is present, we set it as the username of
// the user to read data for via admin auth
if (isset($users[1]) && !is_null($users[1]) && !empty($users[1])) {
    if (!in_array($users[0], $oxpConfig["adminUsers"])) {
        http_response_code(403);
        die('403 Forbidden');
    }
    $registry->setAuth($users[1], array());
} else {
    $registry->setAuth($user, array());
}
