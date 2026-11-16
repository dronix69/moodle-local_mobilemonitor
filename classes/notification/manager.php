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

namespace local_mobilemonitor\notification;

use local_mobilemonitor\local\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Manager of the administrative notifications.
 *
 * The notifications are sent with the native Moodle messaging API, no mail() is used.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** Capability required to receive the notifications. */
    public const CAPABILITY = 'local/mobilemonitor:viewlogs';

    /**
     * Returns the list of users that must receive the notifications.
     *
     * @return \stdClass[] List of user records.
     */
    public static function get_recipients(): array {
        global $DB;

        $userids = [];

        // Site administrators: they have every capability, but they may not have an explicit
        // role assignment, so get_users_by_capability() alone would not return them.
        foreach (get_admins() as $admin) {
            $userids[(int) $admin->id] = true;
        }

        // Users with the capability to see the audit log (delegated administrators).
        $capusers = get_users_by_capability(
            \context_system::instance(),
            self::CAPABILITY,
            'u.id,u.username,u.firstname,u.lastname,u.email,u.suspended,u.deleted,u.emailstop'
        );
        foreach ($capusers as $capuser) {
            $userids[(int) $capuser->id] = true;
        }

        // Additional recipients configured by the administrator.
        $extra = (string) helper::get_setting('notificationusers');
        if (trim($extra) !== '') {
            $candidates = preg_split('/[\s,;]+/', trim($extra));
            foreach ($candidates as $candidate) {
                if ($candidate === '' || !ctype_digit($candidate)) {
                    continue;
                }
                $userids[(int) $candidate] = true;
            }
        }

        if (empty($userids)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal(array_keys($userids), SQL_PARAMS_NAMED, 'uid');
        $users = $DB->get_records_select(
            'user',
            "id $insql AND deleted = 0 AND suspended = 0",
            $params,
            '',
            'id, username, firstname, lastname, email, emailstop'
        );

        return array_values($users);
    }

    /**
     * Notifies the administrators about a new registered token.
     *
     * @param \stdClass $logrecord The registered token.
     * @return int Number of recipients that were notified.
     */
    public static function notify_new_token(\stdClass $logrecord): int {
        if (!helper::are_notifications_enabled()) {
            return 0;
        }

        $recipients = self::get_recipients();
        if (empty($recipients)) {
            return 0;
        }

        $user = \core_user::get_user($logrecord->userid, '*', IGNORE_MISSING);
        if (empty($user)) {
            return 0;
        }

        $servicename = helper::get_service_name((int) $logrecord->externalserviceid);
        if ($servicename === '') {
            $servicename = helper::get_service_shortname((int) $logrecord->externalserviceid);
        }
        if ($servicename === '') {
            $servicename = get_string('unknownservice', 'local_mobilemonitor');
        }

        $lines = [];
        $lines[] = get_string('notification_body', 'local_mobilemonitor');
        $lines[] = get_string('notification_info', 'local_mobilemonitor');
        $lines[] = get_string('notification_student', 'local_mobilemonitor', fullname($user));
        $lines[] = get_string('notification_username', 'local_mobilemonitor', $user->username);
        $lines[] = get_string('notification_service', 'local_mobilemonitor', $servicename);
        $lines[] = get_string('notification_time', 'local_mobilemonitor', userdate($logrecord->timecreated));
        $lines[] = get_string('notification_ip', 'local_mobilemonitor', self::format_ip($logrecord));
        $lines[] = get_string('notification_eventtype', 'local_mobilemonitor',
            get_string('eventtype_' . $logrecord->eventtype, 'local_mobilemonitor'));

        $body = implode("\n", $lines);

        $url = new \moodle_url('/local/mobilemonitor/index.php');
        $body .= "\n" . get_string('notification_link', 'local_mobilemonitor', $url->out(false));

        return self::send_to_recipients(
            $recipients,
            get_string('notification_subject', 'local_mobilemonitor'),
            $body,
            $url
        );
    }

    /**
     * Notifies the administrators about a repetitive generation of tokens.
     *
     * @param \stdClass $alert The alert.
     * @return int Number of recipients that were notified.
     */
    public static function notify_repetition(\stdClass $alert): int {
        if (!helper::are_notifications_enabled()) {
            return 0;
        }

        $recipients = self::get_recipients();
        if (empty($recipients)) {
            return 0;
        }

        $user = \core_user::get_user($alert->userid, '*', IGNORE_MISSING);
        if (empty($user)) {
            return 0;
        }

        $threshold = (int) helper::get_setting('threshold');
        $period = (int) helper::get_setting('periodhours');

        $lines = [];
        $lines[] = get_string('notification_alert_body', 'local_mobilemonitor');
        $lines[] = get_string('notification_info', 'local_mobilemonitor');
        $lines[] = get_string('notification_student', 'local_mobilemonitor', fullname($user));
        $lines[] = get_string('notification_username', 'local_mobilemonitor', $user->username);
        $lines[] = get_string('notification_alert_count', 'local_mobilemonitor', (int) $alert->tokencount);
        $lines[] = get_string('notification_alert_threshold', 'local_mobilemonitor', $threshold);
        $lines[] = get_string('notification_alert_period', 'local_mobilemonitor', $period);
        $lines[] = get_string('notification_alert_disclaimer', 'local_mobilemonitor');

        $body = implode("\n", $lines);

        $url = new \moodle_url('/local/mobilemonitor/index.php', [
            'studentname' => fullname($user),
            'eventtype' => helper::EVENTTYPE_CREATED,
        ]);
        $body .= "\n" . get_string('notification_link', 'local_mobilemonitor', $url->out(false));

        return self::send_to_recipients(
            $recipients,
            get_string('notification_alert_subject', 'local_mobilemonitor'),
            $body,
            $url
        );
    }

    /**
     * Sends a message to a list of recipients.
     *
     * @param \stdClass[] $recipients List of recipients.
     * @param string $subject Subject of the message.
     * @param string $body Body of the message.
     * @param \moodle_url $url URL of the audit panel.
     * @return int Number of recipients that were notified.
     */
    protected static function send_to_recipients(array $recipients, string $subject, string $body, \moodle_url $url): int {
        global $CFG;

        require_once($CFG->libdir . '/messagelib.php');

        $notified = 0;
        foreach ($recipients as $recipient) {
            $message = new \core\message\message();
            $message->component = 'local_mobilemonitor';
            $message->name = 'notification';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $recipient;
            $message->subject = $subject;
            $message->fullmessage = $body;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = $subject;
            $message->notification = 1;
            $message->courseid = SITEID;
            $message->contexturl = $url->out(false);
            $message->contexturlname = get_string('pluginname', 'local_mobilemonitor');

            if (message_send($message)) {
                $notified++;
            }
        }

        return $notified;
    }

    /**
     * Formats the IP address of a log record for the notifications.
     *
     * @param \stdClass $logrecord The log record.
     * @return string
     */
    protected static function format_ip(\stdClass $logrecord): string {
        if (empty($logrecord->ipaddress)) {
            return get_string('notavailable', 'local_mobilemonitor');
        }

        return (string) $logrecord->ipaddress;
    }
}
