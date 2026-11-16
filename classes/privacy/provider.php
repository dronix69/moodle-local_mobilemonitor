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

namespace local_mobilemonitor\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider of the Mobile Token Monitor plugin.
 *
 * The plugin only stores metadata about the tokens: identifiers, service, timestamps and
 * the IP address of the request. The token secret and the private token are never stored.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements core_userlist_provider, metadata_provider, plugin_provider {
    /**
     * Describes the personal data stored by the plugin.
     *
     * @param collection $collection Collection of the metadata.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_mobilemonitor_log', [
            'userid' => 'privacy:metadata:local_mobilemonitor_log:userid',
            'tokenid' => 'privacy:metadata:local_mobilemonitor_log:tokenid',
            'externalserviceid' => 'privacy:metadata:local_mobilemonitor_log:externalserviceid',
            'eventtype' => 'privacy:metadata:local_mobilemonitor_log:eventtype',
            'ipaddress' => 'privacy:metadata:local_mobilemonitor_log:ipaddress',
            'tokenlastaccess' => 'privacy:metadata:local_mobilemonitor_log:tokenlastaccess',
            'timecreated' => 'privacy:metadata:local_mobilemonitor_log:timecreated',
            'timemodified' => 'privacy:metadata:local_mobilemonitor_log:timemodified',
        ], 'privacy:metadata:local_mobilemonitor_log');

        $collection->add_database_table('local_mobilemonitor_access', [
            'userid' => 'privacy:metadata:local_mobilemonitor_access:userid',
            'tokenid' => 'privacy:metadata:local_mobilemonitor_access:tokenid',
            'externalserviceid' => 'privacy:metadata:local_mobilemonitor_access:externalserviceid',
            'ipaddress' => 'privacy:metadata:local_mobilemonitor_access:ipaddress',
            'timecreated' => 'privacy:metadata:local_mobilemonitor_access:timecreated',
        ], 'privacy:metadata:local_mobilemonitor_access');

        $collection->add_database_table('local_mobilemonitor_alert', [
            'userid' => 'privacy:metadata:local_mobilemonitor_alert:userid',
            'alerttype' => 'privacy:metadata:local_mobilemonitor_alert:alerttype',
            'fingerprint' => 'privacy:metadata:local_mobilemonitor_alert:fingerprint',
            'tokencount' => 'privacy:metadata:local_mobilemonitor_alert:tokencount',
            'timecreated' => 'privacy:metadata:local_mobilemonitor_alert:timecreated',
            'timemodified' => 'privacy:metadata:local_mobilemonitor_alert:timemodified',
        ], 'privacy:metadata:local_mobilemonitor_alert');

        return $collection;
    }

    /**
     * Returns the contexts that contain personal data of the given user.
     *
     * The plugin works at system level, so the system context is always returned.
     *
     * @param int $userid Identifier of the user.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();

        $hasdata = $DB->record_exists('local_mobilemonitor_log', ['userid' => $userid])
            || $DB->record_exists('local_mobilemonitor_access', ['userid' => $userid])
            || $DB->record_exists('local_mobilemonitor_alert', ['userid' => $userid]);

        if ($hasdata) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Returns the users that have personal data in the given context.
     *
     * @param userlist $userlist List of users.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $sql = "SELECT userid FROM {local_mobilemonitor_log}";
        $userlist->add_from_sql('userid', $sql, []);

        $sql = "SELECT userid FROM {local_mobilemonitor_access}";
        $userlist->add_from_sql('userid', $sql, []);

        $sql = "SELECT userid FROM {local_mobilemonitor_alert}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Exports the personal data of the given user.
     *
     * @param approved_contextlist $contextlist Contexts of the user.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();
        if (empty($user)) {
            return;
        }

        $context = \context_system::instance();
        $subcontext = [get_string('pluginname', 'local_mobilemonitor')];

        $logs = $DB->get_records('local_mobilemonitor_log', ['userid' => $user->id], 'timecreated ASC');
        $logdata = [];
        foreach ($logs as $log) {
            $logdata[] = (object) [
                'tokenid' => (int) $log->tokenid,
                'externalserviceid' => (int) $log->externalserviceid,
                'eventtype' => (string) $log->eventtype,
                'ipaddress' => (string) $log->ipaddress,
                'tokenlastaccess' => empty($log->tokenlastaccess)
                    ? ''
                    : transform::datetime((int) $log->tokenlastaccess),
                'timecreated' => transform::datetime((int) $log->timecreated),
                'timemodified' => transform::datetime((int) $log->timemodified),
            ];
        }

        if (!empty($logdata)) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:logs', 'local_mobilemonitor')],
                (object) ['logs' => $logdata]
            );
        }

        $accesses = $DB->get_records('local_mobilemonitor_access', ['userid' => $user->id], 'timecreated ASC');
        $accessdata = [];
        foreach ($accesses as $access) {
            $accessdata[] = (object) [
                'tokenid' => (int) $access->tokenid,
                'externalserviceid' => (int) $access->externalserviceid,
                'ipaddress' => (string) $access->ipaddress,
                'timecreated' => transform::datetime((int) $access->timecreated),
            ];
        }

        if (!empty($accessdata)) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:accesses', 'local_mobilemonitor')],
                (object) ['accesses' => $accessdata]
            );
        }

        $alerts = $DB->get_records('local_mobilemonitor_alert', ['userid' => $user->id], 'timecreated ASC');
        $alertdata = [];
        foreach ($alerts as $alert) {
            $alertdata[] = (object) [
                'alerttype' => (string) $alert->alerttype,
                'tokencount' => (int) $alert->tokencount,
                'timecreated' => transform::datetime((int) $alert->timecreated),
                'timemodified' => transform::datetime((int) $alert->timemodified),
            ];
        }

        if (!empty($alertdata)) {
            writer::with_context($context)->export_data(
                [get_string('privacy:path:alerts', 'local_mobilemonitor')],
                (object) ['alerts' => $alertdata]
            );
        }
    }

    /**
     * Deletes the personal data of all the users in the given context.
     *
     * @param \context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $DB->delete_records('local_mobilemonitor_log', []);
        $DB->delete_records('local_mobilemonitor_access', []);
        $DB->delete_records('local_mobilemonitor_alert', []);
    }

    /**
     * Deletes the personal data of the given user.
     *
     * @param approved_contextlist $contextlist Contexts of the user.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();
        if (empty($user)) {
            return;
        }

        $DB->delete_records('local_mobilemonitor_log', ['userid' => $user->id]);
        $DB->delete_records('local_mobilemonitor_access', ['userid' => $user->id]);
        $DB->delete_records('local_mobilemonitor_alert', ['userid' => $user->id]);
    }

    /**
     * Deletes the personal data of the given users.
     *
     * @param approved_userlist $userlist Users.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'uid');
        $DB->delete_records_select('local_mobilemonitor_log', "userid $insql", $params);
        $DB->delete_records_select('local_mobilemonitor_access', "userid $insql", $params);
        $DB->delete_records_select('local_mobilemonitor_alert', "userid $insql", $params);
    }
}
