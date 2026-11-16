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

namespace local_mobilemonitor;

use local_mobilemonitor\local\helper;
use local_mobilemonitor\task\send_notification_task;

/**
 * Observer of the web service token events.
 *
 * Moodle 4.5 fires \core\event\webservice_token_created only when the token is
 * inserted by \core_external\util::generate_token_for_current_user() (login/token.php and
 * admin/tool/mobile/launch.php). Tokens created by an administrator (admin/webservice/tokens.php)
 * or in bulk (\webservice::create_token_for_users()) do not fire any event, therefore the
 * plugin also reconciles the tokens with a scheduled task.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Handles the creation of a new web service token.
     *
     * @param \core\event\webservice_token_created $event The event.
     * @return void
     */
    public static function token_created(\core\event\webservice_token_created $event): void {
        $tokenid = (int) $event->objectid;
        if (empty($tokenid)) {
            return;
        }

        $token = $event->get_record_snapshot('external_tokens', $tokenid);
        if (empty($token)) {
            $token = null;
        }

        $record = helper::record_token_event($tokenid, helper::EVENTTYPE_CREATED, $token);
        if (empty($record) || empty($record->isnew)) {
            // Nothing new to audit (the event was already processed).
            return;
        }

        self::queue_notification(helper::NOTIFICATION_TOKEN, (int) $record->id);
    }

    /**
     * Handles the usage of a web service token.
     *
     * The core updates {external_tokens}.lastaccess just before firing this event,
     * so the last access time of the token is available here.
     *
     * @param \core\event\webservice_token_sent $event The event.
     * @return void
     */
    public static function token_sent(\core\event\webservice_token_sent $event): void {
        global $DB;

        $tokenid = (int) $event->objectid;
        if (empty($tokenid)) {
            return;
        }

        $token = $event->get_record_snapshot('external_tokens', $tokenid);
        if (empty($token)) {
            $token = null;
        }

        // The snapshot may contain an outdated lastaccess value, refresh it from the database.
        $lastaccess = $DB->get_field('external_tokens', 'lastaccess', ['id' => $tokenid], IGNORE_MISSING);
        if (!empty($lastaccess) && $token !== null) {
            $token->lastaccess = (int) $lastaccess;
        }

        helper::record_token_event($tokenid, helper::EVENTTYPE_USED, $token);

        // Keep the grouped access history used by the per-student audit.
        helper::record_token_access($tokenid, $token);
    }

    /**
     * Queues the notification of a new event, so the observer stays lightweight.
     *
     * @param string $type Type of the notification.
     * @param int $recordid Identifier of the log record.
     * @return void
     */
    protected static function queue_notification(string $type, int $recordid): void {
        $task = new send_notification_task();
        $task->set_custom_data([
            'type' => $type,
            'recordid' => $recordid,
        ]);
        $task->set_component('local_mobilemonitor');

        \core\task\manager::queue_adhoc_task($task);
    }
}
