<?php
// This file is part of Moodle - https://moodle.org/
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

namespace local_mobilemonitor\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Filters of the student listing.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_filter_form extends \moodleform {

    /**
     * Defines the form elements.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'studentname', get_string('filter_studentname', 'local_mobilemonitor'));
        $mform->setType('studentname', PARAM_TEXT);

        $mform->addElement('text', 'username', get_string('filter_username', 'local_mobilemonitor'));
        $mform->setType('username', PARAM_TEXT);

        $statuses = [
            '' => get_string('filter_status_any', 'local_mobilemonitor'),
            'active' => get_string('status_active', 'local_mobilemonitor'),
            'inactive' => get_string('status_inactive', 'local_mobilemonitor'),
        ];
        $mform->addElement('select', 'status', get_string('filter_status', 'local_mobilemonitor'), $statuses);
        $mform->setType('status', PARAM_ALPHA);

        $this->add_action_buttons(false, get_string('filter_apply', 'local_mobilemonitor'));
    }
}
