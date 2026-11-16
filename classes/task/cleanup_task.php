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
 * Scheduled task that removes the old records of the audit log.
 *
 * The plugin never deletes the tokens of Moodle, only its own records.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_task extends \core\task\scheduled_task {

    /**
     * Returns the name of the task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskcleanup', 'local_mobilemonitor');
    }

    /**
     * Executes the task.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $days = max(1, (int) helper::get_setting('retentiondays'));
        $cutoff = time() - ($days * DAYSECS);

        $DB->delete_records_select('local_mobilemonitor_log', 'timecreated < :cutoff', ['cutoff' => $cutoff]);
        $DB->delete_records_select('local_mobilemonitor_alert', 'timecreated < :cutoff', ['cutoff' => $cutoff]);
        $DB->delete_records_select('local_mobilemonitor_access', 'timecreated < :cutoff', ['cutoff' => $cutoff]);
    }
}
