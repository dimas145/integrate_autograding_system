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

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_integrate_autograding_system_settings', new lang_string('pluginname', 'local_integrate_autograding_system')));
    $settingspage = new admin_settingpage('managelocalintegrateautogradingsystem', new lang_string('manage', 'local_integrate_autograding_system'));

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configtext(
            'local_integrate_autograding_system/bridge_service_url',
            new lang_string('bridgeserviceurl', 'local_integrate_autograding_system'),
            null,
            new lang_string('bridgeserviceurldefault', 'local_integrate_autograding_system'),
            PARAM_URL,
        ));
    }

    $ADMIN->add('localplugins', $settingspage);
}
