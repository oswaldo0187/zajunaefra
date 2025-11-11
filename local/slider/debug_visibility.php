<?php
/**
 * Debug script para verificar slider visibility
 */

require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $COURSE, $USER;

echo "<h2>Debug Slider Visibility</h2>";

// Verificar estructura de tabla
$dbman = $DB->get_manager();
$table = new xmldb_table('local_slider');

if ($dbman->table_exists($table)) {
    echo "<p>✓ Tabla local_slider existe</p>";
    
    // Verificar si existe el campo visible_to_students
    $field = new xmldb_field('visible_to_students');
    if ($dbman->field_exists($table, $field)) {
        echo "<p>✓ Campo visible_to_students existe</p>";
    } else {
        echo "<p>✗ Campo visible_to_students NO existe - Necesitas ejecutar upgrade</p>";
    }
    
    // Mostrar registros
    $records = $DB->get_records('local_slider');
    echo "<h3>Registros actuales:</h3>";
    echo "<pre>";
    foreach ($records as $record) {
        echo "ID: {$record->id}\n";
        echo "State: {$record->state}\n";
        echo "Course State: " . (isset($record->course_state) ? $record->course_state : 'N/A') . "\n";
        echo "Visible to Students: " . (isset($record->visible_to_students) ? $record->visible_to_students : 'N/A') . "\n";
        echo "---\n";
    }
    echo "</pre>";
} else {
    echo "<p>✗ Tabla local_slider NO existe</p>";
}

// Información del usuario actual
echo "<h3>Usuario actual:</h3>";
echo "<p>ID: {$USER->id}</p>";
echo "<p>Username: {$USER->username}</p>";

// Verificar curso
if (isset($COURSE) && $COURSE->id > 1) {
    echo "<h3>Curso actual:</h3>";
    echo "<p>ID: {$COURSE->id}</p>";
    echo "<p>Nombre: {$COURSE->fullname}</p>";
    
    $context = context_course::instance($COURSE->id);
    $can_edit = has_capability('moodle/course:update', $context);
    echo "<p>¿Puede editar?: " . ($can_edit ? 'SÍ' : 'NO') . "</p>";
}

// Verificar versión del plugin
$plugin = $DB->get_record('config_plugins', [
    'plugin' => 'local_slider',
    'name' => 'version'
]);

if ($plugin) {
    echo "<h3>Versión del plugin:</h3>";
    echo "<p>Versión instalada: {$plugin->value}</p>";
    echo "<p>Versión esperada: 2025110701</p>";
    
    if ($plugin->value < 2025110701) {
        echo "<p style='color:red;'><strong>⚠️ NECESITAS ACTUALIZAR: Ve a Administración → Notificaciones</strong></p>";
    }
}

echo "<hr>";
echo "<p><a href='{$CFG->wwwroot}/admin/index.php?cache=1'>Ir a Notificaciones para actualizar</a></p>";
echo "<p><a href='{$CFG->wwwroot}/admin/purgecaches.php?confirm=1&sesskey=" . sesskey() . "'>Purgar todas las cachés</a></p>";
