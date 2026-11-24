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
 * English strings of the Mobile Token Monitor plugin.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accesses_total'] = 'Registered accesses: {$a}';
$string['backtodashboard'] = 'Back to the audit panel';
$string['backtostudents'] = 'Back to the students listing';
$string['column_actions'] = 'Actions';
$string['column_activetokens'] = 'Active tokens';
$string['column_created'] = 'Creation date';
$string['column_creationip'] = 'Creation IP';
$string['column_eventtype'] = 'Event type';
$string['column_firstseen'] = 'First seen';
$string['column_firstuse'] = 'First use';
$string['column_hits'] = 'Accesses';
$string['column_ip'] = 'IP address';
$string['column_ipcount'] = 'Different IPs';
$string['column_lastaccess'] = 'Last access';
$string['column_lastseen'] = 'Last seen';
$string['column_service'] = 'Web service';
$string['column_status'] = 'Status';
$string['column_student'] = 'Student';
$string['column_time'] = 'Date and time';
$string['column_tokenid'] = 'Token identifier';
$string['column_tokens'] = 'Tokens';
$string['column_username'] = 'Username';
$string['column_validuntil'] = 'Valid until';
$string['dashboard_events'] = 'Registered events';
$string['dashboard_intro'] = 'This panel shows the tokens generated for the monitored web services. Only metadata is stored: the token secret is never saved by this plugin.';
$string['dashboard_stats'] = 'Summary';
$string['dashboard_title'] = 'Audit of the Moodle Mobile tokens';
$string['errorinvalidrange'] = 'The start date must be earlier than the end date.';
$string['eventtype_created'] = 'Token created';
$string['eventtype_detected'] = 'Token detected (reconciliation)';
$string['eventtype_used'] = 'Token used';
$string['filter_apply'] = 'Apply filters';
$string['filter_eventtype'] = 'Event type';
$string['filter_fromdate'] = 'From date';
$string['filter_service'] = 'Web service';
$string['filter_status'] = 'Usage status';
$string['filter_status_any'] = 'Any status';
$string['filter_studentname'] = 'Student name';
$string['filter_todate'] = 'To date';
$string['filter_username'] = 'Username';
$string['messageprovider:notification'] = 'Mobile token alerts and detections';
$string['mobilemonitor:manage'] = 'Manage the Mobile Token Monitor';
$string['mobilemonitor:viewlogs'] = 'View the audit log of the mobile tokens';
$string['nav_dashboard'] = 'Audit panel';
$string['nav_dashboard_desc'] = 'Open audit panel';
$string['nav_students'] = 'Mobile activity by student';
$string['nav_students_desc'] = 'Open student listing';
$string['noaccesses'] = 'There is no access history for this student yet.';
$string['noevents'] = 'There are no records that match the selected filters.';
$string['noips'] = 'There are no IP addresses recorded for this student.';
$string['nostudentactivity'] = 'This student has no recorded mobile activity.';
$string['nostudents'] = 'There are no students that match the selected filters.';
$string['notavailable'] = 'Not available';
$string['notification_alert_body'] = 'A repetitive generation of tokens has been detected. This activity requires a review, it does not necessarily mean a security breach.';
$string['notification_alert_count'] = 'Tokens generated in the period: {$a}';
$string['notification_alert_disclaimer'] = 'The activity has been classified as an activity that requires a human review, never as a confirmed attack.';
$string['notification_alert_period'] = 'Evaluation period (hours): {$a}';
$string['notification_alert_subject'] = 'Repetitive generation of Moodle Mobile tokens';
$string['notification_alert_threshold'] = 'Configured threshold: {$a}';
$string['notification_body'] = 'A new token associated to the Moodle Mobile service has been detected.';
$string['notification_eventtype'] = 'Event type: {$a}';
$string['notification_info'] = 'Information:';
$string['notification_ip'] = 'IP address: {$a}';
$string['notification_link'] = 'Audit panel: {$a}';
$string['notification_service'] = 'Web service: {$a}';
$string['notification_student'] = 'Student: {$a}';
$string['notification_subject'] = 'New Moodle Mobile token detected';
$string['notification_time'] = 'Date and time: {$a}';
$string['notification_username'] = 'Username: {$a}';
$string['notokens'] = 'This student has no tokens for the monitored web services.';
$string['pluginname'] = 'Mobile Token Monitor';
$string['pluginname_desc'] = 'Audits the generation and the usage of the tokens of the Moodle Mobile web service. Only metadata is stored, never the token secret.';
$string['privacy:metadata:local_mobilemonitor_access'] = 'Grouped history of the mobile accesses of the tokens.';
$string['privacy:metadata:local_mobilemonitor_access:externalserviceid'] = 'Identifier of the web service used.';
$string['privacy:metadata:local_mobilemonitor_access:ipaddress'] = 'IP address of the mobile access.';
$string['privacy:metadata:local_mobilemonitor_access:timecreated'] = 'Time of the mobile access.';
$string['privacy:metadata:local_mobilemonitor_access:tokenid'] = 'Identifier of the token record of Moodle. The token secret is never stored.';
$string['privacy:metadata:local_mobilemonitor_access:userid'] = 'Identifier of the user that owns the token.';
$string['privacy:metadata:local_mobilemonitor_alert'] = 'Alerts raised for patterns of repetitive generation of tokens.';
$string['privacy:metadata:local_mobilemonitor_alert:alerttype'] = 'Type of the alert.';
$string['privacy:metadata:local_mobilemonitor_alert:fingerprint'] = 'Identifier of the incident, used to avoid duplicated alerts.';
$string['privacy:metadata:local_mobilemonitor_alert:timecreated'] = 'Time when the alert was created.';
$string['privacy:metadata:local_mobilemonitor_alert:timemodified'] = 'Time when the alert was updated for the last time.';
$string['privacy:metadata:local_mobilemonitor_alert:tokencount'] = 'Number of tokens detected in the evaluation period.';
$string['privacy:metadata:local_mobilemonitor_alert:userid'] = 'Identifier of the user that the alert refers to.';
$string['privacy:metadata:local_mobilemonitor_log'] = 'Records of the tokens of the monitored web services.';
$string['privacy:metadata:local_mobilemonitor_log:eventtype'] = 'Type of the recorded event.';
$string['privacy:metadata:local_mobilemonitor_log:externalserviceid'] = 'Identifier of the web service associated to the token.';
$string['privacy:metadata:local_mobilemonitor_log:ipaddress'] = 'IP address of the request that generated the token.';
$string['privacy:metadata:local_mobilemonitor_log:timecreated'] = 'Time when the token was created.';
$string['privacy:metadata:local_mobilemonitor_log:timemodified'] = 'Time when the record was updated for the last time.';
$string['privacy:metadata:local_mobilemonitor_log:tokenid'] = 'Identifier of the token record of Moodle. The token secret is never stored.';
$string['privacy:metadata:local_mobilemonitor_log:tokenlastaccess'] = 'Last known access time of the token.';
$string['privacy:metadata:local_mobilemonitor_log:userid'] = 'Identifier of the user that owns the token.';
$string['privacy:path:accesses'] = 'Mobile access history';
$string['privacy:path:alerts'] = 'Alerts of repetitive generation of tokens';
$string['privacy:path:logs'] = 'Records of the monitored tokens';
$string['setting_alertenabled'] = 'Enable the detection of repetitive activity';
$string['setting_alertenabled_desc'] = 'Detects the users that generate an abnormal amount of tokens in a short period. This activity requires a human review, it is not a confirmed attack.';
$string['setting_enabled'] = 'Enable the monitoring';
$string['setting_enabled_desc'] = 'When it is disabled, no token is registered and no notification is sent.';
$string['setting_inactivitydays'] = 'Inactivity period (days)';
$string['setting_inactivitydays_desc'] = 'A student or a token is considered inactive when it has not been used during this number of days. It is used by the audit of the mobile activity and does not affect the tokens of Moodle.';
$string['setting_notificationsenabled'] = 'Enable the notifications';
$string['setting_notificationsenabled_desc'] = 'Sends a notification to the administrators when a new token is registered or when a repetitive generation of tokens is detected.';
$string['setting_notificationusers'] = 'Additional notification recipients';
$string['setting_notificationusers_desc'] = 'Comma separated list of user identifiers that also receive the notifications, in addition to the users with the "Mobile Token Monitor: View the audit log of the mobile tokens" capability.';
$string['setting_periodhours'] = 'Evaluation period (hours)';
$string['setting_periodhours_desc'] = 'Time window, in hours, used to count the tokens generated by each user.';
$string['setting_retentiondays'] = 'Retention of the records (days)';
$string['setting_retentiondays_desc'] = 'Records older than this number of days are removed by the maintenance task. The tokens of Moodle are never modified.';
$string['setting_serviceshortnames'] = 'Shortnames of the monitored web services';
$string['setting_serviceshortnames_desc'] = 'Comma separated list of shortnames of the external services to monitor. The Moodle Mobile service is identified by its real shortname ({$a}), never by its display name.';
$string['setting_threshold'] = 'Threshold of tokens';
$string['setting_threshold_desc'] = 'Number of tokens generated by the same user in the evaluation period that generates an alert.';
$string['stat_last24hours'] = 'Tokens created in the last 24 hours';
$string['stat_last7days'] = 'Tokens created in the last 7 days';
$string['stat_lastactivity'] = 'Last recorded activity';
$string['stat_total_students'] = 'Students involved';
$string['stat_total_tokens'] = 'Registered mobile tokens';
$string['status_active'] = 'Active';
$string['status_inactive'] = 'Inactive';
$string['student_accesses'] = 'Access history';
$string['student_intro'] = 'History of the mobile tokens and accesses of this student. Only metadata is stored, never the token secret.';
$string['student_ips'] = 'IP addresses used';
$string['student_summary'] = 'Summary';
$string['student_title'] = 'Mobile activity of {$a}';
$string['student_tokens'] = 'Tokens of the student';
$string['students_intro'] = 'Students that have generated or used a token of the monitored web services. The status "active" means that the student has used the mobile app within the configured inactivity period.';
$string['students_link'] = 'View students';
$string['students_title'] = 'Mobile activity by student';
$string['summary_activetokens'] = 'Active tokens';
$string['summary_firstaccess'] = 'First mobile use detected';
$string['summary_firsttoken'] = 'First token created';
$string['summary_ipcount'] = 'Different IP addresses';
$string['summary_lastaccess'] = 'Last mobile use';
$string['summary_tokens'] = 'Tokens generated';
$string['taskcleanup'] = 'Remove the old records of the audit log';
$string['tasksendnotification'] = 'Send the notifications of the Mobile Token Monitor';
$string['tasksync'] = 'Reconcile the monitored tokens with the audit log';
$string['token_expired'] = 'Expired on {$a}';
$string['token_neverused'] = 'Never used';
$string['token_validunlimited'] = 'No expiration';
$string['unknownservice'] = 'Unknown service';
$string['unknownuser'] = 'Deleted user';
$string['viewdetail'] = 'View detail';
$string['viewprofile'] = 'View Moodle profile';
$string['warning_multipleips'] = 'This student has used the mobile app from more than one IP address. Review the access history before considering it an incident.';
$string['warning_multipletokens'] = 'This student has more than one active token. This may be normal when the app is used on several devices.';
