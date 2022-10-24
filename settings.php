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

defined('MOODLE_INTERNAL') || die();

if (!during_initial_install() && $ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('antivirus_remote/scanhost',
        new lang_string('scanhost', 'antivirus_remote'),
        new lang_string('scanhost_desc', 'antivirus_remote'), 'localhost:8000', PARAM_RAW_TRIMMED));

    $settings->add(new admin_setting_configcheckbox('antivirus_remote/useproxy',
        new lang_string('useproxy', 'antivirus_remote'),
        new lang_string('useproxy_desc', 'antivirus_remote'), 1));

    $settings->add(new admin_setting_configtextarea('antivirus_remote/retry',
        new lang_string('retry', 'antivirus_remote'),
        new lang_string('retry_desc', 'antivirus_remote'), 0, PARAM_INT));
}
