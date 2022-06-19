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

$string['pluginname'] = 'Integrate moodle to advanced autograding system';
$string['gitlab'] = 'GitLab';
$string['gitlabusername'] = 'gitlabUsername';
$string['gitlabusernamedesc'] = 'Gitlab Username';
$string['isgitlabverified'] = 'isGitLabVerified';
$string['isgitlabverifieddesc'] = 'GitLab Verified';
$string['manage'] = 'Manage Autograding System Integration';
$string['bridgeservicedomain'] = 'Bridge Service Domain';
$string['bridgeserviceport'] = 'Bridge Service Port';

$string['bridgeservicedomaindefault'] = 'bridge-service';
$string['bridgeserviceportdefault'] = '8085';
$string['urltemplate'] = 'http://{$a->domain}:{$a->endpoint}{$a->endpoint}';
