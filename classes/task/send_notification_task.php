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

namespace local_mobilemonitor\task;

use local_mobilemonitor\local\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Adhoc task that sends the administrative notifications.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_notification_task extends \core\task\adhoc_task {

    /**
     * Returns the name of the task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('tasksendnotification', 'local_mobilemonitor');
    }

    /**
     * Executes the task.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $type = $data['type'] ?? '';
        $recordid = (int) ($data['recordid'] ?? 0);

        if (empty($recordid)) {
            return;
        }

        if ($type === helper::NOTIFICATION_TOKEN) {
            $record = $DB->get_record('local_mobilemonitor_log', ['id' => $recordid], '*', IGNORE_MISSING);
            if (empty($record)) {
                return;
            }

            \local_mobilemonitor\notification\manager::notify_new_token($record);

            // Evaluate the repetitive generation of tokens for this user.
            $alert = helper::evaluate_repetition((int) $record->userid);
            if (!empty($alert)) {
                \local_mobilemonitor\notification\manager::notify_repetition($alert);
            }

            return;
        }
    }
}
