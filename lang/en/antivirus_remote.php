<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle remote scanner API plugin.
 *
 * @package    antivirus_remote
 * @copyright  2022 Catalyst IT
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Remote scanner';
$string['scanhost'] = 'Remote host';
$string['scanhost_desc'] = 'Enter the host and port for the remote scanning engine.';
$string['errorscanfile'] = 'The remote scanner experienced an error when scanning file.';
$string['privacy:metadata'] = 'The remote scanner plugin does not store any user data.';
$string['useproxy'] = 'Use Moodle proxy';
$string['useproxy_desc'] = 'Route traffic to the antivirus server via the Moodle proxy.';
$string['retry'] = 'Retry delay';
$string['retry_desc'] = 'If set, waits for the number of seconds, then attempts to re-scan once. Set to 0 to disable retrying.';
