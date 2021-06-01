<?php

// OpenXPort: Use composer autoload
require_once('vendor/autoload.php');

require_once __DIR__ . '/../lib/core.php';
$registry = new Horde_Registry();

$is_auth = $registry->isAuthenticated();
$vars = $injector->getInstance('Horde_Variables');

/* Get an Auth object. */
$auth = $injector->getInstance('Horde_Core_Factory_Auth')->create(($is_auth && $vars->app) ? $vars->app : null);

// OpenXPort: Get user login credentials from POST
$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];
$users = [];

$auth_params = array(
    'password' => $pass
);

// Handle admin auth if any
// If user contains * explode it
if (mb_strpos($user, '*')) {
    $users = explode("*", $user);

    // Use first part for login
    $user = $users[0];
}

// OpenXPort: Try to authenticate
$isAuthSuccessful = $auth->authenticate($user, $auth_params);
if (!$isAuthSuccessful) {
    http_response_code(401);
    die("401 Unauthorized");
}

// TODO Add
// Admin auth: If admin authed successfully, use second part for SQMail
$registry->setAuth($user, array());

$accessors = array(
    "Contacts" => new \OpenXPort\DataAccess\HordeContactDataAccess(),
    "Calendars" => new \OpenXPort\DataAccess\HordeCalendarEventDataAccess(),
    "Tasks" => new \OpenXPort\DataAccess\HordeTaskDataAccess(),
    "Notes" => null,
    "Settings" => null,
    "Filters" => null,
    "Files" => null
);

$adapters = array(
    "Contacts" => new HordeContactAdapter(),
    "Calendars" => new HordeCalendarEventAdapter(),
    "Tasks" => new \OpenXPort\Adapter\HordeTaskAdapter(),
    "Notes" => null,
    "Settings" => null,
    "Filters" => null,
    "Files" => null
);

$mappers = array(
    "Contacts" => new \OpenXPort\Mapper\HordeContactMapper(),
    "Calendars" => new HordeCalendarEventMapper(),
    "Tasks" => new \OpenXPort\Mapper\HordeTaskMapper(),
    "Notes" => null,
    "Settings" => null,
    "Filters" => null,
    "Files" => null
);

$server = new \Jmap\Core\Server($accessors, $adapters, $mappers);
$server->listen();
