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

use core_user\output\myprofile\tree;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/profile/definelib.php');
require_once($CFG->dirroot . '/user/profile/field/text/define.class.php');
require_once($CFG->dirroot . '/user/profile/field/checkbox/define.class.php');
require_once($CFG->dirroot . '/webservice/lib.php');

/**
 * Add GitLab category and nodes to myprofile page.
 *
 * @param tree $tree The myprofile tree to add categories and nodes to.
 * @param stdClass $user The user object that the profile page belongs to.
 * @param bool $iscurrentuser If the $user object is the current user.
 * @param stdClass $course The course to determine if we are in a course context or system context.
 *
 * @return bool
 */
function local_integrate_autograding_system_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    $config = get_config('local_integrate_autograding_system');

    // Create GitLab category.
    $categoryname = get_string('gitlab', 'local_integrate_autograding_system');
    $category = new core_user\output\myprofile\category('gitlab', $categoryname, 'contact');
    $tree->add_category($category);

    $url = get_string(
        'urltemplate',
        'local_integrate_autograding_system',
        [
            'url' => $config->bridge_service_url,
            'endpoint' => "/gitlab/auth?userId=$user->id"
        ],
    );
    $node = new core_user\output\myprofile\node('gitlab', 'verify', 'Click here to verify', null, $url, null, null, 'editprofile');
    $tree->add_node($node);

    return true;
}

/**
 * Add Autograders to Navigation.
 *
 * @param global_navigation $nav The myprofile tree to add categories and nodes to.
 */
function local_integrate_autograding_system_extend_navigation(global_navigation $nav) {
    // admin only
    if (!has_capability('moodle/site:config', context_system::instance())) {
        return;
    }

    $main_node = $nav->add('Autograders', '/local/integrate_autograding_system/manage.php');
    $main_node->nodetype = 1;
    $main_node->collapse = false;
    $main_node->forceopen = true;
    $main_node->isexpandable = false;
    $main_node->showinflatnavigation = true;
}

/**
 * Add Plugin configs.
 *
 * @param global_navigation $nav The myprofile tree to add categories and nodes to.
 */
function local_integrate_autograding_system_after_config() {
    global $DB;

    // Add gitlab profile field
    if (!$DB->record_exists('user_info_category', array('name' => get_string('gitlab', 'local_integrate_autograding_system')))) {
        $data = new \stdClass();
        $data->sortorder = $DB->count_records('user_info_category') + 1;
        $data->name = get_string('gitlab', 'local_integrate_autograding_system');
        $data->id = $DB->insert_record('user_info_category', $data, true);

        $createdcategory = $DB->get_record('user_info_category', array('id' => $data->id));
        \core\event\user_info_category_created::create_from_category($createdcategory)->trigger();

        $profileclass = new \profile_define_text();
        $data = (object) [
            'shortname' => get_string('gitlabusername', 'local_integrate_autograding_system'),
            'name' => get_string('gitlabusernamedesc', 'local_integrate_autograding_system'),
            'datatype' => 'text',
            'descriptionformat' => 1,
            'categoryid' => $createdcategory->id,
            'signup' => 0,
            'forceunique' => 0,
            'visible' => 2,
            'locked' => 1,
            'defaultdata' => '-',
            'param1' => 30,
            'param2' => 2048,
        ];
        $profileclass->define_save($data);

        $profileclass = new \profile_define_checkbox();
        $data = (object) [
            'shortname' => get_string('isgitlabverified', 'local_integrate_autograding_system'),
            'name' => get_string('isgitlabverifieddesc', 'local_integrate_autograding_system'),
            'datatype' => 'checkbox',
            'descriptionformat' => 1,
            'categoryid' => $createdcategory->id,
            'signup' => 0,
            'forceunique' => 0,
            'visible' => 2,
            'locked' => 1,
            'defaultdata' => 0,
            'defaultdataformat' => 0,
        ];
        $profileclass->define_save($data);
    }

    // Add external service
    $webservicemanager = new \webservice();

    $service = $DB->get_record('external_services', array('shortname' => get_string('servicename', 'local_integrate_autograding_system')));
    $serviceid = -1;
    if (empty($service)) {
        $data = (object) [
            'shortname' => get_string('servicename', 'local_integrate_autograding_system'),
            'name' => get_string('servicenamedesc', 'local_integrate_autograding_system'),
            'component' => get_string('pluginname', 'local_integrate_autograding_system'),
            'enabled' => 1,
            'restrictedusers' => 0,
            'downloadfiles' => 0,
            'uploadfiles' => 0,
        ];

        $serviceid = $webservicemanager->add_external_service($data);
    } else {
        $serviceid = $service->id;
    }

    if (!$webservicemanager->service_function_exists('core_user_update_users', $serviceid)) {
        $webservicemanager->add_external_function_to_service('core_user_update_users', $serviceid);
    }

    if (!$webservicemanager->service_function_exists('core_grades_update_grades', $serviceid)) {
        $webservicemanager->add_external_function_to_service('core_grades_update_grades', $serviceid);
    }
}

/**
 * Add custom form when creating assignment.
 * 
 * @param moodleform $formwrapper The moodle quickforms wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 */
function local_integrate_autograding_system_coursemodule_standard_elements($formwrapper, $mform) {
    $config = get_config('local_integrate_autograding_system');
    global $CFG;

    $modulename = $formwrapper->get_current()->modulename;
    if ($modulename == 'assign') {
        $mform->addElement('header', 'exampleheader', get_string('autograding', 'local_integrate_autograding_system'));

        $mform->addElement(
            'filemanager',
            'codereference',
            get_string('codereference', 'local_integrate_autograding_system'),
            null,
            array(
                'subdirs' => 0, 'maxbytes' => $CFG->maxbytes, 'areamaxbytes' => 10485760, 'maxfiles' => 50,
                'accepted_types' => '*', 'return_types' => FILE_INTERNAL | FILE_EXTERNAL
            )
        );

        $grading_methods = array();
        $grading_methods['MAXIMUM'] = 'Maximum';
        $grading_methods['MINIMUM'] = 'Minimum';
        $grading_methods['AVERAGE'] = 'Average';
        $mform->addElement('select', 'gradingMethod', 'Grading Method', $grading_methods);
        $mform->setDefault('gradingMethod', 'MAXIMUM');

        $grading_priority = array();
        $grading_priority['FIRST'] = 'First';
        $grading_priority['LAST'] = 'Last';
        $mform->addElement('select', 'gradingPriority', 'Grading Priority', $grading_priority);
        $mform->setDefault('gradingPriority', 'FIRST');

        $mform->addElement('text', 'timeLimit', 'Time Limit');
        $mform->setType('timeLimit', PARAM_INT);
        $mform->setDefault('timeLimit', 3000);

        $curl = new curl();
        $url = get_string(
            'urltemplate',
            'local_integrate_autograding_system',
            [
                'url' => $config->bridge_service_url,
                'endpoint' => '/autograder/running'
            ]
        );
        $curl->setHeader(array('Content-type: application/json'));
        $curl->setHeader(array('Accept: application/json', 'Expect:'));
        $response_json = json_decode($curl->get($url));

        $autograders = array();
        foreach ($response_json->autograders as $autograder) {
            $autograders[$autograder] = $autograder;
        }

        $graders = $mform->addElement('select', 'autograders', 'Autograders', $autograders);
        $graders->setMultiple(true);
    }
}

/**
 * Process data from submitted form
 *
 * @param stdClass $data
 * @param stdClass $course
 */
function local_integrate_autograding_system_coursemodule_edit_post_actions($data, $course) {
    $config = get_config('local_integrate_autograding_system');
    global $DB;

    if (
        isset($data->codereference) &&
        isset($data->gradingMethod) &&
        isset($data->gradingPriority) &&
        isset($data->timeLimit) &&
        isset($data->autograders) &&
        count($data->autograders) > 0 &&
        ($data->modulename === 'assign')
    ) { // only valid if all autograding data is set
        $files_data = $DB->get_records('files', array('itemid' => $data->codereference));

        // create gitlab repository
        $curl = new curl();
        $name = str_replace(' ', '-', $data->name);

        $url = get_string(
            'urltemplate',
            'local_integrate_autograding_system',
            [
                'url' => $config->bridge_service_url,
                'endpoint' => '/gitlab/createRepository'
            ]
        );
        $payload = array(
            'courseId' => $course->id,
            'activityId' => $data->coursemodule,
            'name' => $name,
            'instance' => $data->instance,
            'gradingMethod' => $data->gradingMethod,
            'gradingPriority' => $data->gradingPriority,
            'timeLimit' => $data->timeLimit,
            'dueDate' => $data->duedate,
            'autograders' => $data->autograders,
        );
        $payload_string = json_encode($payload);

        $curl->setHeader(array('Content-type: application/json'));
        $curl->setHeader(array('Accept: application/json', 'Expect:'));
        $response_json = json_decode($curl->post($url, $payload_string));

        // if ($response_json->success) {
        //     // TODO
        // }

        // save code reference
        foreach ($files_data as $file_data) {
            if ($file_data->filename !== '.') {
                $fs = get_file_storage();
                $file = $fs->get_file_by_hash($file_data->pathnamehash);
                $curl = new curl();

                $prop = explode('.', $file_data->filename);
                $filename = $prop[0];
                $ex = '';
                if (count($prop) > 1) {
                    $ex = $prop[1];
                }

                $url = get_string(
                    'urltemplate',
                    'local_integrate_autograding_system',
                    [
                        'url' => $config->bridge_service_url,
                        'endpoint' => '/moodle/saveReference'
                    ]
                );
                $payload = array(
                    'courseId' => $course->id,
                    'activityId' => $data->coursemodule,
                    'contentHash' => $file_data->contenthash,
                    'extension' => $ex,
                    'filename' => $filename,
                    'rawContent' => base64_encode($file->get_content()),
                );
                $payload_string = json_encode($payload);
                $curl->setHeader(array('Content-type: application/json'));
                $curl->setHeader(array('Accept: application/json', 'Expect:'));
                $curl->post($url, $payload_string);

                $file->delete();    // remove from moodle file system
            }
        }

        unset($data->codereference);
        unset($data->gradingMethod);
        unset($data->gradingPriority);
        unset($data->timeLimit);
        unset($data->autograders);
    }

    return $data;
}
