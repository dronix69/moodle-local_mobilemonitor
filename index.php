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
 * Audit panel of the monitored Moodle Mobile tokens.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

use local_mobilemonitor\form\filter_form;
use local_mobilemonitor\local\repository;

$context = context_system::instance();

require_login();
require_capability('local/mobilemonitor:viewlogs', $context);

$PAGE->set_url(new moodle_url('/local/mobilemonitor/index.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_mobilemonitor'));
$PAGE->set_heading(get_string('dashboard_title', 'local_mobilemonitor'));
$PAGE->navbar->add(
    get_string('pluginname', 'local_mobilemonitor'),
    new moodle_url('/local/mobilemonitor/index.php')
);

$baseurl = new moodle_url('/local/mobilemonitor/index.php');

$filters = [
    'studentname' => optional_param('studentname', '', PARAM_TEXT),
    'username' => optional_param('username', '', PARAM_TEXT),
    'eventtype' => optional_param('eventtype', '', PARAM_ALPHANUMEXT),
    'externalserviceid' => optional_param('externalserviceid', 0, PARAM_INT),
    // The date filters use different URL parameter names to avoid a collision with
    // the fromdate[day]/todate[day] arrays submitted by the form.
    'fromdate' => optional_param('from', 0, PARAM_INT),
    'todate' => optional_param('to', 0, PARAM_INT),
];

// Mapping between the internal filter names and the URL parameters.
$urlparamnames = [
    'fromdate' => 'from',
    'todate' => 'to',
];

// Data of the form, with the dates as timestamps (the format accepted by the date element).
$formdata = $filters;
$formdata['fromdate'] = empty($filters['fromdate']) ? 0 : (int) $filters['fromdate'];
$formdata['todate'] = empty($filters['todate']) ? 0 : (int) $filters['todate'];

$form = new filter_form();

if ($form->is_cancelled()) {
    redirect($baseurl);
}

$data = $form->get_data();

if ($data) {
    $filters['studentname'] = trim((string) ($data->studentname ?? ''));
    $filters['username'] = trim((string) ($data->username ?? ''));
    $filters['eventtype'] = (string) ($data->eventtype ?? '');
    $filters['externalserviceid'] = (int) ($data->externalserviceid ?? 0);

    // The date_selector returns a timestamp, or 0 when the optional date is disabled.
    $filters['fromdate'] = max(0, (int) ($data->fromdate ?? 0));
    $todate = max(0, (int) ($data->todate ?? 0));
    $filters['todate'] = $todate ? ($todate + DAYSECS - 1) : 0;

    // Redirect to a canonical URL so that the filters survive reloads and pagination.
    $redirectparams = [];
    foreach ($filters as $key => $value) {
        if (!empty($value)) {
            $redirectparams[$urlparamnames[$key] ?? $key] = $value;
        }
    }
    redirect(new moodle_url('/local/mobilemonitor/index.php', $redirectparams));
}

$hasfilters = false;
foreach ($filters as $value) {
    if (!empty($value)) {
        $hasfilters = true;
        break;
    }
}

$perpage = 25;
$page = optional_param('page', 0, PARAM_INT);
$page = max(0, $page);

$total = repository::count_events($filters);
$records = repository::get_events($filters, $page * $perpage, $perpage);
$stats = repository::get_stats($filters);

$urlparams = [];
foreach ($filters as $key => $value) {
    if (!empty($value)) {
        $urlparams[$urlparamnames[$key] ?? $key] = $value;
    }
}
$url = new moodle_url($baseurl, $urlparams);

echo $OUTPUT->header();

echo html_writer::div(
    get_string('dashboard_intro', 'local_mobilemonitor'),
    'mb-3'
);

echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/mobilemonitor/students.php'),
        get_string('students_link', 'local_mobilemonitor'),
        ['class' => 'btn btn-primary']
    ),
    'mb-3'
);

if (!$form->is_submitted()) {
    // Prefill the form with the filters of the URL (only when it was not submitted,
    // so the submitted values are kept if there are validation errors).
    $form->set_data($formdata);
}
$form->display();

echo html_writer::start_div('mt-3');
echo $OUTPUT->render(new \local_mobilemonitor\output\dashboard($stats, $records, $hasfilters));
echo html_writer::end_div();

if ($total > $perpage) {
    echo $OUTPUT->paging_bar($total, $page, $perpage, $url);
}

echo $OUTPUT->footer();
