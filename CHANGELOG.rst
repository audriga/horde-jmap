===============================
JMAP Horde Plugin Release Notes
===============================

.. contents:: Topics

v1.2.1
=======

Release Summary
---------------
Fix byDay recurrence rule ( #5777 )

Details
-------
* Calendars: Fix byDay recurrence rule ( #5777 )

v1.2.0
=======

Release Summary
---------------
Next generation logging and configuration.

Details
-------
* Move log initialization to OXP
* Next-generation config file with defaults if nothing configured (no need to change config)
* Calendars: Use own mirror of icalendar library ( #5716 )
* Contacts: Skip empty Contact Groups ( #5764 )

v1.1.2
=======

Release Summary
---------------
Fixes various issues

Details
-------
* Contacts: Catch and log exception for Contact/get ( #5671 )
* Contacts: Catch and log exception for ContactGroup/get ( #5672 )
* Calendars: Improve attendee conversion and support non-mailto URLs ( #5675 )
* Calendars: Fallback to DTSTAMP for update if no LAST-MODIFIED ( #5682 )
* Sieve: Do not return Sieve Scripts for empty users ( #5676 )

v1.1.1
=======

Release Summary
---------------
Fixes a regression of v1.1.0 and handles more errors

Details
-------
* Only filter contacts which throw error (regression introduced in 1.1.0 )
* Also handle shutdown errors via ErrorHandler

v1.1.0
=======

Release Summary
---------------
SieveScript/get support for and minor fix for Horde

Details
-------
* Depend on OXP version 1
* Sieve: Read/Download Sieve Scripts
* Contacts: Skip books where ID is similar to username
* Calendars: Some delEx's were objects instead of arrays  #5628

v0.12.3
=======

Release Summary
---------------
Hotfix release

Details
-------
* Calendars: Fix delexes to be objects #5628
* Calendars: Handle all escape chars #5716

v0.11.0
=======

Release Summary
---------------
Various fixes and logging improvements

Details
-------
* Calendar: Support multiple participants #5476
* Contact: Do not leak shared contacts #5492

v0.10.0
=======

Release Summary
---------------
Adds logging

Details
-------
* Use new PSR-3 file logger #5441

v0.8.0
======

Release Summary
---------------
Adds Identity

Details
-------
* Calendar: Fix duplicate recurrenceOverride entries #5420
* Settings: Add Identity #5315

v0.7.0
======

Release Summary
---------------
Adds ContactGroup and finishing touches for folders. Also a lot of fixes.

Details
-------
* Calendar: Fix modified exceptions, add folderId to events
* Tasks: Add folderId + remaining properties (#5394)
* Contacts: Add ContactGroup

v0.6.0
======

Release Summary
---------------
Adds contact features to horde

Details
-------
* Fix export of IM address in HordeContactAdapter.php
* Add Addressbook

v0.5.0
======

Release Summary
---------------
Adds more contact/calendar features and uses a single folder everywhere

Details
-------
* Add more contact properties
* Add more calendar properties
* Add calendar folder
* Add task folder

v0.4.0
======

Release Summary
---------------
Allow debug output in API.

Details
-------
* Print debug logs via API (to debug Error 500)
