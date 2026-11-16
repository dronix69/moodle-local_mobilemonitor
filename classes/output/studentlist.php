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

/**
 * Renderable listing of the students with mobile activity.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class studentlist implements \renderable, \templatable {
    /**
     * Constructor.
     *
     * @param \stdClass[] $students Students with mobile activity.
     * @param bool $hasfilters Whether any filter is applied.
     */
    public function __construct(
        /** @var \stdClass[] $students Students with mobile activity. */
        protected array $students,
        /** @var bool $hasfilters Whether any filter is applied. */
        protected bool $hasfilters,
    ) {
    }

    /**
     * Prepares the data for the template.
     *
     * @param \renderer_base $output Renderer.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $cutoff = helper::get_inactivity_cutoff();
        $rows = [];

        foreach ($this->students as $student) {
            $lastactivity = (int) ($student->lastactivity ?? 0);
            $isactive = $lastactivity >= $cutoff;

            $name = fullname((object) [
                'firstname' => $student->firstname ?? '',
                'lastname' => $student->lastname ?? '',
                'firstnamephonetic' => $student->firstnamephonetic ?? '',
                'lastnamephonetic' => $student->lastnamephonetic ?? '',
                'middlename' => $student->middlename ?? '',
                'alternatename' => $student->alternatename ?? '',
            ]);

            $rows[] = [
                'userid' => (int) $student->userid,
                'fullname' => $name,
                'username' => (string) ($student->username ?? ''),
                'detailurl' => (new \moodle_url(
                    '/local/mobilemonitor/student.php',
                    ['userid' => (int) $student->userid]
                ))->out(false),
                'tokens' => (int) ($student->tokens ?? 0),
                'activetokens' => (int) ($student->activetokens ?? 0),
                'ipcount' => (int) ($student->ipcount ?? 0),
                'firstuse' => empty($student->firstaccess)
                    ? get_string('notavailable', 'local_mobilemonitor')
                    : userdate((int) $student->firstaccess),
                'lastaccess' => empty($student->lastaccess)
                    ? get_string('notavailable', 'local_mobilemonitor')
                    : userdate((int) $student->lastaccess),
                'status' => $isactive
                    ? get_string('status_active', 'local_mobilemonitor')
                    : get_string('status_inactive', 'local_mobilemonitor'),
                'statusclass' => $isactive ? 'badge bg-success' : 'badge bg-secondary',
            ];
        }

        return [
            'rows' => $rows,
            'hasrows' => !empty($rows),
            'hasfilters' => $this->hasfilters,
        ];
    }
}
