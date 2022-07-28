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
namespace antivirus_remote;

class scanner extends \core\antivirus\scanner {

    public function __construct() {
        parent::__construct();
        $this->status = '';
        $this->response = '';
    }

    /**
     * Is the remote scanning engine reachable and working?
     *
     * @return boolean
     */
    public function is_configured() {
        global $CFG;

        // Simply curl the conncheck endpoint and get the status code.
        $curl = new \curl();

        if (!get_config('useproxy', 'antivirus_remote')) {
            $curl->proxy = false;
        }

        // Allow for global curlopts from config file only. No frontend for control.
        if (!empty($CFG->antivirus_remote_curlopts)) {
            $opts = $CFG->antivirus_remote_curlopts;
        } else {
            $opts = [];
        }

        $host = get_config('antivirus_remote', 'scanhost');
        $resp = $curl->get($host . '/conncheck', [], $opts);
        $obj = json_decode($resp);

        if (!is_object($obj)) {
            debugging('Received non-JSON response on /conncheck: ' . $resp);
            return false;
        }
        // Anything that isn't an OK means the scanner is not available.
        return $obj->status === 'OK';
    }

    /**
     * Gets a file from tmp then pipes it to the remote scanning engine as data.
     *
     * @param string $file
     * @param string $filename
     * @return int Scanning constant
     */
    public function scan_file($file, $filename) {
        $this->post_file($file, $filename);
        // Handle naughty statuses.
        if (($this->status === \core\antivirus\scanner::SCAN_RESULT_ERROR) ||
                ($this->status === \core\antivirus\scanner::SCAN_RESULT_FOUND)) {
            $this->message_admins($this->response->msg);
            $this->set_scanning_notice($this->response->msg);
        }
        // Return the status, we know this is mapped to the correct status constant inside of post_file.
        return $this->status;
    }

    /**
     * Post the file as form data to the remote engine.
     *
     * @param string $file location of the file
     * @param string $filename the name of the provided file.
     * @param \curl $curl the curl object to use. Used for testing.
     * @return void
     */
    protected function post_file(string $file, string $filename, \curl $curl = null) {
        global $CFG, $USER;

        // Curl is the easiest engine to dump data to a remote endpoint.
        if (!isset($curl)) {
            $curl = new \curl();
        }

        if (!get_config('useproxy', 'antivirus_remote')) {
            $curl->proxy = false;
        }

        $host = get_config('antivirus_remote', 'scanhost');

        // Sending files needs to be done using form-data.
        $curl->setHeader([
            'Content-Type: multipart/form-data'
        ]);
        // Now we can set the fields of the "form". curl_file_create embeds the file into a format that form_data can use
        $fields = [
            'scanfile' => curl_file_create($file),
            'filename' => $filename,
            'userid' => $USER->id
        ];

        // Allow for global curlopts from config file only. No frontend for control.
        if (!empty($CFG->antivirus_remote_curlopts)) {
            $opts = $CFG->antivirus_remote_curlopts;
        } else {
            $opts = [];
        }

        // Now post away and check the response!
        $resp = $curl->post($host . '/scan', $fields, $opts);
        $this->response = json_decode($resp);

        if (!is_object($this->response)) {
            debugging('Received non-JSON response on /scan: ' . $resp);
            $this->status = \core\antivirus\scanner::SCAN_RESULT_ERROR;
            return;
        }

        if ($curl->info['http_code'] !== 200 || $this->response->status === 'ERROR') {
            $this->status = \core\antivirus\scanner::SCAN_RESULT_ERROR;
            return;
        }

        if ($this->response->status === 'FOUND') {
            $this->status = \core\antivirus\scanner::SCAN_RESULT_FOUND;
            return;
        }

        // If nothing was found, and no errors, we are good.
        $this->status = \core\antivirus\scanner::SCAN_RESULT_OK;
    }
}
