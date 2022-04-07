<?php

use OpenXPort\Jmap\Core\BlobManagement;

require_once('vendor/autoload.php');

// Include the common code shared between download.php and jmap.php
require_once('common.php');

// Parse URL vars
$url_vars = array();
parse_str($_SERVER['QUERY_STRING'], $url_vars);

set_time_limit(0);

// Since blob access classes always need a root path be passed to them in
// their constructor, we create one here that we pass. Since in the case of
// Horde's blob access class we don't need an actual root path, we just set
// the root path in this case to be null.
$rootPath = null;

// Initialize our blob access classes that we pass to our BlobManagement instance below
$blobAccessors = array(
    "SieveScripts" => new OpenXPort\Jmap\Core\HordeSieveScriptBlobAccess($rootPath),
    "Generic" => new OpenXPort\Jmap\Core\HordeSieveScriptBlobAccess($rootPath)
);

// Initialize a new instance of BlobManagement which we use for file download
// and call its downloadBlob() method
$blobManagement = new BlobManagement($blobAccessors);
$blobManagement->downloadBlob($url_vars['accountId'], $url_vars['name'], $url_vars['blobId'], $url_vars['accept']);
