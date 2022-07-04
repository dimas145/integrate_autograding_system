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

require_once("$CFG->libdir/formslib.php");

class register_form extends moodleform {
    // Add elements to form
    public function definition() {
        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('text', 'username', 'Docker User Name');             // Add elements to your form.
        $mform->setType('username', PARAM_TEXT);                                // Set type of element.
        $mform->setDefault('username', '');
        $mform->addRule('username', get_string('required'), 'required');

        $mform->addElement('text', 'repo_name', 'Docker Repository Name');
        $mform->setType('repo_name', PARAM_TEXT);
        $mform->addRule('repo_name', get_string('required'), 'required');

        $mform->addElement('text', 'tag', 'Docker Repository Tag');
        $mform->setType('tag', PARAM_TEXT);
        $mform->setDefault('tag', 'latest');                                    // Default value.

        $mform->addElement('text', 'name', 'Autograder Displayed Name');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        $mform->addElement('text', 'port', 'Autograder Running Port');
        $mform->setType('port', PARAM_INT);
        $mform->setDefault('port', '5000');

        $mform->addElement('text', 'endpoint', 'Autograder Grading Endpoint');
        $mform->setType('endpoint', PARAM_TEXT);
        $mform->setDefault('endpoint', '/grade');

        $mform->addElement('text', 'description', 'Autograder Description');
        $mform->setType('description', PARAM_TEXT);

        $this->add_action_buttons();
    }

    // Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
