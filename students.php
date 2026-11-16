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
 * Listing of the students with mobile activity.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

use local_mobilemonitor\form\student_filter_form;
use local_mobilemonitor\local\repository;

$context = context_system::instance();

require_login();
require_capability('local/mobilemonitor:viewlogs', $context);

$PAGE->set_url(new moodle_url('/local/mobilemonitor/students.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('students_title', 'local_mobilemonitor'));
$PAGE->set_heading(get_string('students_title', 'local_mobilemonitor'));
$PAGE->navbar->add(get_string('pluginname', 'local_mobilemonitor'),
    new moodle_url('/local/mobilemonitor/index.php'));
$PAGE->navbar->add(get_string('students_title', 'local_mobilemonitor'));

$baseurl = new moodle_url('/local/mobilemonitor/students.php');

$filters = [
    'studentname' => optional_param('studentname', '', PARAM_TEXT),
    'username' => optional_param('username', '', PARAM_TEXT),
    'status' => optional_param('status', '', PARAM_ALPHA),
];

$form = new student_filter_form();

if ($form->is_cancelled()) {
    redirect($baseurl);
}

$data = $form->get_data();

if ($data) {
    $filters['studentname'] = trim((string) ($data->studentname ?? ''));
    $filters['username'] = trim((string) ($data->username ?? ''));
    $filters['status'] = (string) ($data->status ?? '');

    $redirectparams = [];
    foreach ($filters as $key => $value) {
        if ($value !== '' && $value !== null) {
            $redirectparams[$key] = $value;
        }
    }
    redirect(new moodle_url($baseurl, $redirectparams));
}

$hasfilters = false;
foreach ($filters as $value) {
    if ($value !== '' && $value !== null) {
        $hasfilters = true;
        break;
    }
}

$perpage = 30;
$page = optional_param('page', 0, PARAM_INT);
$page = max(0, $page);

$total = repository::count_students($filters);
$students = repository::get_students($filters, $page * $perpage, $perpage);

$urlparams = [];
foreach ($filters as $key => $value) {
    if ($value !== '' && $value !== null) {
        $urlparams[$key] = $value;
    }
}
$url = new moodle_url($baseurl, $urlparams);

echo $OUTPUT->header();

echo html_writer::div(get_string('students_intro', 'local_mobilemonitor'), 'mb-3');

if (!$form->is_submitted()) {
    $formdata = new stdClass();
    $formdata->studentname = $filters['studentname'];
    $formdata->username = $filters['username'];
    $formdata->status = $filters['status'];
    $form->set_data($formdata);
}
$form->display();

echo html_writer::start_div('mt-3');
echo $OUTPUT->render(new \local_mobilemonitor\output\studentlist($students, $hasfilters));
echo html_writer::end_div();

if ($total > $perpage) {
    echo $OUTPUT->paging_bar($total, $page, $perpage, $url);
}

echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/mobilemonitor/index.php'),
        get_string('backtodashboard', 'local_mobilemonitor'),
        ['class' => 'btn btn-secondary mt-3']
    ),
    'mt-3'
);

echo $OUTPUT->footer();
