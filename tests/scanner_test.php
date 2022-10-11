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



namespace antivirus_remote\tests;
defined('MOODLE_INTERNAL') || die();
require(__DIR__ . '/fixtures/mock_curl.php');

/**
 * Remote scanner tests.
 *
 * @package    antivirus_remote
 * @copyright  2022 Catalyst IT
 * @author     Peter Burnett <peterburnett@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scanner_test extends \advanced_testcase {

    /**
     * Data provider for test_post_file
     *
     * @return array
     */
    public function test_post_file_provider() {
        // Return a set of params: $responsecode + response, and expected responsecode from post_file invocation.
        return [
            [200, '{"status": "OK"}', \core\antivirus\scanner::SCAN_RESULT_OK],
            [404, '{}', \core\antivirus\scanner::SCAN_RESULT_ERROR],
            [200, '{"status": "ERROR", "msg": "CPU melted"}', \core\antivirus\scanner::SCAN_RESULT_ERROR],
            [502, '{"status": "FOUND", "msg": "File is bad"}', \core\antivirus\scanner::SCAN_RESULT_ERROR],
            [200, '{"status": "FOUND", "msg": "File is bad"}', \core\antivirus\scanner::SCAN_RESULT_FOUND]
        ];
    }

    /**
     * Test for standard single request file posting.
     *
     * @dataProvider test_post_file_provider
     * @param int $responsecode code to set as the response from the POST action.
     * @param string $response string to set as the verbose response from the POST action.
     * @param int $scannercode Expected result from the mock scan.
     */
    public function test_post_file($responsecode, $response, $scannercode) {
        $file = __DIR__ . '/fixtures/test_doc.docx';
        $filename = 'Test Document';

        $curl = new \antivirus_remote\tests\fixtures\mock_curl($responsecode, $response);
        $scanner = new \antivirus_remote\scanner();
        $reflectedmethod = new \ReflectionMethod($scanner, 'post_file');
        $reflectedmethod->setAccessible(true);
        $reflectedmethod->invoke($scanner, $file, $filename, $curl);

        // Now access the internal property and get its value. It must be equal to the param value.
        $reflectedprop = new \ReflectionProperty($scanner, 'status');
        $this->assertEquals($scannercode, $reflectedprop->getValue($scanner));
    }

    /**
     * Test for retrying scans.
     *
     * @covers \antivirus_remote\scanner::post_file
     * @return void
     */
    public function test_file_retry() {
        $this->resetAfterTest(true);
        $file = __DIR__ . '/fixtures/test_doc.docx';
        $filename = 'Test Document';
        set_config('retry', 3, 'antivirus_remote');

        $curl = new \antivirus_remote\tests\fixtures\mock_curl(200, '{"status": "OK"}', true);
        $scanner = new \antivirus_remote\scanner();
        $reflectedmethod = new \ReflectionMethod($scanner, 'post_file');
        $reflectedmethod->setAccessible(true);
        $reflectedmethod->invoke($scanner, $file, $filename, $curl);

        // Now access the internal property and get its value. It must be OK.
        $reflectedprop = new \ReflectionProperty($scanner, 'status');
        $this->assertEquals(\core\antivirus\scanner::SCAN_RESULT_OK, $reflectedprop->getValue($scanner));
        // And confirm that there were 2 calls to the mock curl.
        $this->assertEquals(2, $curl->callcount);
    }
}
