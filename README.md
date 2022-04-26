# Antivirus_remote
A lightweight scanning engine for Moodle, that pushes files to a remote scanning agent for scanning. This avoids the issues of having a single plugin tied to a particular virus engine, and abstracts the implementation of the scanning and return to a remote server.

- [Antivirus_remote](#antivirus_remote)
  - [Branches](#branches)
  - [Installation](#installation)
  - [Scanner engine](#scanner-engine)
    - [Response API](#response-api)
    - [Example engine](#example-engine)
  - [Support](#support)

## Branches

All installations should use the MOODLE_33_STABLE branch.

| Moodle version    | Branch           | PHP  |
|-------------------|------------------|------|
| Moodle 3.3+       | MOODLE_33_STABLE | 7.1+ |
| Totara 12+        | MOODLE_33_STABLE | 7.1+ |

## Installation
- Clone this repository into `lib/antivirus/remote` under your Moodle installation directory.
    ```
    git clone git@github.com:catalyst/moodle-antivirus_remote.git lib/antivirus/remote
    ```
- Run the Moodle upgrade: `php admin/cli/upgrade.php`
- Configure and enable the "Remote Scanner" in **Site administration > Plugins > Antivirus plugins > Manage Antivirus plugins** (`admin/settings.php?section=manageantiviruses`)

## Scanner engine
The scanner API for this plugin is very simple. A scanning engine must implement 2 endpoints.
- `/conncheck` must be implemented as a GET route to check connectivity to the engine, and should indicate that the antivirus engine is ready to scan files.
- `/scan` must be implemented as a POST route, to receive files as `Content-Type: multipart/form-data`

### Response API
The scanner should return responses to both endpoints using a shared JSON format, using two keys, `status` and `msg`. The status can be one of 3 values: `OK`, `ERROR` and `FOUND`.

If `/conncheck` returns anything but `OK` for the status, Moodle will not attempt to scan files using the scanning engine.

An example `/scan` response follows:

```
{
    "status": "FOUND"
    "msg": "This file contains malicious content"
}
```

The `/scan` endpoint will also have access to the filename and userid in Moodle, via form fields `filename` and `userid` respectively, for use in further forensics and logging in case of a found event.

**Note** The returned error message will not be exposed to the user, only Moodle administrators.

### Example engine
The provided `engine.py` is an example server that can be adapted to suit the virus engine that it will be communicating with. This file on its own is **NOT** an antivirus scanner, and cannot be used as such. However this file is free to be adapted into a production ready engine, after integration with a scanning service accessible from a terminal command invocation.

The example engine can be run using `python3 lib/antivirus/remote/engine.py` from your Moodle installation directory. It requires the `flask` module to be installed: `pip install flask`.

## Support

If you have issues please log them in github here

https://github.com/catalyst/moodle-antivirus_remote/issues

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact Catalyst IT Australia:

https://www.catalyst-au.net/contact-us

This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">
