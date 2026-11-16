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
 * Mobile activity detail of a single student.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_mobilemonitor\local\repository;

$context = context_system::instance();

require_login();
require_capability('local/mobilemonitor:viewlogs', $context);

$userid = required_param('userid', PARAM_INT);
$user = core_user::get_user($userid, '*', IGNORE_MISSING);

if (empty($user) || !empty($user->deleted)) {
    throw new moodle_exception('invaliduser');
}

$student = repository::get_student($userid);

$studentname = fullname($user);

$PAGE->set_url(new moodle_url('/local/mobilemonitor/student.php', ['userid' => $userid]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('student_title', 'local_mobilemonitor', $studentname));
$PAGE->set_heading(get_string('student_title', 'local_mobilemonitor', $studentname));
$PAGE->navbar->add(get_string('pluginname', 'local_mobilemonitor'),
    new moodle_url('/local/mobilemonitor/index.php'));
$PAGE->navbar->add(get_string('students_title', 'local_mobilemonitor'),
    new moodle_url('/local/mobilemonitor/students.php'));
$PAGE->navbar->add($studentname);

$backurl = new moodle_url('/local/mobilemonitor/students.php');

echo $OUTPUT->header();

if ($student === null) {
    echo html_writer::div(
        get_string('nostudentactivity', 'local_mobilemonitor'),
        'alert alert-info',
        ['role' => 'status']
    );
    echo html_writer::link($backurl, get_string('backtostudents', 'local_mobilemonitor'),
        ['class' => 'btn btn-secondary']);
    echo $OUTPUT->footer();
    exit;
}

$perpage = 50;
$page = optional_param('page', 0, PARAM_INT);
$page = max(0, $page);

$tokens = repository::get_student_tokens($userid);
$ips = repository::get_student_ips($userid);
$totalaccesses = repository::count_student_accesses($userid);
$accesses = repository::get_student_accesses($userid, $page * $perpage, $perpage);

echo html_writer::div(get_string('student_intro', 'local_mobilemonitor'), 'mb-3');

echo $OUTPUT->render(new \local_mobilemonitor\output\studentdetail(
    $student,
    $tokens,
    $ips,
    $accesses,
    $totalaccesses
));

$url = new moodle_url('/local/mobilemonitor/student.php', ['userid' => $userid]);
if ($totalaccesses > $perpage) {
    echo $OUTPUT->paging_bar($totalaccesses, $page, $perpage, $url);
}

echo html_writer::div(
    html_writer::link($backurl, get_string('backtostudents', 'local_mobilemonitor'),
        ['class' => 'btn btn-secondary mt-3']),
    'mt-3'
);

echo $OUTPUT->footer();
