<?php
/**
 * Script de actualización para el módulo imagecarousel
 * 
 * @package    mod_imagecarousel
 * @copyright  2024 Zajuna Team
 * @author     Zajuna Team - Andres Eduardo Brochero
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Función de actualización del plugin imagecarousel
 * Se encarga de crear/actualizar la estructura de la base de datos cuando se actualiza el plugin
 *
 * @param int $oldversion Versión anterior del plugin
 * @return bool true si la actualización fue exitosa
 */
function xmldb_imagecarousel_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024040100) {
        // Definir la tabla para las imágenes
        $table = new xmldb_table('imagecarousel_images');

        // Verificar si la tabla ya existe antes de crearla
        if (!$dbman->table_exists($table)) {
            // Agregar campos
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('carouselid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            // El campo image puede contener URL o path relativo a imagen guardada en Moodle
            $table->add_field('image', XMLDB_TYPE_TEXT, null, null, null, null, null);
            // Bandera para identificar si es archivo subido (1) o URL externa (0)
            $table->add_field('is_uploaded', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            // Si es archivo subido, aquí guardamos el itemid para referenciarlo
            $table->add_field('fileid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            // URL al hacer clic en la imagen
            $table->add_field('url', XMLDB_TYPE_TEXT, null, null, null, null, null);
            // Texto a mostrar sobre la imagen
            $table->add_field('text', XMLDB_TYPE_TEXT, null, null, null, null, null);
            // Color del texto
            $table->add_field('text_color', XMLDB_TYPE_CHAR, '50', null, null, null, null);
            // Opacidad del color del texto
            $table->add_field('text_color_opacity', XMLDB_TYPE_INTEGER, '3', null, null, null, '100');
            // Tamaño del texto
            $table->add_field('text_size', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Posición predefinida del texto
            $table->add_field('text_position', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Ajuste personalizado - top
            $table->add_field('text_position_top', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Ajuste personalizado - right
            $table->add_field('text_position_right', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Ajuste personalizado - bottom
            $table->add_field('text_position_bottom', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Ajuste personalizado - left
            $table->add_field('text_position_left', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Bandera para identificar si el texto está en negrita
            $table->add_field('text_style_bold', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
            // Bandera para identificar si el texto está en cursiva
            $table->add_field('text_style_italic', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
            // Bandera para identificar si el texto está subrayado
            $table->add_field('text_style_underline', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
            // URL al hacer clic en el texto
            $table->add_field('text_url', XMLDB_TYPE_TEXT, null, null, null, null, null);
            // Color de fondo del texto
            $table->add_field('text_background', XMLDB_TYPE_CHAR, '50', null, null, null, null);
            // Opacidad del fondo del texto
            $table->add_field('text_background_opacity', XMLDB_TYPE_INTEGER, '3', null, null, null, '0');
            // Relleno del texto
            $table->add_field('text_padding', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Radio del borde del texto
            $table->add_field('text_border_radius', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            // Orden de visualización
            $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            // Campo para identificar si es imagen para móvil (1) o escritorio (0)
            $table->add_field('is_mobile', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            // Agregar claves
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('carouselid', XMLDB_KEY_FOREIGN, ['carouselid'], 'imagecarousel', ['id']);

            // Crear la tabla
            $dbman->create_table($table); // Tabla imagecarousel_images creada correctamente
        }
        
        // Guardar punto de control de la actualización para esta versión
        upgrade_mod_savepoint(true, 2024040100, 'imagecarousel');
    }
    
    if ($oldversion < 2024040101) {
        // Actualización de seguridad y permisos
        // No necesitamos cambios en la base de datos, solo aseguramos la compatibilidad con Moodle 4.1
        // También se agregaron verificaciones de permisos en las páginas de administración
        
        // Guardar punto de control de la actualización para esta versión
        upgrade_mod_savepoint(true, 2024040101, 'imagecarousel');
    }
    
    if ($oldversion < 2024040200) {
        // Actualización para agregar soporte para imágenes específicas de móvil
        // La estructura ya está creada en la primera actualización, esta actualización es solo
        // para asegurar que la versión del plugin se actualice correctamente
        
        // Verificar si la clave is_mobile existe en la tabla
        $table = new xmldb_table('imagecarousel_images');
        $field = new xmldb_field('is_mobile', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        
        if (!$dbman->field_exists($table, $field)) {
            // Agregar el campo si no existe
            $dbman->add_field($table, $field);
        }
        
        // Guardar punto de control de la actualización para esta versión
        upgrade_mod_savepoint(true, 2024040200, 'imagecarousel');
    }
    
    if ($oldversion < 2024040300) {
        // Actualización para cambiar la estructura y almacenar imágenes en formato Base64
        $table = new xmldb_table('imagecarousel_images');
        
        // Campos nuevos para almacenar imágenes en Base64
        $desktop_image = new xmldb_field('desktop_image', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $mobile_image = new xmldb_field('mobile_image', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $desktop_image_name = new xmldb_field('desktop_image_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $mobile_image_name = new xmldb_field('mobile_image_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        
        // Campos a eliminar posteriormente
        $image = new xmldb_field('image');
        $is_uploaded = new xmldb_field('is_uploaded');
        $fileid = new xmldb_field('fileid');
        $is_mobile = new xmldb_field('is_mobile');
        
        // 1. Agregar los nuevos campos
        if (!$dbman->field_exists($table, $desktop_image)) {
            $dbman->add_field($table, $desktop_image);
        }
        
        if (!$dbman->field_exists($table, $mobile_image)) {
            $dbman->add_field($table, $mobile_image);
        }
        
        if (!$dbman->field_exists($table, $desktop_image_name)) {
            $dbman->add_field($table, $desktop_image_name);
        }
        
        if (!$dbman->field_exists($table, $mobile_image_name)) {
            $dbman->add_field($table, $mobile_image_name);
        }
        
        // 2. Migrar datos de la estructura antigua a la nueva
        $images = $DB->get_records('imagecarousel_images');
        foreach ($images as $img) {
            $fs = get_file_storage();
            
            // Si la imagen actual es un archivo subido
            if (!empty($img->is_uploaded) && $img->is_uploaded == 1) {
                // Buscar el archivo en el sistema de archivos de Moodle
                $context = context_module::instance(
                    $DB->get_field('course_modules', 'id', 
                        ['instance' => $img->carouselid, 'module' => $DB->get_field('modules', 'id', ['name' => 'imagecarousel'])]
                    )
                );
                
                $files = $fs->get_area_files($context->id, 'mod_imagecarousel', 'images', $img->id, 'id', false);
                
                if (!empty($files)) {
                    foreach ($files as $file) {
                        if (!$file->is_directory()) {
                            $content = $file->get_content();
                            if ($content) {
                                $base64_content = base64_encode($content);
                                $filename = $file->get_filename();
                                
                                // Actualizar registro según si es móvil o escritorio
                                $update = new stdClass();
                                $update->id = $img->id;
                                
                                if ($img->is_mobile == 1) {
                                    $update->mobile_image = $base64_content;
                                    $update->mobile_image_name = $filename;
                                } else {
                                    $update->desktop_image = $base64_content;
                                    $update->desktop_image_name = $filename;
                                }
                                
                                $DB->update_record('imagecarousel_images', $update);
                            }
                        }
                    }
                }
            } else if (!empty($img->image)) {
                // Si la imagen es una URL, guardar la URL en el campo correspondiente
                $update = new stdClass();
                $update->id = $img->id;
                
                if ($img->is_mobile == 1) {
                    $update->mobile_image = $img->image;
                    $update->mobile_image_name = basename($img->image);
                } else {
                    $update->desktop_image = $img->image;
                    $update->desktop_image_name = basename($img->image);
                }
                
                $DB->update_record('imagecarousel_images', $update);
            }
        }
        
        // 3. Eliminar campos antiguos
        if ($dbman->field_exists($table, $image)) {
            $dbman->drop_field($table, $image);
        }
        
        if ($dbman->field_exists($table, $is_uploaded)) {
            $dbman->drop_field($table, $is_uploaded);
        }
        
        if ($dbman->field_exists($table, $fileid)) {
            $dbman->drop_field($table, $fileid);
        }
        
        if ($dbman->field_exists($table, $is_mobile)) {
            $dbman->drop_field($table, $is_mobile);
        }
        
        // Guardar punto de control de la actualización para esta versión
        upgrade_mod_savepoint(true, 2024040300, 'imagecarousel');
    }

    // Agregar campos de disponibilidad (availablefrom / availableuntil)
    if ($oldversion < 2025101503) {
        $table = new xmldb_table('imagecarousel');

        $field = new xmldb_field('availablefrom', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timemodified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('availableuntil', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'availablefrom');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Guardar punto de control de la actualización para esta versión
        upgrade_mod_savepoint(true, 2025101503, 'imagecarousel');
    }

    // Agregar bandera de visibilidad por imagen
    if ($oldversion < 2025121500) {
        $table = new xmldb_table('imagecarousel_images');
        $field = new xmldb_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'sortorder');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Asegurar que todas las imágenes existentes queden visibles tras la actualización
        $DB->set_field('imagecarousel_images', 'visible', 1);

        upgrade_mod_savepoint(true, 2025121500, 'imagecarousel');
    }


    return true;
}
