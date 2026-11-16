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

/**
 * Scheduled task that reconciles the monitored tokens with the audit log.
 *
 * Moodle does not fire any event when an administrator creates a token
 * (admin/webservice/tokens.php) or when tokens are created in bulk
 * (\webservice::create_token_for_users()). This task detects those tokens so the
 * plugin does not depend exclusively on the core events.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_task extends \core\task\scheduled_task {
    /** Maximum number of tokens processed in a single run. */
    public const BATCH_SIZE = 500;

    /**
     * Only tokens created during this window generate a notification.
     */
    public const NOTIFICATION_WINDOW = DAYSECS;

    /**
     * Returns the name of the task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('tasksync', 'local_mobilemonitor');
    }

    /**
     * Executes the task.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        if (!helper::is_monitoring_enabled()) {
            return;
        }

        $serviceids = helper::get_monitored_service_ids();
        if (empty($serviceids)) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED, 'sid');

        $sql = "SELECT t.id, t.userid, t.externalserviceid, t.timecreated, t.lastaccess
                  FROM {external_tokens} t
                 WHERE t.externalserviceid $insql
                   AND NOT EXISTS (
                       SELECT 1
                         FROM {local_mobilemonitor_log} l
                        WHERE l.tokenid = t.id
                   )
              ORDER BY t.id";

        $tokens = $DB->get_records_sql($sql, $params, 0, self::BATCH_SIZE);
        if (empty($tokens)) {
            return;
        }

        $notificationlimit = time() - self::NOTIFICATION_WINDOW;

        foreach ($tokens as $token) {
            $record = helper::record_token_event((int) $token->id, helper::EVENTTYPE_DETECTED, (object) $token);
            if (empty($record) || empty($record->isnew)) {
                continue;
            }

            // Historical tokens are registered silently, only recent ones are notified.
            if ((int) $record->timecreated >= $notificationlimit) {
                $task = new send_notification_task();
                $task->set_custom_data([
                    'type' => helper::NOTIFICATION_TOKEN,
                    'recordid' => (int) $record->id,
                ]);
                $task->set_component('local_mobilemonitor');
                \core\task\manager::queue_adhoc_task($task);
            }
        }
    }
}
