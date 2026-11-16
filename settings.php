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
 * Administration settings of the Mobile Token Monitor plugin.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // External pages for the audit panel and the student list.
    // Registered as admin_externalpage so they appear in the admin navigation
    // tree and are accessible from Site administration > Plugins > Local plugins.
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_mobilemonitor_dashboard',
        get_string('nav_dashboard', 'local_mobilemonitor'),
        new moodle_url('/local/mobilemonitor/index.php'),
        'local/mobilemonitor:viewlogs'
    ));

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_mobilemonitor_students',
        get_string('nav_students', 'local_mobilemonitor'),
        new moodle_url('/local/mobilemonitor/students.php'),
        'local/mobilemonitor:viewlogs'
    ));

    // Settings page.
    $settings = new admin_settingpage('local_mobilemonitor', get_string('pluginname', 'local_mobilemonitor'));
    $ADMIN->add('localplugins', $settings);

    // Link to the audit panel at the top of the settings page.
    $settings->add(new admin_setting_heading(
        'local_mobilemonitor/dashboardlink',
        get_string('nav_dashboard', 'local_mobilemonitor'),
        html_writer::link(
            new moodle_url('/local/mobilemonitor/index.php'),
            get_string('nav_dashboard_desc', 'local_mobilemonitor'),
            ['class' => 'btn btn-primary btn-sm mb-2']
        ) . ' ' . html_writer::link(
            new moodle_url('/local/mobilemonitor/students.php'),
            get_string('nav_students_desc', 'local_mobilemonitor'),
            ['class' => 'btn btn-secondary btn-sm mb-2']
        )
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_mobilemonitor/enabled',
        get_string('setting_enabled', 'local_mobilemonitor'),
        get_string('setting_enabled_desc', 'local_mobilemonitor'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_mobilemonitor/serviceshortnames',
        get_string('setting_serviceshortnames', 'local_mobilemonitor'),
        get_string('setting_serviceshortnames_desc', 'local_mobilemonitor'),
        MOODLE_OFFICIAL_MOBILE_SERVICE,
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_mobilemonitor/notificationsenabled',
        get_string('setting_notificationsenabled', 'local_mobilemonitor'),
        get_string('setting_notificationsenabled_desc', 'local_mobilemonitor'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_mobilemonitor/notificationusers',
        get_string('setting_notificationusers', 'local_mobilemonitor'),
        get_string('setting_notificationusers_desc', 'local_mobilemonitor'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_mobilemonitor/alertenabled',
        get_string('setting_alertenabled', 'local_mobilemonitor'),
        get_string('setting_alertenabled_desc', 'local_mobilemonitor'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_mobilemonitor/threshold',
        get_string('setting_threshold', 'local_mobilemonitor'),
        get_string('setting_threshold_desc', 'local_mobilemonitor'),
        5,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_mobilemonitor/periodhours',
        get_string('setting_periodhours', 'local_mobilemonitor'),
        get_string('setting_periodhours_desc', 'local_mobilemonitor'),
        24,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_mobilemonitor/retentiondays',
        get_string('setting_retentiondays', 'local_mobilemonitor'),
        get_string('setting_retentiondays_desc', 'local_mobilemonitor'),
        90,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_mobilemonitor/inactivitydays',
        get_string('setting_inactivitydays', 'local_mobilemonitor'),
        get_string('setting_inactivitydays_desc', 'local_mobilemonitor'),
        30,
        PARAM_INT
    ));
}
