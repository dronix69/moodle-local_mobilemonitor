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
 * Event observers of the Mobile Token Monitor plugin.
 *
 * Both observers are triggered by Moodle core:
 *  - \core\event\webservice_token_created: fired once, when a new web service token is inserted.
 *  - \core\event\webservice_token_sent: fired on every token request (login/token.php,
 *    admin/tool/mobile/launch.php), including when an existing token is reused.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\webservice_token_created',
        'callback' => '\local_mobilemonitor\observer::token_created',
    ],
    [
        'eventname' => '\core\event\webservice_token_sent',
        'callback' => '\local_mobilemonitor\observer::token_sent',
    ],
];
