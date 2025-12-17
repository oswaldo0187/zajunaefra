<?php
require('../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/utils/images.php');
// Obtener el id del módulo del curso
$id = required_param('id', PARAM_INT);

// Obtener la información del curso y del módulo
$cm = get_coursemodule_from_id('imagecarousel', $id, 0, false, MUST_EXIST);
// Obtener el curso 
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
// Obtener la instancia del módulo
$moduleinstance = $DB->get_record('imagecarousel', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

// Verificar que el usuario tenga permisos para gestionar el carrusel
$context = context_module::instance($cm->id);
require_capability('mod/imagecarousel:manageitems', $context);

// Configurar la URL de la página
$PAGE->set_url('/mod/imagecarousel/manage.php', array('id' => $cm->id));
// Configurar el título de la página
$PAGE->set_title(format_string($moduleinstance->name));
// Configurar el encabezado de la página
$PAGE->set_heading(format_string($course->fullname));

// Procesar acciones de movimiento de imágenes
$action = optional_param('action', '', PARAM_ALPHA);
$imageid = optional_param('imageid', -1, PARAM_INT);

if ($action === 'moveup' && $imageid >= 0) {
    Images::moveImage($moduleinstance->id, $imageid, 'up');
    redirect($PAGE->url, get_string('image_moved_up', 'mod_imagecarousel'));
} else if ($action === 'movedown' && $imageid >= 0) {
    Images::moveImage($moduleinstance->id, $imageid, 'down');
    redirect($PAGE->url, get_string('image_moved_down', 'mod_imagecarousel'));
} else if ($action === 'togglevisibility' && $imageid >= 0) {
    $image = $DB->get_record('imagecarousel_images', ['id' => $imageid, 'carouselid' => $moduleinstance->id]);
    if ($image) {
        $newvisible = empty($image->visible) ? 1 : 0;
        $DB->set_field('imagecarousel_images', 'visible', $newvisible, ['id' => $imageid]);

        $messagekey = $newvisible ? 'image_visibility_enabled' : 'image_visibility_disabled';
        redirect($PAGE->url, get_string($messagekey, 'mod_imagecarousel'));
    } else {
        redirect($PAGE->url, get_string('image_visibility_toggle_error', 'mod_imagecarousel'));
    }
}

// Obtener las imágenes desde la base de datos
// Obtener imágenes como array indexado (0 .. n-1) para trabajar con índices previsibles
$images = array_values(Images::getImages($moduleinstance->id));

// Configurar la tabla
$table = new html_table();
$table->head = array(
    'ID',
    get_string('preview', 'mod_imagecarousel'),
    // Mostrar ambas URLs (desktop y móvil) en columnas separadas
    'URL imagen (Desktop)',
    'URL imagen (Mobile)',
    get_string('text', 'mod_imagecarousel'),
    get_string('text_url', 'mod_imagecarousel'),
    get_string('visibility', 'mod_imagecarousel'),
    get_string('actions', 'mod_imagecarousel')
);
$table->data = array();

// Llenar la tabla con los datos de las imágenes
foreach ($images as $index => $image) {
    $actions = '';
    
    // Agregar flechas de ordenamiento (usar índice en la lista para determinar posición)
    // Flecha arriba
    if ($index > 0) {
        $actions .= html_writer::link(
            new moodle_url('/mod/imagecarousel/manage.php', array('id' => $cm->id, 'imageid' => $image->id, 'action' => 'moveup')),
            $OUTPUT->pix_icon('t/up', get_string('moveup', 'mod_imagecarousel')),
            ['class' => 'action-icon']
        );
    }
    // Flecha abajo
    if ($index < count($images) - 1) {
        $actions .= html_writer::link(
            new moodle_url('/mod/imagecarousel/manage.php', array('id' => $cm->id, 'imageid' => $image->id, 'action' => 'movedown')),
            $OUTPUT->pix_icon('t/down', get_string('movedown', 'mod_imagecarousel')),
            ['class' => 'action-icon']
        );
    }
    
    // Agregar espacio entre los grupos de iconos
    $actions .= html_writer::span('&nbsp;&nbsp;');

    // Botón para alternar visibilidad
    $toggleurl = new moodle_url('/mod/imagecarousel/manage.php', array('id' => $cm->id, 'imageid' => $image->id, 'action' => 'togglevisibility'));
    $toggleicon = !empty($image->visible)
        ? $OUTPUT->pix_icon('t/hide', get_string('hide'))
        : $OUTPUT->pix_icon('t/show', get_string('show'));
    $actions .= html_writer::link($toggleurl, $toggleicon, ['class' => 'action-icon']);
    
    // Iconos de edición y eliminación existentes
    $actions .= html_writer::link(
        new moodle_url('/mod/imagecarousel/edit.php', array('id' => $cm->id, 'imageid' => $image->id)),
        $OUTPUT->pix_icon('t/edit', get_string('edit')),
        ['class' => 'action-icon']
    );
    $actions .= html_writer::link(
        new moodle_url('/mod/imagecarousel/delete.php', array('id' => $cm->id, 'imageid' => $image->id)),
        $OUTPUT->pix_icon('t/delete', get_string('delete')),
        ['class' => 'action-icon']
    );
    
    // Crear la vista previa de la imagen
    $imgSrc = '';
    $previewHtml = '';
    
    // Verificar primero la imagen de escritorio
    if (!empty($image->desktop_image)) {
        if (filter_var($image->desktop_image, FILTER_VALIDATE_URL)) {
            // Es una URL externa
            $imgSrc = $image->desktop_image;
        } else {
            // Es una imagen Base64
            $imageType = 'jpeg'; // Por defecto
            if (!empty($image->desktop_image_name)) {
                $extension = strtolower(pathinfo($image->desktop_image_name, PATHINFO_EXTENSION));
                if ($extension === 'png') {
                    $imageType = 'png';
                } elseif ($extension === 'webp') {
                    $imageType = 'webp';
                }
            }
            $imgSrc = 'data:image/' . $imageType . ';base64,' . $image->desktop_image;
        }
        
        $previewHtml .= html_writer::empty_tag('img', array(
            'src' => $imgSrc,
            'alt' => isset($image->text) ? $image->text : '',
            'style' => 'max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 8px;'
        ));
        $previewHtml .= '<br><span class="badge badge-info">Desktop</span>';
    } 
    
    // Verificar si hay imagen móvil (mostrar junto a la de escritorio)
    if (!empty($image->mobile_image)) {
        $mobileImgSrc = '';
        if (filter_var($image->mobile_image, FILTER_VALIDATE_URL)) {
            // Es una URL externa
            $mobileImgSrc = $image->mobile_image;
        } else {
            // Es una imagen Base64
            $imageType = 'jpeg'; // Por defecto
            if (!empty($image->mobile_image_name)) {
                $extension = strtolower(pathinfo($image->mobile_image_name, PATHINFO_EXTENSION));
                if ($extension === 'png') {
                    $imageType = 'png';
                } elseif ($extension === 'webp') {
                    $imageType = 'webp';
                }
            }
            $mobileImgSrc = 'data:image/' . $imageType . ';base64,' . $image->mobile_image;
        }
        
        // Si ya hay una imagen de escritorio, añadir un espacio
        if (!empty($image->desktop_image)) {
            $previewHtml .= '<br><br>';
        }
        
        $previewHtml .= html_writer::empty_tag('img', array(
            'src' => $mobileImgSrc,
            'alt' => isset($image->text) ? $image->text : '',
            'style' => 'max-width: 100px; max-height: 150px; object-fit: cover; border-radius: 8px;'
        ));
        $previewHtml .= '<br><span class="badge badge-success">Mobile</span>';
    } 
    
    if (empty($image->desktop_image) && empty($image->mobile_image)) {
        // No hay imagen
        $imgSrc = $OUTPUT->image_url('no-image', 'mod_imagecarousel')->out();
        $previewHtml = html_writer::empty_tag('img', array(
            'src' => $imgSrc,
            'alt' => 'No image',
            'style' => 'max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 8px;'
        ));
    }
    
    $preview = $previewHtml;
    
    // Preparar el texto y las URLs
    $text = isset($image->text) ? $image->text : '';
    // Construir URLs específicas de imágenes según origen
    $desktop_url = '-';
    if (!empty($image->desktop_image) && filter_var($image->desktop_image, FILTER_VALIDATE_URL)) {
        $desktop_url = html_writer::link($image->desktop_image, $image->desktop_image, ['target' => '_blank']);
    }
    $mobile_url = '-';
    if (!empty($image->mobile_image) && filter_var($image->mobile_image, FILTER_VALIDATE_URL)) {
        $mobile_url = html_writer::link($image->mobile_image, $image->mobile_image, ['target' => '_blank']);
    }
    $text_url = !empty($image->text_url) ? html_writer::link($image->text_url, $image->text_url, ['target' => '_blank']) : '-';

    $visibilityBadge = !empty($image->visible)
        ? html_writer::tag('span', get_string('visible', 'mod_imagecarousel'), ['class' => 'badge badge-success'])
        : html_writer::tag('span', get_string('hidden', 'mod_imagecarousel'), ['class' => 'badge badge-secondary']);
    
    // Insertar la fila con la columna ID (1-based según el orden actual)
    $table->data[] = array(
        $index + 1,
        $preview,
        $desktop_url,
        $mobile_url,
        $text,
        $text_url,
        $visibilityBadge,
        $actions
    );
}

// Agregar estilos CSS para los iconos de acción
$PAGE->requires->css(new moodle_url('/mod/imagecarousel/styles.css'));

echo $OUTPUT->header();

// Botón para agregar nueva imagen
$addurl = new moodle_url('/mod/imagecarousel/adding_image.php', array('id' => $cm->id));
echo html_writer::div(
    $OUTPUT->single_button($addurl, get_string('addnewimage', 'mod_imagecarousel')),
    'mb-3'
);

// Agregar mensaje de advertencia
echo $OUTPUT->notification(
    get_string('position_warning', 'mod_imagecarousel'),
    \core\output\notification::NOTIFY_INFO
);

// Mostrar la tabla
echo html_writer::table($table);

echo $OUTPUT->footer();