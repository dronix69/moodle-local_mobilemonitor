# Changelog — local_mobilemonitor

## 1.1.2 — 2026-11-24

### Corregido

- Claves de idioma de las capacidades corregidas: `local/mobilemonitor:viewlogs` y
  `local/mobilemonitor:manage` se declaraban como `$string['local/mobilemonitor:...']`, pero
  `get_capability_string()` resuelve `mobilemonitor:viewlogs` y `mobilemonitor:manage` dentro del
  componente `local_mobilemonitor`. Las capacidades aparecían sin nombre legible en la interfaz.
- Añadida la cadena `messageprovider:notification`, requerida por el proveedor de mensajes
  declarado en `db/messages.php`; sin ella el proveedor se mostraba sin nombre en las
  preferencias de notificación.
- Cabecera de licencia GPL y marcadores `@copyright` / `@license` añadidos a
  `templates/dashboard.mustache`, `templates/studentdetail.mustache`,
  `templates/studentlist.mustache` y `styles.css`.
- Cadenas de los archivos de idioma unidas en una sola línea: se construían con el operador `.`
  en 17 cadenas (inglés) y 18 (español). AMOS procesa los archivos línea por línea, por lo que las
  cadenas partidas no se registran correctamente.

## 1.1.1 — 2026-11-16

### Corregido

- `settings.php` no registraba las páginas del panel de auditoría ni de estudiantes como
  `admin_externalpage`, por lo que no aparecían en el árbol de administración de Moodle.
- Añadidos botones de acceso directo al panel de auditoría y al listado de estudiantes en la
  cabecera de la página de configuración del plugin.
- Cadenas `nav_dashboard`, `nav_dashboard_desc`, `nav_students` y `nav_students_desc` añadidas
  en inglés y español.
- Plantillas Mustache renombradas para que coincidan con el nombre de la clase renderable:
  `students.mustache` → `studentlist.mustache` y `student.mustache` → `studentdetail.mustache`.
  Moodle deriva el nombre del template desde el nombre de la clase (`output\studentlist` →
  `studentlist.mustache`); el nombre incorrecto causaba el error "archivo no encontrado".

## 1.1.0 — 2026-11-16

### Añadido

- Auditoría de accesos móviles con historial agrupado por estudiante, token, sesión e IP
  (tabla `local_mobilemonitor_access`).
- Página de estudiantes `/local/mobilemonitor/students.php` con búsqueda, filtro por estado
  activo/inactivo, paginación y enlace desde el panel de auditoría.
- Página de detalle `/local/mobilemonitor/student.php?userid=` con primer uso detectado,
  tokens activos, IPs utilizadas e historial de accesos.
- Detección de estudiantes con varios tokens activos o con accesos desde IPs distintas,
  presentada como aviso para revisión.
- Ajuste `inactivitydays` (30 días por defecto) para clasificar estudiantes y tokens como
  activos o inactivos.
- Metadatos, exportación y borrado de la nueva tabla en la Privacy API.
- Cadenas nuevas en inglés y español y documentación de la auditoría por estudiante.

### Cambiado

- El observador de `core\event\webservice_token_sent` también registra el acceso agrupado.
- La tarea de limpieza elimina el historial de accesos según la retención configurada.

## 1.0.0 — 2026-11-15

Versión inicial.

### Añadido

- Monitorización de tokens del servicio `moodle_mobile_app` identificado por su shortname
  real (`MOODLE_OFFICIAL_MOBILE_SERVICE`).
- Observadores de `core\event\webservice_token_created` y
  `core\event\webservice_token_sent`.
- Tarea de conciliación `sync_task` para tokens creados sin evento (administración y
  creación en lote), ejecutada cada hora.
- Tabla `local_mobilemonitor_log` con índice único `(tokenid, eventtype)` para idempotencia.
- Tabla `local_mobilemonitor_alert` con índice único por incidente.
- Panel de auditoría `/local/mobilemonitor/index.php` con métricas, filtros, paginación y
  tabla accesible.
- Notificaciones mediante la API de mensajería de Moodle (proveedor
  `local_mobilemonitor/notification`), enviadas desde una tarea adhoc.
- Detección configurable de generación repetitiva de tokens (umbral y periodo).
- Capacidades `local/mobilemonitor:viewlogs` y `local/mobilemonitor:manage`.
- Tarea de limpieza `cleanup_task` con retención configurable.
- Privacy API completa: metadatos, exportación y borrado por usuario, por lista de usuarios
  y por contexto.
- Cadenas en inglés y español; interfaz Mustache + Bootstrap 5; `styles.css`.
- Documentación de instalación, configuración, funcionamiento y limitaciones en `README.md`.

### Seguridad

- No se almacena el token ni el `privatetoken`; solo metadatos.
- Consultas con DML y parámetros vinculados; sin SQL concatenado.
- Parámetros tipados con `PARAM_*`; `require_login()` + `require_capability()` en el panel.
- Salidas escapadas por Mustache; sin HTML concatenado.
- Formulario de filtros con `sesskey` automático de Moodle.

### Limitaciones

- El evento `used` se agrega por token (último acceso), sin histórico de cada uso.
- Los tokens anteriores a la instalación se registran como `detected` sin IP.
- La detección de repetición es heurística.
- Pendiente de ejecución de la batería PHPUnit.
