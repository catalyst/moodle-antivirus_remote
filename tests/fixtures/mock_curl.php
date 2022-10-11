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
 * Remote scanner tests.
 *
 * @package    antivirus_remote
 * @copyright  2022 Catalyst IT
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace antivirus_remote\tests\fixtures;

class mock_curl extends \curl {

    /** @var bool Is this curl mock being used for testing retries? */
    private $shouldretry;
    /** @var int Counter for sequential calls */
    public $callcount;

    /**
     * Constructor for curl mock. Handles input codes and data to return via POST.
     *
     * @param string $responsecode
     * @param string $response
     * @param boolean $retry
     */
    public function __construct($responsecode, $response, $retry = false) {
        $this->responsecode = $responsecode;
        $this->response = $response;
        $this->retrytest = $retry;
        $this->callcount = 0;
        parent::__construct();
    }

    /**
     * Here we should override post to return the given statuses (and simulate transient errors).
     *
     * @param string $url
     * @param string $params
     * @param array $options
     * @return void
     */
    public function post($url, $params = '', $options = array()) {
        $this->info['http_code'] = $this->responsecode;

        if (!$this->retrytest || ($this->retrytest && $this->callcount > 0)) {
            $response = $this->response;
        } else {
            // Fail to simulate a retry condition.
            $response = '{"status": "ERROR", "msg": "Retry condition"}';
        }

        $this->callcount++;
        return $response;
    }
}
