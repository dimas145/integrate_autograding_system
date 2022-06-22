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

require_once($CFG->dirroot . '/user/profile/index_category_form.php');
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
}
