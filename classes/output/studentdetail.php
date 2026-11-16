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

namespace local_mobilemonitor\output;

use local_mobilemonitor\local\helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderable with the mobile activity detail of a single student.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class studentdetail implements \renderable, \templatable {

    /**
     * @param \stdClass $student Summary of the student.
     * @param \stdClass[] $tokens Tokens of the student.
     * @param \stdClass[] $ips IP addresses seen for the student.
     * @param \stdClass[] $accesses Access history page.
     * @param int $totalaccesses Total number of access rows.
     */
    public function __construct(
        protected \stdClass $student,
        protected array $tokens,
        protected array $ips,
        protected array $accesses,
        protected int $totalaccesses
    ) {
    }

    /**
     * Prepares the data for the template.
     *
     * @param \renderer_base $output Renderer.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $now = time();
        $cutoff = helper::get_inactivity_cutoff();

        $lastactivity = (int) ($this->student->lastactivity ?? 0);
        $isactive = $lastactivity >= $cutoff;

        $summary = [
            'fullname' => fullname((object) [
                'firstname' => $this->student->firstname ?? '',
                'lastname' => $this->student->lastname ?? '',
                'firstnamephonetic' => $this->student->firstnamephonetic ?? '',
                'lastnamephonetic' => $this->student->lastnamephonetic ?? '',
                'middlename' => $this->student->middlename ?? '',
                'alternatename' => $this->student->alternatename ?? '',
            ]),
            'username' => (string) ($this->student->username ?? ''),
            'profileurl' => (new \moodle_url('/user/view.php',
                ['id' => (int) $this->student->userid]))->out(false),
            'firsttoken' => empty($this->student->firsttoken)
                ? get_string('notavailable', 'local_mobilemonitor')
                : userdate((int) $this->student->firsttoken),
            'firstaccess' => empty($this->student->firstaccess)
                ? get_string('notavailable', 'local_mobilemonitor')
                : userdate((int) $this->student->firstaccess),
            'lastaccess' => empty($this->student->lastaccess)
                ? get_string('notavailable', 'local_mobilemonitor')
                : userdate((int) $this->student->lastaccess),
            'tokens' => (int) ($this->student->tokens ?? 0),
            'activetokens' => (int) ($this->student->activetokens ?? 0),
            'ipcount' => (int) ($this->student->ipcount ?? 0),
            'status' => $isactive
                ? get_string('status_active', 'local_mobilemonitor')
                : get_string('status_inactive', 'local_mobilemonitor'),
            'statusclass' => $isactive ? 'badge bg-success' : 'badge bg-secondary',
            'hasmultipleips' => (int) ($this->student->ipcount ?? 0) > 1,
            'hasmultipletokens' => (int) ($this->student->activetokens ?? 0) > 1,
        ];

        $tokenrows = [];
        foreach ($this->tokens as $token) {
            $lastaccess = (int) ($token->lastaccess ?? 0);

            if ($lastaccess === 0) {
                $state = get_string('token_neverused', 'local_mobilemonitor');
                $stateclass = 'badge bg-warning text-dark';
                $lastaccesstext = get_string('notavailable', 'local_mobilemonitor');
            } else if ($lastaccess >= $cutoff) {
                $state = get_string('status_active', 'local_mobilemonitor');
                $stateclass = 'badge bg-success';
                $lastaccesstext = userdate($lastaccess);
            } else {
                $state = get_string('status_inactive', 'local_mobilemonitor');
                $stateclass = 'badge bg-secondary';
                $lastaccesstext = userdate($lastaccess);
            }

            $validuntil = (int) ($token->validuntil ?? 0);
            if ($validuntil === 0) {
                $validext = get_string('token_validunlimited', 'local_mobilemonitor');
            } else if ($validuntil < $now) {
                $validext = get_string('token_expired', 'local_mobilemonitor', userdate($validuntil));
            } else {
                $validext = userdate($validuntil);
            }

            $tokenrows[] = [
                'id' => (int) $token->id,
                'service' => (string) ($token->servicename ?? ''),
                'shortname' => (string) ($token->serviceshortname ?? ''),
                'created' => userdate((int) $token->timecreated),
                'lastaccess' => $lastaccesstext,
                'validuntil' => $validext,
                'state' => $state,
                'stateclass' => $stateclass,
                'creationips' => implode(', ', $token->creationips ?? []) ?: get_string('notavailable', 'local_mobilemonitor'),
            ];
        }

        $iprows = [];
        foreach ($this->ips as $ip) {
            $iprows[] = [
                'ipaddress' => (string) $ip->ipaddress,
                'hits' => (int) $ip->hits,
                'firstseen' => userdate((int) $ip->firstseen),
                'lastseen' => userdate((int) $ip->lastseen),
            ];
        }

        $accessrows = [];
        foreach ($this->accesses as $access) {
            $accessrows[] = [
                'timecreated' => userdate((int) $access->timecreated),
                'ipaddress' => empty($access->ipaddress)
                    ? get_string('notavailable', 'local_mobilemonitor')
                    : (string) $access->ipaddress,
                'service' => empty($access->servicename)
                    ? get_string('unknownservice', 'local_mobilemonitor')
                    : (string) $access->servicename,
                'tokenid' => (int) $access->tokenid,
            ];
        }

        return [
            'summary' => $summary,
            'hasmultipleips' => $summary['hasmultipleips'],
            'hasmultipletokens' => $summary['hasmultipletokens'],
            'tokens' => $tokenrows,
            'hastokens' => !empty($tokenrows),
            'ips' => $iprows,
            'hasips' => !empty($iprows),
            'accesses' => $accessrows,
            'hasaccesses' => !empty($accessrows),
            'totalaccesses' => $this->totalaccesses,
        ];
    }
}
