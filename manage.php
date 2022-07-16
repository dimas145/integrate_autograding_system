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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
$PAGE->set_url(new moodle_url('/local/integrate_autograding_system/manage.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Manage Autograder');

$config = get_config('local_integrate_autograding_system');
$curl = new curl();

$url = get_string(
    'urltemplate',
    'local_integrate_autograding_system',
    [
        'url' => $config->bridge_service_url,
        'endpoint' => '/autograder/list'
    ]
);
$curl->setHeader(array('Content-type: application/json'));
$curl->setHeader(array('Accept: application/json', 'Expect:'));
$response = $curl->get($url);
$response_json = json_decode($response);

echo $OUTPUT->header();

$template_context = (object) [
    'autograders' => is_null($response_json) ? []: (count($response_json->autograders) > 0 ? array_values($response_json->autograders) : []),
    // 'register_url' => new moodle_url('/local/integrate_autograding_system/register.php'),
];
echo $OUTPUT->render_from_template('local_integrate_autograding_system/manage', $template_context);

echo $OUTPUT->footer();
