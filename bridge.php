<?php
/* Based on index.php (Horde 5.2.22) */

/* Add anchor to outgoing URL. */
// OpenXPort: Remove, since the anchor does not seem to be needed

/* Try to login - if we are doing auth to an app, we need to auth to
 * Horde first or else we will lose the session. Ignore any auth errors.
 * Transparent authentication is handled by the Horde_Application::
 * constructor. */
// OpenXPort: Change require_once to go one directory up and the into 'lib' (since we're one directory deeper compared to login.php which we're copying this require from)
require_once __DIR__ . '/../lib/Application.php';
try {
    Horde_Registry::appInit('horde', array(
        'authentication' => 'none',
        'nologintasks' => true
    ));
} catch (Horde_Exception_AuthenticationFailure $e) {}

$is_auth = $registry->isAuthenticated();
$vars = $injector->getInstance('Horde_Variables');

/* This ensures index.php doesn't pick up the 'url' parameter. */
// OpenXPort: Remove redirect code

/* Get an Auth object. */
$auth = $injector->getInstance('Horde_Core_Factory_Auth')->create(($is_auth && $vars->app) ? $vars->app : null);

/* Get URL/Anchor strings now. */
// OpenXPort: Remove, since the anchor does not seem to be needed

// OpenXPort: Moved Logging to jmap.php

/* Change language. */
// OpenXPort: Remove UI code

// OpenXPort: Remove UI code

/* Build the list of necessary login parameters.
 * Need to wait until after we set language to get login parameters. */
// OpenXPort: Remove UI code

/* If we currently are authenticated, and are not trying to authenticate to
 * an application, redirect to initial page. This is done in index.php.
 * If we are trying to authenticate to an application, but don't have to,
 * redirect to the requesting URL. */
// OpenXPort: Remove redirect code

/* Redirect the user if an alternate login page has been specified. */
// // OpenXPort: Remove redirect code

/* Build the <select> widget containing the available languages. */
// OpenXPort: Remove UI code

// OpenXPort: Moved Logging to jmap.php

// OpenXPort: Remove UI code
