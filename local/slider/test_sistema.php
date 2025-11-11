<?php
require_once(__DIR__ . '/../../config.php');
require_login();

global $DB, $USER, $COURSE;

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Debug Slider - Estado del Sistema</h1>";

// 1. Verificar tabla y campo
echo "<h2>1. Verificación de Base de Datos</h2>";
$dbman = $DB->get_manager();
$table = new xmldb_table('local_slider');

if ($dbman->table_exists($table)) {
    echo "✓ Tabla local_slider existe<br>";
    
    $field = new xmldb_field('visible_to_students');
    if ($dbman->field_exists($table, $field)) {
        echo "✓ Campo visible_to_students existe<br>";
        
        // Mostrar estructura
        $columns = $DB->get_columns('local_slider');
        echo "<h3>Columnas de la tabla:</h3><ul>";
        foreach ($columns as $column) {
            echo "<li>{$column->name} ({$column->type})</li>";
        }
        echo "</ul>";
    } else {
        echo "✗ <strong style='color:red;'>Campo visible_to_students NO EXISTE</strong><br>";
        echo "<p><a href='{$CFG->wwwroot}/admin/index.php' style='background:red;color:white;padding:10px;display:inline-block;'>IR A ACTUALIZAR BASE DE DATOS</a></p>";
    }
} else {
    echo "✗ Tabla local_slider no existe<br>";
}

// 2. Ver registros actuales
echo "<h2>2. Registros Actuales</h2>";
$records = $DB->get_records('local_slider');
if (empty($records)) {
    echo "<p>No hay registros en local_slider</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>State</th><th>Course State</th><th>Visible to Students</th></tr>";
    foreach ($records as $r) {
        $vis = isset($r->visible_to_students) ? $r->visible_to_students : 'N/A';
        echo "<tr>";
        echo "<td>{$r->id}</td>";
        echo "<td>" . substr($r->name, 0, 30) . "</td>";
        echo "<td>{$r->state}</td>";
        echo "<td>" . (isset($r->course_state) ? $r->course_state : 'N/A') . "</td>";
        echo "<td><strong>{$vis}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Usuario actual
echo "<h2>3. Usuario Actual</h2>";
echo "ID: {$USER->id}<br>";
echo "Username: {$USER->username}<br>";

if (isset($COURSE) && $COURSE->id > 1) {
    $context = context_course::instance($COURSE->id);
    $can_edit = has_capability('moodle/course:update', $context);
    echo "Curso: {$COURSE->fullname} (ID: {$COURSE->id})<br>";
    echo "¿Puede editar?: " . ($can_edit ? '<strong style="color:green;">SÍ</strong>' : '<strong style="color:red;">NO</strong>') . "<br>";
}

// 4. Versión del plugin
echo "<h2>4. Versión del Plugin</h2>";
$version = $DB->get_record('config_plugins', ['plugin' => 'local_slider', 'name' => 'version']);
if ($version) {
    echo "Versión instalada: {$version->value}<br>";
    echo "Versión requerida: 2025110701<br>";
    if ($version->value < 2025110701) {
        echo "<p style='color:red;'><strong>⚠️ NECESITAS ACTUALIZAR</strong></p>";
        echo "<p><a href='{$CFG->wwwroot}/admin/index.php' style='background:orange;color:white;padding:10px;display:inline-block;'>IR A NOTIFICACIONES</a></p>";
    } else {
        echo "<p style='color:green;'>✓ Versión correcta</p>";
    }
}

// 5. Test de consulta
echo "<h2>5. Test de Consulta SQL</h2>";
if (isset($COURSE) && $COURSE->id > 1) {
    $context = context_course::instance($COURSE->id);
    $can_edit = has_capability('moodle/course:update', $context);
    
    echo "<h3>Como Profesor (can_edit = true):</h3>";
    $sql = "SELECT id, name, state, course_state, visible_to_students
            FROM {local_slider}
            WHERE " . $DB->sql_compare_text('state') . " = ?
            AND course_state = ?";
    $results = $DB->get_records_sql($sql, ['1', '1']);
    echo "Registros encontrados: " . count($results) . "<br>";
    
    echo "<h3>Como Estudiante (can_edit = false):</h3>";
    $sql = "SELECT id, name, state, course_state, visible_to_students
            FROM {local_slider}
            WHERE " . $DB->sql_compare_text('state') . " = ?
            AND course_state = ?
            AND visible_to_students = ?";
    $results = $DB->get_records_sql($sql, ['1', '1', '1']);
    echo "Registros encontrados: " . count($results) . "<br>";
}

// 6. Test del endpoint
echo "<h2>6. Test del Endpoint</h2>";
$endpoint = $CFG->wwwroot . '/local/slider/toggle_visibility.php';
echo "Endpoint: <a href='{$endpoint}' target='_blank'>{$endpoint}</a><br>";

if (file_exists($CFG->dirroot . '/local/slider/toggle_visibility.php')) {
    echo "✓ Archivo existe<br>";
} else {
    echo "✗ <strong style='color:red;'>Archivo NO existe</strong><br>";
}

// 7. Test de JavaScript
echo "<h2>7. JavaScript</h2>";
$jsfile = $CFG->dirroot . '/theme/zajuna/javascript/slider_visibility.js';
if (file_exists($jsfile)) {
    echo "✓ Script de visibilidad existe<br>";
    echo "Ubicación: /theme/zajuna/javascript/slider_visibility.js<br>";
} else {
    echo "✗ <strong style='color:red;'>Script NO existe</strong><br>";
}

echo "<hr>";
echo "<h2>Acciones Rápidas</h2>";
echo "<p><a href='{$CFG->wwwroot}/admin/index.php' style='background:#007bff;color:white;padding:10px;display:inline-block;margin:5px;'>Ir a Notificaciones</a></p>";
echo "<p><a href='{$CFG->wwwroot}/admin/purgecaches.php?confirm=1&sesskey=" . sesskey() . "' style='background:#28a745;color:white;padding:10px;display:inline-block;margin:5px;'>Purgar Cachés</a></p>";

// 8. Actualizar manualmente si es necesario
echo "<hr>";
echo "<h2>8. Actualización Manual (Solo si upgrade no funciona)</h2>";
echo "<form method='post'>";
echo "<input type='hidden' name='sesskey' value='" . sesskey() . "'>";
echo "<input type='hidden' name='manual_update' value='1'>";
echo "<button type='submit' style='background:#dc3545;color:white;padding:10px;border:none;cursor:pointer;'>Agregar Campo Manualmente</button>";
echo "</form>";

if (isset($_POST['manual_update']) && confirm_sesskey()) {
    try {
        $table = new xmldb_table('local_slider');
        $field = new xmldb_field('visible_to_students', XMLDB_TYPE_TEXT, null, null, null, null, '1', 'course_state');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            echo "<p style='color:green;'><strong>✓ Campo agregado exitosamente!</strong></p>";
            
            // Actualizar registros existentes
            $DB->execute("UPDATE {local_slider} SET visible_to_students = ?", ['1']);
            echo "<p style='color:green;'>✓ Registros actualizados</p>";
            
            // Actualizar versión
            $DB->set_field('config_plugins', 'value', 2025110701, ['plugin' => 'local_slider', 'name' => 'version']);
            echo "<p style='color:green;'>✓ Versión actualizada</p>";
            
            echo "<p><a href='' style='background:#007bff;color:white;padding:10px;display:inline-block;'>Recargar Página</a></p>";
        } else {
            echo "<p style='color:orange;'>Campo ya existe</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}
