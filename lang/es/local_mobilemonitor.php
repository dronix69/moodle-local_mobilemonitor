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
 * Spanish strings of the Mobile Token Monitor plugin.
 *
 * @package    local_mobilemonitor
 * @copyright  2026 Daniel Ferrada
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Monitor de tokens móviles';
$string['pluginname_desc'] = 'Audita la generación y el uso de los tokens del servicio web Moodle Mobile. '
    . 'Solo se almacenan metadatos, nunca el valor secreto del token.';

$string['local/mobilemonitor:manage'] = 'Gestionar el monitor de tokens móviles';
$string['local/mobilemonitor:viewlogs'] = 'Ver el registro de auditoría de los tokens móviles';

$string['dashboard_title'] = 'Auditoría de tokens de Moodle Mobile';
$string['dashboard_intro'] = 'Este panel muestra los tokens generados para los servicios web monitorizados. '
    . 'Solo se almacenan metadatos: el secreto del token nunca es guardado por este plugin.';
$string['dashboard_stats'] = 'Resumen';
$string['dashboard_events'] = 'Eventos registrados';
$string['students_link'] = 'Ver estudiantes';
$string['backtodashboard'] = 'Volver al panel de auditoría';

$string['students_title'] = 'Actividad móvil por estudiante';
$string['students_intro'] = 'Estudiantes que han generado o utilizado un token de los servicios web monitorizados. '
    . 'El estado "activo" indica que el estudiante ha utilizado la aplicación móvil dentro del periodo de inactividad '
    . 'configurado.';
$string['student_title'] = 'Actividad móvil de {$a}';
$string['student_intro'] = 'Historial de los tokens y accesos móviles de este estudiante. '
    . 'Solo se almacenan metadatos, nunca el valor secreto del token.';
$string['student_summary'] = 'Resumen';
$string['student_tokens'] = 'Tokens del estudiante';
$string['student_ips'] = 'Direcciones IP utilizadas';
$string['student_accesses'] = 'Historial de accesos';
$string['nostudents'] = 'No hay estudiantes que coincidan con los filtros seleccionados.';
$string['nostudentactivity'] = 'Este estudiante no tiene actividad móvil registrada.';
$string['notokens'] = 'Este estudiante no tiene tokens de los servicios web monitorizados.';
$string['noips'] = 'No hay direcciones IP registradas para este estudiante.';
$string['noaccesses'] = 'Todavía no hay historial de accesos para este estudiante.';
$string['accesses_total'] = 'Accesos registrados: {$a}';
$string['viewdetail'] = 'Ver detalle';
$string['viewprofile'] = 'Ver el perfil de Moodle';
$string['backtostudents'] = 'Volver al listado de estudiantes';

$string['column_tokens'] = 'Tokens';
$string['column_activetokens'] = 'Tokens activos';
$string['column_ipcount'] = 'IP distintas';
$string['column_firstuse'] = 'Primer uso';
$string['column_status'] = 'Estado';
$string['column_validuntil'] = 'Vigente hasta';
$string['column_creationip'] = 'IP de creación';
$string['column_hits'] = 'Accesos';
$string['column_firstseen'] = 'Visto por primera vez';
$string['column_lastseen'] = 'Visto por última vez';
$string['column_time'] = 'Fecha y hora';
$string['column_tokenid'] = 'Identificador del token';
$string['column_actions'] = 'Acciones';

$string['status_active'] = 'Activo';
$string['status_inactive'] = 'Inactivo';
$string['token_neverused'] = 'Nunca utilizado';
$string['token_validunlimited'] = 'Sin caducidad';
$string['token_expired'] = 'Caducado el {$a}';

$string['summary_firsttoken'] = 'Primer token creado';
$string['summary_firstaccess'] = 'Primer uso móvil detectado';
$string['summary_lastaccess'] = 'Último uso móvil';
$string['summary_tokens'] = 'Tokens generados';
$string['summary_activetokens'] = 'Tokens activos';
$string['summary_ipcount'] = 'Direcciones IP distintas';

$string['warning_multipleips'] = 'Este estudiante ha utilizado la aplicación móvil desde más de una dirección IP. '
    . 'Revise el historial de accesos antes de considerarlo un incidente.';
$string['warning_multipletokens'] = 'Este estudiante tiene más de un token activo. '
    . 'Puede ser normal cuando la aplicación se usa en varios dispositivos.';

$string['filter_status'] = 'Estado de uso';
$string['filter_status_any'] = 'Cualquier estado';

$string['stat_total_tokens'] = 'Tokens móviles registrados';
$string['stat_total_students'] = 'Estudiantes involucrados';
$string['stat_last24hours'] = 'Tokens creados en las últimas 24 horas';
$string['stat_last7days'] = 'Tokens creados en los últimos 7 días';
$string['stat_lastactivity'] = 'Última actividad registrada';

$string['column_student'] = 'Estudiante';
$string['column_username'] = 'Nombre de usuario';
$string['column_service'] = 'Servicio web';
$string['column_created'] = 'Fecha de creación';
$string['column_lastaccess'] = 'Último acceso';
$string['column_ip'] = 'Dirección IP';
$string['column_eventtype'] = 'Tipo de evento';

$string['eventtype_created'] = 'Token creado';
$string['eventtype_detected'] = 'Token detectado (conciliación)';
$string['eventtype_used'] = 'Token utilizado';

$string['filter_studentname'] = 'Nombre del estudiante';
$string['filter_username'] = 'Nombre de usuario';
$string['filter_eventtype'] = 'Tipo de evento';
$string['filter_service'] = 'Servicio web';
$string['filter_fromdate'] = 'Desde la fecha';
$string['filter_todate'] = 'Hasta la fecha';
$string['filter_apply'] = 'Aplicar filtros';
$string['errorinvalidrange'] = 'La fecha inicial debe ser anterior a la fecha final.';

$string['noevents'] = 'No hay registros que coincidan con los filtros seleccionados.';
$string['notavailable'] = 'No disponible';
$string['unknownservice'] = 'Servicio desconocido';
$string['unknownuser'] = 'Usuario eliminado';

$string['setting_enabled'] = 'Activar la monitorización';
$string['setting_enabled_desc'] = 'Si se desactiva, no se registra ningún token ni se envía ninguna notificación.';
$string['setting_serviceshortnames'] = 'Shortnames de los servicios web monitorizados';
$string['setting_serviceshortnames_desc'] = 'Lista de shortnames separados por comas de los servicios externos a monitorizar. '
    . 'El servicio Moodle Mobile se identifica por su shortname real ({$a}), nunca por su nombre visible.';
$string['setting_notificationsenabled'] = 'Activar las notificaciones';
$string['setting_notificationsenabled_desc'] = 'Envía una notificación a los administradores cuando se registra un token nuevo '
    . 'o cuando se detecta una generación repetitiva de tokens.';
$string['setting_notificationusers'] = 'Destinatarios adicionales de las notificaciones';
$string['setting_notificationusers_desc'] = 'Lista de identificadores de usuario separados por comas que también reciben las '
    . 'notificaciones, además de los usuarios con la capacidad "Monitor de tokens móviles: Ver el registro de auditoría '
    . 'de los tokens móviles".';
$string['setting_alertenabled'] = 'Activar la detección de actividad repetitiva';
$string['setting_alertenabled_desc'] = 'Detecta los usuarios que generan una cantidad anormal de tokens en un periodo corto. '
    . 'Esta actividad requiere revisión humana, no es un ataque confirmado.';
$string['setting_threshold'] = 'Umbral de tokens';
$string['setting_threshold_desc'] = 'Cantidad de tokens generados por un mismo usuario en el periodo de evaluación que '
    . 'genera una alerta.';
$string['setting_periodhours'] = 'Periodo de evaluación (horas)';
$string['setting_periodhours_desc'] = 'Ventana de tiempo, en horas, utilizada para contar los tokens generados por cada usuario.';
$string['setting_retentiondays'] = 'Retención de los registros (días)';
$string['setting_retentiondays_desc'] = 'Los registros con más antigüedad que este número de días se eliminan en la tarea de '
    . 'mantenimiento. Los tokens de Moodle nunca se modifican.';
$string['setting_inactivitydays'] = 'Periodo de inactividad (días)';
$string['setting_inactivitydays_desc'] = 'Un estudiante o un token se considera inactivo cuando no se ha utilizado durante este '
    . 'número de días. Se usa en la auditoría de la actividad móvil y no afecta a los tokens de Moodle.';

$string['notification_subject'] = 'Nuevo token Moodle Mobile detectado';
$string['notification_body'] = 'Se ha detectado la creación de un nuevo token asociado al servicio Moodle Mobile.';
$string['notification_info'] = 'Información:';
$string['notification_student'] = 'Estudiante: {$a}';
$string['notification_username'] = 'Nombre de usuario: {$a}';
$string['notification_service'] = 'Servicio web: {$a}';
$string['notification_time'] = 'Fecha y hora: {$a}';
$string['notification_ip'] = 'Dirección IP: {$a}';
$string['notification_eventtype'] = 'Tipo de evento: {$a}';
$string['notification_link'] = 'Panel de auditoría: {$a}';

$string['notification_alert_subject'] = 'Generación repetitiva de tokens Moodle Mobile';
$string['notification_alert_body'] = 'Se ha detectado una generación repetitiva de tokens. '
    . 'Esta actividad requiere revisión, no implica necesariamente una vulneración.';
$string['notification_alert_count'] = 'Tokens generados en el periodo: {$a}';
$string['notification_alert_threshold'] = 'Umbral configurado: {$a}';
$string['notification_alert_period'] = 'Periodo de evaluación (horas): {$a}';
$string['notification_alert_disclaimer'] = 'La actividad ha sido clasificada como una actividad que requiere revisión '
    . 'humana, nunca como un ataque confirmado.';

$string['tasksync'] = 'Conciliar los tokens monitorizados con el registro de auditoría';
$string['taskcleanup'] = 'Eliminar los registros antiguos del registro de auditoría';
$string['tasksendnotification'] = 'Enviar las notificaciones del monitor de tokens móviles';

$string['privacy:metadata:local_mobilemonitor_log'] = 'Registros de los tokens de los servicios web monitorizados.';
$string['privacy:metadata:local_mobilemonitor_log:userid'] = 'Identificador del usuario propietario del token.';
$string['privacy:metadata:local_mobilemonitor_log:tokenid'] = 'Identificador del registro del token en Moodle. '
    . 'El secreto del token nunca se almacena.';
$string['privacy:metadata:local_mobilemonitor_log:externalserviceid'] = 'Identificador del servicio web asociado al token.';
$string['privacy:metadata:local_mobilemonitor_log:eventtype'] = 'Tipo del evento registrado.';
$string['privacy:metadata:local_mobilemonitor_log:ipaddress'] = 'Dirección IP de la petición que generó el token.';
$string['privacy:metadata:local_mobilemonitor_log:tokenlastaccess'] = 'Último acceso conocido del token.';
$string['privacy:metadata:local_mobilemonitor_log:timecreated'] = 'Momento en que se creó el token.';
$string['privacy:metadata:local_mobilemonitor_log:timemodified'] = 'Momento en que se actualizó el registro por última vez.';

$string['privacy:metadata:local_mobilemonitor_access'] = 'Historial agrupado de los accesos móviles de los tokens.';
$string['privacy:metadata:local_mobilemonitor_access:userid'] = 'Identificador del usuario propietario del token.';
$string['privacy:metadata:local_mobilemonitor_access:tokenid'] = 'Identificador del registro del token en Moodle. '
    . 'El secreto del token nunca se almacena.';
$string['privacy:metadata:local_mobilemonitor_access:externalserviceid'] = 'Identificador del servicio web utilizado.';
$string['privacy:metadata:local_mobilemonitor_access:ipaddress'] = 'Dirección IP del acceso móvil.';
$string['privacy:metadata:local_mobilemonitor_access:timecreated'] = 'Momento del acceso móvil.';

$string['privacy:metadata:local_mobilemonitor_alert'] = 'Alertas generadas por patrones de generación repetitiva de tokens.';
$string['privacy:metadata:local_mobilemonitor_alert:userid'] = 'Identificador del usuario al que se refiere la alerta.';
$string['privacy:metadata:local_mobilemonitor_alert:alerttype'] = 'Tipo de la alerta.';
$string['privacy:metadata:local_mobilemonitor_alert:fingerprint'] = 'Identificador del incidente, utilizado para evitar '
    . 'alertas duplicadas.';
$string['privacy:metadata:local_mobilemonitor_alert:tokencount'] = 'Cantidad de tokens detectados en el periodo de evaluación.';
$string['privacy:metadata:local_mobilemonitor_alert:timecreated'] = 'Momento en que se creó la alerta.';
$string['privacy:metadata:local_mobilemonitor_alert:timemodified'] = 'Momento en que se actualizó la alerta por última vez.';

$string['privacy:path:logs'] = 'Registros de los tokens monitorizados';
$string['privacy:path:accesses'] = 'Historial de accesos móviles';
$string['privacy:path:alerts'] = 'Alertas de generación repetitiva de tokens';

$string['nav_dashboard'] = 'Panel de auditoría';
$string['nav_dashboard_desc'] = 'Abrir panel de auditoría';
$string['nav_students'] = 'Actividad móvil por estudiante';
$string['nav_students_desc'] = 'Abrir listado de estudiantes';
