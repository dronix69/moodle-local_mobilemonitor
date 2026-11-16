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

/**
 * Renderable of the audit panel.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard implements \renderable, \templatable {
    /**
     * Constructor.
     *
     * @param array $stats Summary figures of the panel.
     * @param \stdClass[] $records Records of the audit log.
     * @param bool $hasfilters Whether any filter is applied.
     */
    public function __construct(
        /** @var array $stats Summary figures of the panel. */
        protected array $stats,
        /** @var \stdClass[] $records Records of the audit log. */
        protected array $records,
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
        $rows = [];

        foreach ($this->records as $record) {
            $student = get_string('unknownuser', 'local_mobilemonitor');
            $username = '';
            if (!empty($record->firstname) || !empty($record->lastname)) {
                $student = fullname((object) [
                    'firstname' => $record->firstname ?? '',
                    'lastname' => $record->lastname ?? '',
                    'firstnamephonetic' => $record->firstnamephonetic ?? '',
                    'lastnamephonetic' => $record->lastnamephonetic ?? '',
                    'middlename' => $record->middlename ?? '',
                    'alternatename' => $record->alternatename ?? '',
                ]);
            }
            if (!empty($record->username)) {
                $username = $record->username;
            }

            $servicename = get_string('unknownservice', 'local_mobilemonitor');
            if (!empty($record->servicename)) {
                $servicename = $record->servicename;
            }

            $rows[] = [
                'id' => (int) $record->id,
                'student' => $student,
                'username' => $username,
                'service' => $servicename,
                'created' => userdate((int) $record->timecreated),
                'lastaccess' => empty($record->tokenlastaccess)
                    ? get_string('notavailable', 'local_mobilemonitor')
                    : userdate((int) $record->tokenlastaccess),
                'ipaddress' => empty($record->ipaddress)
                    ? get_string('notavailable', 'local_mobilemonitor')
                    : (string) $record->ipaddress,
                'eventtype' => get_string('eventtype_' . $record->eventtype, 'local_mobilemonitor'),
            ];
        }

        $lastactivity = 0;
        if (!empty($this->stats['lastactivity'])) {
            $lastactivity = userdate((int) $this->stats['lastactivity']);
        } else {
            $lastactivity = get_string('notavailable', 'local_mobilemonitor');
        }

        return [
            'stats' => [
                'total' => (int) ($this->stats['total'] ?? 0),
                'students' => (int) ($this->stats['students'] ?? 0),
                'last24hours' => (int) ($this->stats['last24hours'] ?? 0),
                'last7days' => (int) ($this->stats['last7days'] ?? 0),
                'lastactivity' => $lastactivity,
            ],
            'rows' => $rows,
            'hasrows' => !empty($rows),
            'hasfilters' => $this->hasfilters,
        ];
    }
}
