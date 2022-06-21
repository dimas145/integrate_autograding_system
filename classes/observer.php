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
 * Version metadata for the plugintype_pluginname plugin.
 *
 * @package   local_integrate_autograding_system
 * @copyright 2022, Dimas 13518069@std.stei.itb.ac.id
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Assignment event handler for this plugin.
 */
class local_integrate_autograding_system_observer {
    /**
     * Triggered when an assignment is created.
     *
     * @param \core\event\course_module_created $event
     */
    public static function assignment_created(\core\event\course_module_created $event) {
        if ($event->other['modulename'] === 'assign') {
            global $DB;
            $config = get_config('local_integrate_autograding_system');
            $curl = new curl();

            $name = str_replace(' ', '-', $event->other['name']);
            $instance_id = $event->other['instanceid'];
            $instance = $DB->get_record('course_modules', array('instance' => $instance_id));

            $url = get_string(
                'urltemplate',
                'local_integrate_autograding_system',
                [
                    'url' => $config->bridge_service_url,
                    'endpoint' => '/gitlab/createRepository'
                ]
            );
            $data = array(
                'courseId' => $event->courseid,
                'activityId' => $instance->module,
                'name' => $name,
                'instance' => $instance_id,
                'gradingMethod' => 'MAXIMUM',       // TODO
                'gradingPriority' => 'FIRST',       // TODO
                'timeLimit' => '3000',              // TODO
                'dueDate' => '1687021200',          // TODO
                'autograders' => [                  // TODO
                    'python-black-box-autograder'
                ],
            );
            $data_string = json_encode($data);

            $curl->setHeader(array('Content-type: application/json'));
            $curl->setHeader(array('Accept: application/json', 'Expect:'));
            $response = $curl->post($url, $data_string);
            $response_json = json_decode($response);

            if ($response_json->success) {
                // TODO
            }
        }
    }
}
