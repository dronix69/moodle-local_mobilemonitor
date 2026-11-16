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

namespace local_mobilemonitor\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class with the shared logic of the Mobile Token Monitor plugin.
 *
 * IMPORTANT: this plugin never stores the token secret nor the private token.
 * Only metadata (identifiers, service, timestamps and the request IP) is stored.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /** Event type: the token was created and the core event was fired. */
    public const EVENTTYPE_CREATED = 'created';

    /** Event type: the token was used (sent) by a client. */
    public const EVENTTYPE_USED = 'used';

    /** Event type: the token was detected by the reconciliation task (no core event). */
    public const EVENTTYPE_DETECTED = 'detected';

    /** Alert type: repetitive token generation. */
    public const ALERT_REPETITION = 'repetition';

    /** Notification type: a new token was registered. */
    public const NOTIFICATION_TOKEN = 'token';

    /** Notification type: repetitive token generation. */
    public const NOTIFICATION_ALERT = 'alert';

    /**
     * Access rows are grouped per session: a new row is stored only when the IP changes
     * or the last stored access is older than this many seconds. This bounds the size of
     * the access history without losing the information needed by the audit.
     */
    public const ACCESS_THROTTLE = 300;

    /**
     * Returns the value of a plugin setting, applying its default value.
     *
     * @param string $name Name of the setting, without the component prefix.
     * @return mixed
     */
    public static function get_setting(string $name) {
        $defaults = [
            'enabled' => 1,
            'notificationsenabled' => 1,
            'alertenabled' => 1,
            'threshold' => 5,
            'periodhours' => 24,
            'retentiondays' => 90,
            'inactivitydays' => 30,
            'serviceshortnames' => MOODLE_OFFICIAL_MOBILE_SERVICE,
            'notificationusers' => '',
        ];

        $default = $defaults[$name] ?? null;

        $value = get_config('local_mobilemonitor', $name);

        return ($value === false) ? $default : $value;
    }

    /**
     * Whether the monitoring of the tokens is enabled.
     *
     * @return bool
     */
    public static function is_monitoring_enabled(): bool {
        return (bool) self::get_setting('enabled');
    }

    /**
     * Whether the administrative notifications are enabled.
     *
     * @return bool
     */
    public static function are_notifications_enabled(): bool {
        return (bool) self::get_setting('notificationsenabled');
    }

    /**
     * Whether the detection of repetitive activity is enabled.
     *
     * @return bool
     */
    public static function is_repetition_detection_enabled(): bool {
        return (bool) self::get_setting('alertenabled');
    }

    /**
     * Shortnames of the external services that must be monitored.
     *
     * The real identifier of the service is its shortname. The Moodle Mobile
     * service is identified as MOODLE_OFFICIAL_MOBILE_SERVICE ('moodle_mobile_app'),
     * never by its display name, so custom services named like the official one
     * are not monitored by mistake.
     *
     * @return string[]
     */
    public static function get_monitored_shortnames(): array {
        $raw = (string) self::get_setting('serviceshortnames');

        $shortnames = preg_split('/[\s,;]+/', trim($raw));
        $result = [];
        foreach ($shortnames as $shortname) {
            if ($shortname !== '') {
                $result[] = $shortname;
            }
        }

        if (empty($result)) {
            $result = [MOODLE_OFFICIAL_MOBILE_SERVICE];
        }

        return array_values(array_unique($result));
    }

    /**
     * Identifiers of the external services that must be monitored.
     *
     * @return int[]
     */
    public static function get_monitored_service_ids(): array {
        global $DB;

        $shortnames = self::get_monitored_shortnames();
        if (empty($shortnames)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED, 'shortname');
        $ids = $DB->get_fieldset_select('external_services', 'id', "shortname $insql", $params);

        return array_map('intval', $ids);
    }

    /**
     * Whether the given external service is monitored by the plugin.
     *
     * @param int $externalserviceid Identifier of the external service.
     * @return bool
     */
    public static function is_monitored_service(int $externalserviceid): bool {
        return in_array($externalserviceid, self::get_monitored_service_ids(), true);
    }

    /**
     * Returns the monitored services, ready to be used in a select element.
     *
     * @return array[] List of ['id' => int, 'name' => string] entries.
     */
    public static function get_monitored_services(): array {
        global $DB;

        $shortnames = self::get_monitored_shortnames();
        if (empty($shortnames)) {
            return [];
        }

        list($insql, $params) = $DB->get_in_or_equal($shortnames, SQL_PARAMS_NAMED, 'shortname');
        $services = $DB->get_records_select('external_services', "shortname $insql", $params, 'name ASC');

        $result = [];
        foreach ($services as $service) {
            $result[] = [
                'id' => (int) $service->id,
                'name' => (string) $service->name,
                'shortname' => (string) $service->shortname,
            ];
        }

        return $result;
    }

    /**
     * Returns the shortname of an external service.
     *
     * @param int $externalserviceid Identifier of the external service.
     * @return string Empty string when the service does not exist anymore.
     */
    public static function get_service_shortname(int $externalserviceid): string {
        global $DB;

        if (empty($externalserviceid)) {
            return '';
        }

        return (string) $DB->get_field('external_services', 'shortname', ['id' => $externalserviceid], IGNORE_MISSING);
    }

    /**
     * Returns the display name of an external service.
     *
     * @param int $externalserviceid Identifier of the external service.
     * @return string Empty string when the service does not exist anymore.
     */
    public static function get_service_name(int $externalserviceid): string {
        global $DB;

        if (empty($externalserviceid)) {
            return '';
        }

        return (string) $DB->get_field('external_services', 'name', ['id' => $externalserviceid], IGNORE_MISSING);
    }

    /**
     * Returns the IP address of the current request, when it is technically reliable.
     *
     * @return string
     */
    public static function get_request_ip(): string {
        $ip = getremoteaddr();

        if (empty($ip) || !is_string($ip)) {
            return '';
        }

        // Keep it short and free of control characters, it is only metadata.
        $ip = preg_replace('/[\x00-\x1F\x7F]/', '', $ip);

        return substr((string) $ip, 0, 45);
    }

    /**
     * Registers an event about a web service token.
     *
     * The operation is idempotent: only one row per token and event type is stored.
     *
     * @param int $tokenid Identifier of the row in {external_tokens}.
     * @param string $eventtype One of the EVENTTYPE_* constants.
     * @param \stdClass|null $token The token record, when it is already available.
     * @param string|null $ipaddress IP address of the request, when known.
     * @return \stdClass|null The stored record, with an extra "isnew" flag, or null when
     *                       the token does not belong to a monitored service.
     */
    public static function record_token_event(
        int $tokenid,
        string $eventtype,
        ?\stdClass $token = null,
        ?string $ipaddress = null
    ): ?\stdClass {
        global $DB;

        if (!self::is_monitoring_enabled()) {
            return null;
        }

        $validtypes = [self::EVENTTYPE_CREATED, self::EVENTTYPE_USED, self::EVENTTYPE_DETECTED];
        if (!in_array($eventtype, $validtypes, true)) {
            throw new \coding_exception('Invalid token event type: ' . $eventtype);
        }

        if ($token === null) {
            $token = $DB->get_record('external_tokens', ['id' => $tokenid], '*', IGNORE_MISSING);
        }

        if (empty($token)) {
            return null;
        }

        $externalserviceid = (int) $token->externalserviceid;
        if (!self::is_monitored_service($externalserviceid)) {
            // The token belongs to a service that is not monitored.
            return null;
        }

        $now = time();
        $lastaccess = empty($token->lastaccess) ? 0 : (int) $token->lastaccess;
        $ip = ($ipaddress === null) ? self::get_request_ip() : $ipaddress;

        $existing = $DB->get_record('local_mobilemonitor_log', [
            'tokenid' => $tokenid,
            'eventtype' => $eventtype,
        ]);

        if ($existing) {
            $existing->tokenlastaccess = max((int) $existing->tokenlastaccess, $lastaccess);
            if (empty($existing->ipaddress)) {
                $existing->ipaddress = $ip;
            }
            $existing->timemodified = $now;
            $DB->update_record('local_mobilemonitor_log', $existing);
            $existing->isnew = false;

            return $existing;
        }

        $record = new \stdClass();
        $record->userid = (int) $token->userid;
        $record->tokenid = $tokenid;
        $record->externalserviceid = $externalserviceid;
        $record->eventtype = $eventtype;
        $record->ipaddress = $ip;
        $record->tokenlastaccess = $lastaccess;
        $record->timecreated = ($eventtype === self::EVENTTYPE_USED) ? $now : (int) $token->timecreated;
        $record->timemodified = $now;
        $record->id = $DB->insert_record('local_mobilemonitor_log', $record);
        $record->isnew = true;

        return $record;
    }

    /**
     * Evaluates whether a user generated an abnormal amount of tokens.
     *
     * Repeated generation of tokens is classified as an activity that requires a human
     * review, never as a confirmed attack.
     *
     * @param int $userid Identifier of the user.
     * @return \stdClass|null The created alert, or null when there is nothing to alert about.
     */
    public static function evaluate_repetition(int $userid): ?\stdClass {
        global $DB;

        if (!self::is_repetition_detection_enabled() || empty($userid)) {
            return null;
        }

        $threshold = max(1, (int) self::get_setting('threshold'));
        $period = max(1, (int) self::get_setting('periodhours')) * HOURSECS;
        $since = time() - $period;

        list($insql, $inparams) = $DB->get_in_or_equal(
            [self::EVENTTYPE_CREATED, self::EVENTTYPE_DETECTED],
            SQL_PARAMS_NAMED,
            'etype'
        );
        $params = $inparams + ['userid' => $userid, 'since' => $since];

        $count = $DB->count_records_select(
            'local_mobilemonitor_log',
            "userid = :userid AND eventtype $insql AND timecreated >= :since",
            $params
        );

        if ($count < $threshold) {
            return null;
        }

        // One alert per user, threshold and evaluation window, so the same incident
        // does not generate duplicated alerts.
        $fingerprint = $userid . ':' . $threshold . ':' . (int) floor(time() / $period);

        if ($DB->record_exists('local_mobilemonitor_alert', [
            'userid' => $userid,
            'alerttype' => self::ALERT_REPETITION,
            'fingerprint' => $fingerprint,
        ])) {
            return null;
        }

        $now = time();
        $alert = new \stdClass();
        $alert->userid = $userid;
        $alert->alerttype = self::ALERT_REPETITION;
        $alert->fingerprint = $fingerprint;
        $alert->tokencount = $count;
        $alert->timecreated = $now;
        $alert->timemodified = $now;
        $alert->id = $DB->insert_record('local_mobilemonitor_alert', $alert);

        return $alert;
    }

    /**
     * Registers a mobile access of a token.
     *
     * The access history is grouped per session: consecutive accesses of the same token
     * from the same IP within {@see self::ACCESS_THROTTLE} seconds do not create new rows,
     * but a change of IP always creates a new one. The last access is still tracked by
     * Moodle in {external_tokens}.lastaccess.
     *
     * @param int $tokenid Identifier of the row in {external_tokens}.
     * @param \stdClass|null $token The token record, when it is already available.
     * @param string|null $ipaddress IP address of the request, when known.
     * @return \stdClass|null The stored access, or null when the token is not monitored.
     */
    public static function record_token_access(
        int $tokenid,
        ?\stdClass $token = null,
        ?string $ipaddress = null
    ): ?\stdClass {
        global $DB;

        if (!self::is_monitoring_enabled()) {
            return null;
        }

        if ($token === null) {
            $token = $DB->get_record('external_tokens', ['id' => $tokenid], '*', IGNORE_MISSING);
        }

        if (empty($token)) {
            return null;
        }

        $externalserviceid = (int) $token->externalserviceid;
        if (!self::is_monitored_service($externalserviceid)) {
            return null;
        }

        $now = time();
        $ip = ($ipaddress === null) ? self::get_request_ip() : $ipaddress;

        $lastaccess = $DB->get_records_sql(
            "SELECT id, ipaddress, timecreated
               FROM {local_mobilemonitor_access}
              WHERE tokenid = :tokenid
           ORDER BY timecreated DESC, id DESC",
            ['tokenid' => $tokenid],
            0,
            1
        );

        if (!empty($lastaccess)) {
            $last = reset($lastaccess);
            if ((string) $last->ipaddress === $ip && ($now - (int) $last->timecreated) < self::ACCESS_THROTTLE) {
                // Same session: no new row is needed.
                return $last;
            }
        }

        $record = new \stdClass();
        $record->userid = (int) $token->userid;
        $record->tokenid = $tokenid;
        $record->externalserviceid = $externalserviceid;
        $record->ipaddress = $ip;
        $record->timecreated = $now;
        $record->id = $DB->insert_record('local_mobilemonitor_access', $record);

        return $record;
    }

    /**
     * Timestamp that separates a recent usage from an inactive one.
     *
     * @return int
     */
    public static function get_inactivity_cutoff(): int {
        $days = max(1, (int) self::get_setting('inactivitydays'));

        return time() - ($days * DAYSECS);
    }
}
