# Horde JMAP
The JMAP Plugin for Horde provides [JMAP](https://jmap.io/) support for Horde-based systems by exposing a RESTful API Endpoint which speaks the JMAP Protocol.

Please note that this version is still in its early stages.

The following data types are currently supported by the JMAP Plugin for Horde:

* Contacts over the JMAP for Contacts protocol
* Calendars over the JMAP for Calendars protocol, built on top of the [JSCalendar](https://tools.ietf.org/html/draft-ietf-calext-jscalendar-32) format
* Tasks over the JMAP for Tasks protocol, built on top of the [JSCalendar](https://tools.ietf.org/html/draft-ietf-calext-jscalendar-32) format

## Installation
1. ☁ Clone this plugin inside the root of your Horde installation : `git clone https://github.com/audriga/jmap-horde jmap` (Make sure the folder is name `jmap`)
1. 💻 In the folder of the plugin, run `composer install --prefer-dist --no-dev`
1. 🎉 Partytime! Help fix [some issues](https://github.com/audriga/jmap-horde/issues) and [send us some pull requests](https://github.com/audriga/jmap-horde/pulls) 👍

## Usage
Set up your favorite client to talk to Horde's JMAP API.

## Development
### Installation
1. Leave out `--no-dev` and run `composer install --prefer-dist` instead

### Tests
Run PHP CodeSniffer via
```
$ phpcs .
```

For debugging purposes it makes sense to throw some cURL calls at the API. For example, this is how you tell the JMAP API to return all CalendarEvents:
```
curl <horde-address>/jmap/jmap.php -u <username>:<password> -d '{"using":["urn:ietf:params:jmap:calendars"],"methodCalls":[["CalendarEvent/get", {"accountId":"<username>"}, "0"]]}'
```
