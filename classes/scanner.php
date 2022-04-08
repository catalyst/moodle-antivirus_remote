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

class scanner extends \core\antivirus\scanner {

    const BLOCK_SIZE = 4096;
    const UNKNOWN = 'unknown';
    const OK = 'ok';
    const ERROR = 'error';
    const VIRUS = 'found';

    function __construct() {
        parent::__construct();
        $this->status = self::UNKNOWN;
    }

    /**
     * Is the remote scanning engine reachable and working?
     *
     * @return boolean
     */
    public function is_configured() {
        $curl = new curl();
        $host = get_config('antivirus_remote', 'scanhost');
        $curl->get($host . '/conncheck');
        $obj = json_decode($curl->response['body']);
        if ($obj->status === 'OK') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets a file from tmp then pipes it to the remote scanning engine as data.
     *
     * @param string $file
     * @param string $filename
     * @return void
     */
    public function scan_file($file, $filename) {
        $fhandle = fopen($file, 'r');
        $socket = $this->open_socket();
        $this->stream_file($fhandle, filesize($file), $socket);
    }

    /**
     * Pipes data to the remote scanning engine.
     *
     * @param string $data
     * @return void
     */
    public function scan_data($data) {
        $socket = $this->open_socket();
        $this->stream_data($data, $socket);
        $this->get_socket_response();
    }

    /**
     * Open the scanning socket to the remote scanner endpoint.
     *
     * @return resource
     */
    protected function open_socket() {
        $host = get_config('antivirus_remote', 'scanhost');
        $socket = stream_socket_client($host . '/scan');
        return $socket;
    }

    protected function stream_file($fhandle, $filesize, $socket) {
        $pointer = 0;
        do {
            // Detect the end of file ahead of time.
            if ($pointer + self::BLOCK_SIZE > $filesize) {
                $block = $filesize - $pointer;
            } else {
                $block = self::BLOCK_SIZE;
            }
            $chunk = stream_copy_to_stream($fhandle, $socket, $block, $pointer);
            if ($chunk !== $block || $chunk === false) {
                $this->status = self::ERROR;
            }
        } while ($pointer < $filesize);

        // Append a message for end of data.
        fwrite($socket, 'ENDDATASTREAM');

        //fflush($this->socket);
    }

    protected function stream_data($data, $socket) {
        $pointer = 0;
        $filesize = strlen($data);
        do {
            // Detect the end of file ahead of time.
            if ($pointer + self::BLOCK_SIZE > $filesize) {
                $block = $filesize - $pointer;
            } else {
                $block = self::BLOCK_SIZE;
            }
            $chunk = fwrite($socket, substr($data, $pointer, $block));
            if ($chunk !== $block || $chunk === false) {
                $this->status = self::ERROR;
            }
        } while ($pointer < $filesize);

        // Append a message for end of data.
        fwrite($socket, 'ENDDATASTREAM');

        //fflush($this->socket);
    }

    protected function get_socket_response() {

    }
}