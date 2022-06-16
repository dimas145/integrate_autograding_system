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
    // Create GitLab category.
    $categoryname = get_string('gitlab', 'local_integrate_autograding_system');
    $category = new core_user\output\myprofile\category('gitlab', $categoryname, 'contact');
    $tree->add_category($category);

    $url = sprintf('http://localhost:5000/gitlab/auth?userId=%s', $user->id); // TODO
    $node = new core_user\output\myprofile\node('gitlab', 'verify', 'Click here to verify', null, $url, null, null, 'editprofile');
    $tree->add_node($node);

    $node = new core_user\output\myprofile\node('gitlab', 'name', 'gitlab', null, null, $user->username);  // TODO
    $tree->add_node($node);

    return true;
}
