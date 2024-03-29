# Horde JMAP
⚠️  This version is still in its early stages. This API exposes user data. It is not recommended to expose this API to untrusted networks for now. Please consider contacting us before using this in production.

The JMAP Plugin for Horde provides [JMAP](https://jmap.io/) support for Horde-based systems by exposing a RESTful API Endpoint which speaks the JMAP Protocol.

The following data types are currently supported by the JMAP Plugin for Horde:

* Contacts over the JMAP for Contacts protocol
* Calendars over the JMAP for Calendars protocol, built on top of the [JSCalendar](https://tools.ietf.org/html/draft-ietf-calext-jscalendar-32) format
* Tasks over the JMAP for Tasks protocol, built on top of the [JSCalendar](https://tools.ietf.org/html/draft-ietf-calext-jscalendar-32) format

## Installation
1. Run `make` to initialize the project for the default PHP version (8.1). Use other build targets (e.g. `make php56_mode` or `make php70_mode`) instead, in case you are using a different version.
1. (optional) there are build targets that enable logging to graylog instead of a file, e.g. run `make graylog56_mode`
1. Run `make zip` to create a zipped package under `build/`
1. Extract the resulting package the root of your Horde installation (make sure the folder is named `jmap`).
1. 🎉 Partytime! Help fix [some issues](https://github.com/audriga/jmap-horde/issues) and [send us some pull requests](https://github.com/audriga/jmap-horde/pulls) 👍

## Usage
Set up your favorite client to talk to Horde's JMAP API.

## Development
### Installation
1. Run step 1) from above
1. Run `make update` to update depdendencies and make devtools available

### Tests
To run all tests run `make fulltest`. This requires [Podman](https://podman.io/)
(for Static Anaylsis) and [Ansible](https://www.ansible.com/) (for Integration
Tests).

You can also run them separately:

* **Static Analysis** via `make lint`
* **Unit Tests** via `make unit_test`

For debugging purposes it makes sense to throw some cURL calls at the API. For example, this is how you tell the JMAP API to return all CalendarEvents:
```
curl <horde-address>/jmap/jmap.php -u <username>:<password> -d '{"using":["urn:ietf:params:jmap:calendars"],"methodCalls":[["CalendarEvent/get", {"accountId":"<username>"}, "0"]]}'
```
