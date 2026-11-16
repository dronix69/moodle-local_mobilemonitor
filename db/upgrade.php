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

/**
 * Upgrade steps for the Mobile Token Monitor plugin.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin database.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_local_mobilemonitor_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026111501) {
        // Table storing the audit trail of the monitored tokens.
        $table = new xmldb_table('local_mobilemonitor_log');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Owner of the token (the student that generated it)');
        $table->add_field('tokenid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Identifier of the row in {external_tokens}. It is NOT the token secret.');
        $table->add_field('externalserviceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Identifier of the row in {external_services}');
        $table->add_field('eventtype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'created',
            'created | used | detected');
        $table->add_field('ipaddress', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, '',
            'IP address of the request that generated the token, when it is technically reliable');
        $table->add_field('tokenlastaccess', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Last known access time of the token');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('tokenid-eventtype', XMLDB_INDEX_UNIQUE, ['tokenid', 'eventtype'],
            'Guarantees idempotency: one row per token and event type');
        $table->add_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('externalserviceid', XMLDB_INDEX_NOTUNIQUE, ['externalserviceid']);
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Table storing the alerts that require a human review.
        $table = new xmldb_table('local_mobilemonitor_alert');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'User the alert refers to');
        $table->add_field('alerttype', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, 'repetition');
        $table->add_field('fingerprint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '',
            'Identifier of the incident, used to avoid duplicated alerts');
        $table->add_field('tokencount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('userid-alerttype-fingerprint', XMLDB_INDEX_UNIQUE,
            ['userid', 'alerttype', 'fingerprint'], 'One alert per incident');
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026111501, 'local', 'mobilemonitor');
    }

    if ($oldversion < 2026111601) {
        // Grouped access history used by the per-student audit.
        $table = new xmldb_table('local_mobilemonitor_access');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Owner of the token');
        $table->add_field('tokenid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Identifier of the row in {external_tokens}. It is NOT the token secret.');
        $table->add_field('externalserviceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Identifier of the row in {external_services}');
        $table->add_field('ipaddress', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, '',
            'IP address of the request');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0',
            'Time of the access (grouped by session/IP)');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('userid-timecreated', XMLDB_INDEX_NOTUNIQUE, ['userid', 'timecreated']);
        $table->add_index('tokenid-timecreated', XMLDB_INDEX_NOTUNIQUE, ['tokenid', 'timecreated']);
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026111601, 'local', 'mobilemonitor');
    }

    return true;
}
