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
 * restore class for the Planet eStream Assignment Submission Plugin
 * extends submission plugin base class
 *
 * @package        assignsubmission_estream
 * @copyright        Planet Enterprises Ltd
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class restore_assignsubmission_estream_subplugin extends restore_subplugin
{
    protected function define_submission_subplugin_structure() {
            $paths = array();
            $elename = $this->get_namefor('submission');
            $elepath = $this->get_pathfor('/submission_estream');
            $paths[] = new restore_path_element($elename, $elepath);
            return $paths;
    }
    public function process_assignsubmission_estream_submission($data) {
            global $DB;
            $data = (object)$data;
            $data->assignment = $this->get_new_parentid('assign');
            $oldsubmissionid = $data->submission;
            $data->submission = $this->get_mappingid('submission', $data->submission);
            $DB->insert_record('assignsubmission_estream', $data);
            $this->add_related_files('assignsubmission_estream', 'submissions_estream', 'submission', null, $oldsubmissionid);
    }
}
