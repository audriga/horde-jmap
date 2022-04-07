<?php

// Use composer autoload
require_once('vendor/autoload.php');

// Decode JSON post body here in case the debug capability is included
$jmapRequest = OpenXPort\Util\HttpUtil::getRequestBody();

// Include the common code shared between download.php and jmap.php
require_once('common.php');

$accessors = array(
    "AddressBooks" => new OpenXPort\DataAccess\HordeAddressBookDataAccess(),
    "Contacts" => new OpenXPort\DataAccess\HordeContactDataAccess(),
    "ContactGroups" => new OpenXPort\DataAccess\HordeContactGroupDataAccess(),
    "Calendars" => new OpenXPort\DataAccess\HordeCalendarDataAccess(),
    "CalendarEvents" => new OpenXPort\DataAccess\HordeCalendarEventDataAccess(),
    "TaskLists" => new OpenXPort\DataAccess\HordeTaskListDataAccess(),
    "Tasks" => new OpenXPort\DataAccess\HordeTaskDataAccess(),
    "Notes" => new OpenXPort\DataAccess\HordeNoteDataAccess(),
    "Notebooks" => new OpenXPort\DataAccess\HordeNotebookDataAccess(),
    "Identities" => new OpenXPort\DataAccess\HordeIdentityDataAccess(),
    "Filters" => null,
    "StorageNodes" => null,
    "SieveScripts" => new OpenXPort\DataAccess\HordeSieveScriptDataAccess()
);

$adapters = array(
    "AddressBooks" => new OpenXPort\Adapter\HordeAddressBookAdapter(),
    "Contacts" => new OpenXPort\Adapter\HordeContactAdapter(),
    "ContactGroups" => new OpenXPort\Adapter\HordeContactGroupAdapter(),
    "Calendars" => new OpenXPort\Adapter\HordeCalendarAdapter(),
    "CalendarEvents" => new OpenXPort\Adapter\HordeCalendarEventAdapter(),
    "TaskLists" => new OpenXPort\Adapter\HordeTaskListAdapter(),
    "Tasks" => new OpenXPort\Adapter\HordeTaskAdapter(),
    "Notes" => new OpenXPort\Adapter\HordeNoteAdapter(),
    "Notebooks" => new OpenXPort\Adapter\HordeNotebookAdapter(),
    "Identities" => new OpenXPort\Adapter\HordeIdentityAdapter(),
    "Filters" => null,
    "StorageNodes" => null,
    "SieveScripts" => new OpenXPort\Adapter\HordeSieveScriptAdapter()
);

$mappers = array(
    "AddressBooks" => new OpenXPort\Mapper\HordeAddressBookMapper(),
    "Contacts" => new OpenXPort\Mapper\HordeContactMapper(),
    "ContactGroups" => new OpenXPort\Mapper\HordeContactGroupMapper(),
    "Calendars" => new OpenXPort\Mapper\HordeCalendarMapper(),
    "CalendarEvents" => new OpenXPort\Mapper\HordeCalendarEventMapper(),
    "TaskLists" => new OpenXPort\Mapper\HordeTaskListMapper(),
    "Tasks" => new OpenXPort\Mapper\HordeTaskMapper(),
    "Notes" => new OpenXPort\Mapper\HordeNoteMapper(),
    "Notebooks" => new OpenXPort\Mapper\HordeNotebookMapper(),
    "Identities" => new OpenXPort\Mapper\HordeIdentityMapper(),
    "Filters" => null,
    "StorageNodes" => null,
    "SieveScripts" => new OpenXPort\Mapper\HordeSieveScriptMapper()
);

$server = new OpenXPort\Jmap\Core\Server($accessors, $adapters, $mappers, $oxpConfig);
$server->handleJmapRequest($jmapRequest);
