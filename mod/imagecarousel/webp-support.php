<?php
// Este script agrega soporte para WebP al sistema Moodle
// Ejecutar una sola vez desde un usuario administrador

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

// Comprobar si el usuario es administrador
require_login();
require_capability('moodle/site:config', context_system::instance());

// Obtener el id del módulo si está disponible
$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/mod/imagecarousel/webp-support.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Activar soporte para imágenes WebP");
$PAGE->set_heading("Activar soporte para imágenes WebP");

echo $OUTPUT->header();
echo $OUTPUT->heading("Activar soporte para imágenes WebP");

// Verificar si ya existe el registro para WebP
$types = core_component::get_plugin_list('fileconverter');

// Obtener la lista actual de tipos MIME permitidos
$current_types = get_config('core', 'filetypes');
$webp_registered = (strpos($current_types, 'webp') !== false);

if ($webp_registered) {
    echo "<div class='alert alert-info'>El formato WebP ya está registrado en el sistema.</div>";
} else {
    // Registrar el tipo MIME WebP
    $filetypes = get_config('core', 'filetypes');
    $filetypes_array = explode(',', $filetypes);
    $filetypes_array[] = 'webp';
    $new_filetypes = implode(',', array_unique($filetypes_array));
    
    // Guardar la configuración actualizada
    set_config('filetypes', $new_filetypes, 'core');
    
    // Verificar si el tipo MIME 'image/webp' está en la tabla 'files_types'
    $webp_type_exists = $DB->record_exists('files_types', ['mimetype' => 'image/webp']);
    
    if (!$webp_type_exists) {
        // Insertar el nuevo tipo MIME
        $record = new stdClass();
        $record->mimetype = 'image/webp';
        $record->extension = 'webp';
        $record->groups = 'web_image,optimised_image,image';
        $record->description = 'WebP Image';
        $record->icon = 'image';
        
        $DB->insert_record('files_types', $record);
    }
    
    // Limpiar cachés para asegurar que se aplique el cambio
    purge_all_caches();
    
    echo "<div class='alert alert-success'>El formato WebP ha sido registrado correctamente. Las cachés del sistema han sido purgadas.</div>";
}

// Información adicional y enlaces
echo "<div class='mt-4'>
    <p>Para comprobar si WebP funciona correctamente, intenta subir una imagen WebP en el módulo ImageCarousel.</p>";
    
if ($id) {
    echo "<p><a href='$CFG->wwwroot/mod/imagecarousel/manage.php?id=$id' class='btn btn-primary'>Volver al módulo</a></p>";
} else {
    echo "<p><a href='$CFG->wwwroot/mod/imagecarousel/' class='btn btn-primary'>Ir a la lista de carruseles</a></p>";
}

echo "</div>";

echo $OUTPUT->footer(); 