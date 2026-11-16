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

use local_mobilemonitor\local\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Filter form of the audit panel.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_form extends \moodleform {

    /**
     * Defines the form.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'studentname', get_string('filter_studentname', 'local_mobilemonitor'),
            ['size' => 30]);
        $mform->setType('studentname', PARAM_TEXT);

        $mform->addElement('text', 'username', get_string('filter_username', 'local_mobilemonitor'),
            ['size' => 30]);
        $mform->setType('username', PARAM_TEXT);

        $eventtypes = ['' => get_string('any')];
        $eventtypes[helper::EVENTTYPE_CREATED] = get_string('eventtype_created', 'local_mobilemonitor');
        $eventtypes[helper::EVENTTYPE_DETECTED] = get_string('eventtype_detected', 'local_mobilemonitor');
        $eventtypes[helper::EVENTTYPE_USED] = get_string('eventtype_used', 'local_mobilemonitor');
        $mform->addElement('select', 'eventtype', get_string('filter_eventtype', 'local_mobilemonitor'),
            $eventtypes);
        $mform->setType('eventtype', PARAM_ALPHANUMEXT);

        $services = ['' => get_string('any')];
        foreach (helper::get_monitored_services() as $service) {
            $services[$service['id']] = $service['name'];
        }
        $mform->addElement('select', 'externalserviceid', get_string('filter_service', 'local_mobilemonitor'),
            $services);
        $mform->setType('externalserviceid', PARAM_INT);

        $mform->addElement('date_selector', 'fromdate', get_string('filter_fromdate', 'local_mobilemonitor'),
            ['optional' => true]);
        $mform->addElement('date_selector', 'todate', get_string('filter_todate', 'local_mobilemonitor'),
            ['optional' => true]);

        $this->add_action_buttons(true, get_string('filter_apply', 'local_mobilemonitor'));
    }

    /**
     * Validates the form.
     *
     * @param array $data Data of the form.
     * @param array $files Files of the form.
     * @return array Errors of the form.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $from = $this->get_timestamp($data['fromdate'] ?? null);
        $to = $this->get_timestamp($data['todate'] ?? null);

        if ($from && $to && $from > $to) {
            $errors['todate'] = get_string('errorinvalidrange', 'local_mobilemonitor');
        }

        return $errors;
    }

    /**
     * Converts the data of a date element into a timestamp.
     *
     * The date_selector element returns a timestamp (0 when the optional date is
     * disabled); an array with day/month/year is also accepted for safety.
     *
     * @param mixed $data Data of the date element.
     * @return int
     */
    protected function get_timestamp($data): int {
        if (is_array($data)) {
            if (empty($data['year'])) {
                return 0;
            }

            return (int) make_timestamp(
                (int) $data['year'],
                (int) ($data['month'] ?? 1),
                (int) ($data['day'] ?? 1)
            );
        }

        return max(0, (int) $data);
    }
}
