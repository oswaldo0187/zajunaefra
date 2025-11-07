<?php
require('../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/utils/images.php');

// Obtener parámetros
$id = required_param('id', PARAM_INT);        // Course Module ID
$imageid = required_param('imageid', PARAM_INT);   // Image ID (not index)
$confirm = optional_param('confirm', 0, PARAM_BOOL);

// Obtener la información del curso y del módulo
$cm = get_coursemodule_from_id('imagecarousel', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('imagecarousel', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

// Verificar permisos
$context = context_module::instance($cm->id);
require_capability('mod/imagecarousel:manageitems', $context);

// Definir la URL de retorno
$returnurl = new moodle_url('/mod/imagecarousel/manage.php', array('id' => $cm->id));

// Verificar que la imagen existe
$image = Images::getImage($moduleinstance->id, $imageid);
if (!$image) {
    redirect($returnurl, get_string('image_not_found', 'mod_imagecarousel'), null, \core\output\notification::NOTIFY_ERROR);
}

// Configurar la página
$PAGE->set_url('/mod/imagecarousel/delete.php', array('id' => $id, 'imageid' => $imageid));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

// Si se ha confirmado la eliminación
if ($confirm && confirm_sesskey()) {
    // Eliminar la imagen de la base de datos
    if ($DB->delete_records('imagecarousel_images', array('id' => $imageid, 'carouselid' => $moduleinstance->id))) {
        // Borrar el archivo asociado si existe
        if (!empty($image->is_uploaded) && !empty($image->fileid)) {
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_imagecarousel', 'images', $imageid);
        }
        
        // Reordenar las imágenes restantes
        $images = $DB->get_records('imagecarousel_images', array('carouselid' => $moduleinstance->id), 'sortorder');
        $order = 1;
        foreach ($images as $img) {
            $DB->set_field('imagecarousel_images', 'sortorder', $order++, array('id' => $img->id));
        }
        
        redirect($returnurl, get_string('image_deleted', 'mod_imagecarousel'), null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        redirect($returnurl, get_string('error_deleting_image', 'mod_imagecarousel'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Mostrar página de confirmación
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('delete_image_title', 'mod_imagecarousel'));

$confirmurl = new moodle_url('/mod/imagecarousel/delete.php', array(
    'id' => $id,
    'imageid' => $imageid,
    'confirm' => 1,
    'sesskey' => sesskey()
));

echo $OUTPUT->confirm(
    get_string('delete_image_confirm', 'mod_imagecarousel'),
    $confirmurl,
    $returnurl
);

echo $OUTPUT->footer(); 