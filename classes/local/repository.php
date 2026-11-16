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

/**
 * Repository with the queries used by the audit panel.
 *
 * All the queries use the Moodle DML layer with bound parameters, no SQL is built
 * with the values received from the user.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository {
    /**
     * Builds the conditions of the listing, according to the filters.
     *
     * @param array $filters Filters of the listing.
     * @return array [string $sql, array $params]
     */
    protected static function build_where(array $filters): array {
        global $DB;

        $conditions = ['1 = 1'];
        $params = [];

        if (!empty($filters['studentname'])) {
            $value = '%' . $DB->sql_like_escape((string) $filters['studentname']) . '%';
            $conditions[] = '(' . $DB->sql_like('u.firstname', ':pfirst', false, false) . ' OR ' .
                $DB->sql_like('u.lastname', ':plast', false, false) . ')';
            $params['pfirst'] = $value;
            $params['plast'] = $value;
        }

        if (!empty($filters['username'])) {
            $params['pusername'] = '%' . $DB->sql_like_escape((string) $filters['username']) . '%';
            $conditions[] = $DB->sql_like('u.username', ':pusername', false, false);
        }

        if (!empty($filters['eventtype'])) {
            $conditions[] = 'l.eventtype = :peventtype';
            $params['peventtype'] = (string) $filters['eventtype'];
        }

        if (!empty($filters['externalserviceid'])) {
            $conditions[] = 'l.externalserviceid = :pserviceid';
            $params['pserviceid'] = (int) $filters['externalserviceid'];
        }

        if (!empty($filters['fromdate'])) {
            $conditions[] = 'l.timecreated >= :pfromdate';
            $params['pfromdate'] = (int) $filters['fromdate'];
        }

        if (!empty($filters['todate'])) {
            $conditions[] = 'l.timecreated <= :ptodate';
            $params['ptodate'] = (int) $filters['todate'];
        }

        return [implode(' AND ', $conditions), $params];
    }

    /**
     * Counts the records that match the filters.
     *
     * @param array $filters Filters of the listing.
     * @return int
     */
    public static function count_events(array $filters): int {
        global $DB;

        [$where, $params] = self::build_where($filters);

        $sql = "SELECT COUNT(l.id)
                  FROM {local_mobilemonitor_log} l
             LEFT JOIN {user} u ON u.id = l.userid
                 WHERE $where";

        return (int) $DB->count_records_sql($sql, $params);
    }

    /**
     * Returns the records that match the filters.
     *
     * @param array $filters Filters of the listing.
     * @param int $offset Number of records to skip.
     * @param int $limit Maximum number of records, 0 for no limit.
     * @return \stdClass[]
     */
    public static function get_events(array $filters, int $offset = 0, int $limit = 0): array {
        global $DB;

        [$where, $params] = self::build_where($filters);

        $sql = "SELECT l.id, l.userid, l.tokenid, l.externalserviceid, l.eventtype, l.ipaddress,
                       l.tokenlastaccess, l.timecreated, l.timemodified,
                       u.username, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename,
                       s.name AS servicename, s.shortname AS serviceshortname
                  FROM {local_mobilemonitor_log} l
             LEFT JOIN {user} u ON u.id = l.userid
             LEFT JOIN {external_services} s ON s.id = l.externalserviceid
                 WHERE $where
              ORDER BY l.timecreated DESC, l.id DESC";

        if ($limit > 0) {
            return $DB->get_records_sql($sql, $params, $offset, $limit);
        }

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns the summary figures of the audit panel.
     *
     * @param array $filters Filters of the listing.
     * @return array
     */
    public static function get_stats(array $filters): array {
        global $DB;

        [$where, $params] = self::build_where($filters);

        $newsql = "SELECT l.id, l.userid, l.timecreated, l.eventtype, l.tokenlastaccess, l.timemodified
                      FROM {local_mobilemonitor_log} l
                 LEFT JOIN {user} u ON u.id = l.userid
                     WHERE $where";

        $total = 0;
        $students = [];
        $last24 = 0;
        $last7 = 0;
        $lastactivity = 0;

        $now = time();
        $records = $DB->get_records_sql($newsql, $params);

        foreach ($records as $record) {
            $lastactivity = max($lastactivity, (int) $record->timemodified, (int) $record->tokenlastaccess);

            // Only the registration of a token is counted as a new token.
            if (!in_array($record->eventtype, [helper::EVENTTYPE_CREATED, helper::EVENTTYPE_DETECTED], true)) {
                continue;
            }

            $total++;
            $students[(int) $record->userid] = true;

            if ((int) $record->timecreated >= $now - DAYSECS) {
                $last24++;
            }
            if ((int) $record->timecreated >= $now - (7 * DAYSECS)) {
                $last7++;
            }
        }

        return [
            'total' => $total,
            'students' => count($students),
            'last24hours' => $last24,
            'last7days' => $last7,
            'lastactivity' => $lastactivity,
        ];
    }

    /**
     * Builds the aggregated query of the students with mobile activity.
     *
     * @param array $filters Filters: studentname, username, status, userid.
     * @param bool $count Whether the query must count the rows instead of returning them.
     * @return array [string $sql, array $params]
     */
    protected static function build_students_query(array $filters, bool $count = false): array {
        global $DB;

        $serviceids = helper::get_monitored_service_ids();
        if (empty($serviceids)) {
            return ['', []];
        }

        [$servicesql, $params] = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED, 'studentsvc');

        $conditions = [
            "l.eventtype IN ('created', 'detected')",
            'u.deleted = 0',
            'l.userid > 0',
        ];

        if (!empty($filters['studentname'])) {
            $value = '%' . $DB->sql_like_escape((string) $filters['studentname']) . '%';
            $conditions[] = '(' . $DB->sql_like('u.firstname', ':pfirst', false, false) . ' OR ' .
                $DB->sql_like('u.lastname', ':plast', false, false) . ')';
            $params['pfirst'] = $value;
            $params['plast'] = $value;
        }

        if (!empty($filters['username'])) {
            $params['pusername'] = '%' . $DB->sql_like_escape((string) $filters['username']) . '%';
            $conditions[] = $DB->sql_like('u.username', ':pusername', false, false);
        }

        if (!empty($filters['userid'])) {
            $conditions[] = 'l.userid = :suserid';
            $params['suserid'] = (int) $filters['userid'];
        }

        $where = implode(' AND ', $conditions);

        // CASE WHEN is used instead of GREATEST() because the latter is not available in all
        // the database engines supported by Moodle (for example SQL Server).
        $maxlastaccess = 'COALESCE(MAX(l.tokenlastaccess), 0)';
        $accesslastaccess = 'COALESCE(acc.lastaccess, 0)';
        $lastactivitysql = "CASE WHEN $maxlastaccess > $accesslastaccess
                                  THEN $maxlastaccess
                                  ELSE $accesslastaccess END";

        $having = '';
        $status = (string) ($filters['status'] ?? '');
        if ($status === 'active' || $status === 'inactive') {
            $having = ' HAVING ' . $lastactivitysql
                . ($status === 'active' ? ' >= ' : ' < ') . ':cutoff';
            $params['cutoff'] = helper::get_inactivity_cutoff();
        }

        $groupby = 'l.userid, u.username, u.firstname, u.lastname, u.firstnamephonetic,
                    u.lastnamephonetic, u.middlename, u.alternatename,
                    acc.firstaccess, acc.lastaccess, act.activetokens';

        $fields = 'l.userid, u.username, u.firstname, u.lastname, u.firstnamephonetic,
                   u.lastnamephonetic, u.middlename, u.alternatename';

        $sql = "SELECT $fields,
                       COUNT(DISTINCT l.tokenid) AS tokens,
                       MIN(l.timecreated) AS firsttoken,
                       MAX(l.tokenlastaccess) AS tokenlastaccess,
                       COALESCE(acc.firstaccess, 0) AS firstaccess,
                       COALESCE(acc.lastaccess, 0) AS lastaccess,
                       COALESCE(act.activetokens, 0) AS activetokens,
                       $lastactivitysql AS lastactivity
                  FROM {local_mobilemonitor_log} l
                  JOIN {user} u ON u.id = l.userid
             LEFT JOIN (SELECT userid, MIN(timecreated) AS firstaccess, MAX(timecreated) AS lastaccess
                          FROM {local_mobilemonitor_access}
                      GROUP BY userid) acc ON acc.userid = l.userid
             LEFT JOIN (SELECT t.userid, COUNT(*) AS activetokens
                          FROM {external_tokens} t
                          JOIN {external_services} s ON s.id = t.externalserviceid
                         WHERE s.shortname $servicesql AND (t.validuntil = 0 OR t.validuntil > :actnow)
                      GROUP BY t.userid) act ON act.userid = l.userid
                 WHERE $where
              GROUP BY $groupby
                 $having";

        $params['actnow'] = time();

        if ($count) {
            return ['SELECT COUNT(*) FROM (' . $sql . ') students', $params];
        }

        $sql .= ' ORDER BY lastactivity DESC, u.lastname ASC, u.firstname ASC';

        return [$sql, $params];
    }

    /**
     * Counts the students with mobile activity that match the filters.
     *
     * @param array $filters Filters of the listing.
     * @return int
     */
    public static function count_students(array $filters): int {
        global $DB;

        [$sql, $params] = self::build_students_query($filters, true);
        if ($sql === '') {
            return 0;
        }

        return (int) $DB->count_records_sql($sql, $params);
    }

    /**
     * Returns the students with mobile activity that match the filters.
     *
     * @param array $filters Filters of the listing.
     * @param int $offset Number of records to skip.
     * @param int $limit Maximum number of records, 0 for no limit.
     * @return \stdClass[]
     */
    public static function get_students(array $filters, int $offset = 0, int $limit = 0): array {
        global $DB;

        [$sql, $params] = self::build_students_query($filters);
        if ($sql === '') {
            return [];
        }

        if ($limit > 0) {
            $students = $DB->get_records_sql($sql, $params, $offset, $limit);
        } else {
            $students = $DB->get_records_sql($sql, $params);
        }

        if (empty($students)) {
            return [];
        }

        $ipcounts = self::get_ip_counts(array_keys($students));
        foreach ($students as $userid => $student) {
            $student->ipcount = $ipcounts[(int) $userid] ?? 0;
        }

        return $students;
    }

    /**
     * Returns the single student summary, or null when the user has no recorded activity.
     *
     * @param int $userid Identifier of the user.
     * @return \stdClass|null
     */
    public static function get_student(int $userid): ?\stdClass {
        $students = self::get_students(['userid' => $userid], 0, 1);
        if (empty($students)) {
            return null;
        }

        return reset($students);
    }

    /**
     * Counts the distinct IP addresses seen for each user.
     *
     * The creation IP of the audit log and the access history are merged, so an IP used
     * both to create a token and to use it is counted once.
     *
     * @param int[] $userids Identifiers of the users.
     * @return array Map userid => number of distinct IP addresses.
     */
    protected static function get_ip_counts(array $userids): array {
        global $DB;

        if (empty($userids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal(array_map('intval', $userids), SQL_PARAMS_NAMED, 'ipuser');

        $sqlaccess = "SELECT userid, ipaddress
                        FROM {local_mobilemonitor_access}
                       WHERE userid $insql AND ipaddress <> ''
                    GROUP BY userid, ipaddress";

        $sqllog = "SELECT userid, ipaddress
                     FROM {local_mobilemonitor_log}
                    WHERE userid $insql AND ipaddress <> ''
                 GROUP BY userid, ipaddress";

        $map = [];
        foreach ([$sqlaccess, $sqllog] as $sql) {
            foreach ($DB->get_records_sql($sql, $params) as $record) {
                $map[(int) $record->userid][(string) $record->ipaddress] = true;
            }
        }

        $counts = [];
        foreach ($map as $userid => $ips) {
            $counts[$userid] = count($ips);
        }

        return $counts;
    }

    /**
     * Returns the tokens of a student for the monitored services.
     *
     * @param int $userid Identifier of the user.
     * @return \stdClass[]
     */
    public static function get_student_tokens(int $userid): array {
        global $DB;

        $serviceids = helper::get_monitored_service_ids();
        if (empty($serviceids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($serviceids, SQL_PARAMS_NAMED, 'tokensvc');
        $params['tuserid'] = $userid;

        $sql = "SELECT t.id, t.timecreated, t.lastaccess, t.validuntil,
                       s.name AS servicename, s.shortname AS serviceshortname
                  FROM {external_tokens} t
                  JOIN {external_services} s ON s.id = t.externalserviceid
                 WHERE s.shortname $insql AND t.userid = :tuserid
              ORDER BY t.timecreated DESC, t.id DESC";

        // Attach the creation IP recorded by the plugin, when available.
        $ips = $DB->get_records_sql(
            "SELECT tokenid, ipaddress
               FROM {local_mobilemonitor_log}
              WHERE userid = :iuserid AND eventtype IN ('created', 'detected') AND ipaddress <> ''
           GROUP BY tokenid, ipaddress",
            ['iuserid' => $userid]
        );

        $tokenips = [];
        foreach ($ips as $ip) {
            $tokenips[(int) $ip->tokenid][] = (string) $ip->ipaddress;
        }

        $tokens = $DB->get_records_sql($sql, $params);
        foreach ($tokens as $token) {
            $token->creationips = $tokenips[(int) $token->id] ?? [];
        }

        return $tokens;
    }

    /**
     * Counts the access rows of a student.
     *
     * @param int $userid Identifier of the user.
     * @return int
     */
    public static function count_student_accesses(int $userid): int {
        global $DB;

        return $DB->count_records('local_mobilemonitor_access', ['userid' => $userid]);
    }

    /**
     * Returns the access history of a student.
     *
     * @param int $userid Identifier of the user.
     * @param int $offset Number of records to skip.
     * @param int $limit Maximum number of records, 0 for no limit.
     * @return \stdClass[]
     */
    public static function get_student_accesses(int $userid, int $offset = 0, int $limit = 0): array {
        global $DB;

        $sql = "SELECT a.id, a.tokenid, a.ipaddress, a.timecreated, s.name AS servicename
                  FROM {local_mobilemonitor_access} a
             LEFT JOIN {external_services} s ON s.id = a.externalserviceid
                 WHERE a.userid = :auserid
              ORDER BY a.timecreated DESC, a.id DESC";

        $params = ['auserid' => $userid];

        if ($limit > 0) {
            return $DB->get_records_sql($sql, $params, $offset, $limit);
        }

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns the IP addresses seen for a student, with their first and last usage.
     *
     * @param int $userid Identifier of the user.
     * @return \stdClass[]
     */
    public static function get_student_ips(int $userid): array {
        global $DB;

        $sql = "SELECT ipaddress, COUNT(*) AS hits, MIN(ts) AS firstseen, MAX(ts) AS lastseen
                  FROM (
                        SELECT ipaddress, timecreated AS ts
                          FROM {local_mobilemonitor_access}
                         WHERE userid = :auserid AND ipaddress <> ''
                        UNION ALL
                        SELECT ipaddress, timecreated AS ts
                          FROM {local_mobilemonitor_log}
                         WHERE userid = :luserid AND ipaddress <> ''
                       ) ips
              GROUP BY ipaddress
              ORDER BY lastseen DESC, ipaddress ASC";

        return $DB->get_records_sql($sql, ['auserid' => $userid, 'luserid' => $userid]);
    }
}
