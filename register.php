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
 * Register autograder page
 *
 * @package   local_integrate_autograding_system
 * @copyright 2022, Dimas 13518069@std.stei.itb.ac.id
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/integrate_autograding_system/classes/form/register.php');

require_login();
$PAGE->set_url(new moodle_url('/local/integrate_autograding_system/register.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Register Autograder');

// Register Autograder form
$mform = new register_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is pressed, redirect to home
    redirect(new moodle_url('/local/integrate_autograding_system/manage.php'), 'Autograder is not registered');
} else if ($fromform = $mform->get_data()) {
    // Submit data to external system
    $config = get_config('local_integrate_autograding_system');
    $curl = new curl();

    $url = get_string(
        'urltemplate',
        'local_integrate_autograding_system',
        [
            'url' => $config->bridge_service_url,
            'endpoint' => '/autograder/initialize'
        ]
    );
    $data = array(
        'dockerUser' => $fromform->username,
        'name' => $fromform->name,
        'displayedName' => $fromform->displayedName,
        'tag' => $fromform->tag,
        'description' => $fromform->description,
    );
    $data_string = json_encode($data);

    $curl->setHeader(array('Content-type: application/json'));
    $curl->setHeader(array('Accept: application/json', 'Expect:'));
    $response = $curl->post($url, $data_string);
    $response_json = json_decode($response);

    if ($response_json->success) {
        redirect(new moodle_url('/local/integrate_autograding_system/manage.php'), 'Autograder is registered');
    } else {
        redirect(new moodle_url('/local/integrate_autograding_system/manage.php'), 'Error');
    }
}

echo $OUTPUT->header();

//displays the form
$mform->display();

echo $OUTPUT->footer();
