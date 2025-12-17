<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Clase de utilidad para manejar las imágenes del carrusel
 * 
 * @package    mod_imagecarousel
 * @copyright  2024 Zajuna Team
 * @author     Zajuna Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Clase para manejar las operaciones con imágenes del carrusel
 * Permite obtener, guardar, actualizar y eliminar imágenes, además de
 * gestionar versiones específicas para dispositivos móviles y de escritorio.
 */

class Images {
    /**
     * Get carousel images from database
     * 
     * @param int $carouselid ID of the carousel
     * @param bool $onlyvisible When true, returns only images marked as visible
     * @return array List of images
     */
    public static function getImages($carouselid, $onlyvisible = false) {
        global $DB;
        
        $params = array('carouselid' => $carouselid);
        if ($onlyvisible) {
            $params['visible'] = 1;
        }
        
        // Obtener todas las imágenes ordenadas
        $images = $DB->get_records('imagecarousel_images', $params, 'sortorder ASC');
        
        return $images;
    }
    
    /**
     * Agrega opacidad a un color hexadecimal o nombre de color
     *
     * @param string $color Color hexadecimal o nombre
     * @param int $opacity Valor de opacidad (0-100)
     * @return string Color con opacidad aplicada
     */
    private static function add_opacity($color, $opacity) {
        // Usar la función existente en lib.php
        require_once(dirname(__FILE__) . '/../../lib.php');
        return mod_imagecarousel_hex2rgba($color, $opacity);
    }
    
    /**
     * Guarda una imagen en la base de datos
     *
     * @param object $data Datos de la imagen
     * @param object $cm Objeto del módulo de curso
     * @return int ID de la imagen
     */
    
    public static function saveImage($data, $cm) {
        global $DB, $USER;
        // Obtener el contexto del módulo
        $contextmodule = context_module::instance($cm->id);
        
        // Crear un nuevo registro
        $record = new stdClass();
        // Si estamos editando, usar el ID existente
        if (!empty($data->id)) {
            $record->id = $data->id;
        }
        // Asignar el ID del carrusel
        $record->carouselid = $cm->instance;
        // Asignar la URL de la imagen
        $record->url = isset($data->url) ? $data->url : null;
        // Asignar el texto a mostrar sobre la imagen
        $record->text = isset($data->text) ? $data->text : null;
        // Asignar el color del texto
        $record->text_color = isset($data->text_color) ? $data->text_color : null;
        // Asignar la opacidad del color del texto
        $record->text_color_opacity = isset($data->text_color_opacity) ? $data->text_color_opacity : 100;
        // Asignar el tamaño del texto
        $record->text_size = isset($data->text_size) ? $data->text_size : null;
        // Asignar la posición predefinida del texto
        $record->text_position = isset($data->text_position) ? $data->text_position : null;
        // Asignar los ajustes personalizados del texto
        $record->text_position_top = isset($data->text_position_top) ? $data->text_position_top : null;
        // Asignar el ajuste personalizado - right
        $record->text_position_right = isset($data->text_position_right) ? $data->text_position_right : null;
        // Asignar el ajuste personalizado - bottom
        $record->text_position_bottom = isset($data->text_position_bottom) ? $data->text_position_bottom : null;
        // Asignar el ajuste personalizado - left
        $record->text_position_left = isset($data->text_position_left) ? $data->text_position_left : null;
        // Asignar la bandera para identificar si el texto está en negrita
        $record->text_style_bold = isset($data->text_style_bold) ? $data->text_style_bold : 0;
        // Asignar la bandera para identificar si el texto está en cursiva
        $record->text_style_italic = isset($data->text_style_italic) ? $data->text_style_italic : 0;
        // Asignar la bandera para identificar si el texto está subrayado
        $record->text_style_underline = isset($data->text_style_underline) ? $data->text_style_underline : 0;
        // Asignar la URL al hacer clic en el texto
        $record->text_url = isset($data->text_url) ? $data->text_url : null;
        // Asignar el color de fondo del texto
        $record->text_background = isset($data->text_background) ? $data->text_background : null;
        // Asignar la opacidad del color de fondo del texto
        $record->text_background_opacity = isset($data->text_background_opacity) ? $data->text_background_opacity : 0;
        // Asignar el relleno del texto
        $record->text_padding = isset($data->text_padding) ? $data->text_padding : null;
        // Asignar el radio del borde del texto
        $record->text_border_radius = isset($data->text_border_radius) ? $data->text_border_radius : null;
        
        // Determinar qué tipo de imagen estamos procesando (desktop o mobile)
        // y actualizar los campos correspondientes
        $isDesktop = true;
        if (isset($data->is_mobile) && $data->is_mobile) {
            $isDesktop = false;
        }
        
        // Procesar imagen según sea escritorio o móvil
        if ($isDesktop) {
            // Si la imagen es una URL
            if (!empty($data->image)) {
                $record->desktop_image = $data->image;
                $record->desktop_image_name = basename($data->image);
            }
            // Si es un archivo subido y hay un draftitem
            else if (!empty($data->is_uploaded) && !empty($data->desktopimage)) {
                // Procesar el archivo para convertirlo a Base64
                require_once(dirname(__FILE__) . '/../../lib.php');
                $usercontext = context_user::instance($USER->id);
                $fs = get_file_storage();
                $draftitemid = $data->desktopimage;
                
                // Obtener archivos del área draft
                $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
                
                // Depuración
                error_log("Procesando imagen de escritorio, draftitemid: $draftitemid, archivos encontrados: " . count($draftfiles));
                
                foreach ($draftfiles as $file) {
                    if (!$file->is_directory()) {
                        error_log("Procesando archivo: " . $file->get_filename());
                        // Obtener contenido del archivo y convertir a Base64
                        $content = $file->get_content();
                        if ($content) {
                            $record->desktop_image = base64_encode($content);
                            $record->desktop_image_name = $file->get_filename();
                            error_log("Imagen convertida a Base64, tamaño: " . strlen($record->desktop_image));
                        } else {
                            error_log("No se pudo obtener el contenido del archivo");
                        }
                        break; // Solo procesamos el primer archivo
                    }
                }
            }
        } else {
            // Si la imagen es una URL
            if (!empty($data->image)) {
                $record->mobile_image = $data->image;
                $record->mobile_image_name = basename($data->image);
            }
            // Si es un archivo subido y hay un draftitem
            else if (!empty($data->is_uploaded) && !empty($data->mobileimage)) {
                // Procesar el archivo para convertirlo a Base64
                require_once(dirname(__FILE__) . '/../../lib.php');
                $usercontext = context_user::instance($USER->id);
                $fs = get_file_storage();
                $draftitemid = $data->mobileimage;
                
                // Obtener archivos del área draft
                $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
                
                // Depuración
                error_log("Procesando imagen móvil, draftitemid: $draftitemid, archivos encontrados: " . count($draftfiles));
                
                foreach ($draftfiles as $file) {
                    if (!$file->is_directory()) {
                        error_log("Procesando archivo: " . $file->get_filename());
                        // Obtener contenido del archivo y convertir a Base64
                        $content = $file->get_content();
                        if ($content) {
                            $record->mobile_image = base64_encode($content);
                            $record->mobile_image_name = $file->get_filename();
                            error_log("Imagen convertida a Base64, tamaño: " . strlen($record->mobile_image));
                        } else {
                            error_log("No se pudo obtener el contenido del archivo");
                        }
                        break; // Solo procesamos el primer archivo
                    }
                }
            }
        }

        if (empty($data->id)) {
            // Nuevo registro, obtener el máximo orden y sumar 1
            $maxsortorder = $DB->get_field_sql(
                'SELECT MAX(sortorder) FROM {imagecarousel_images} WHERE carouselid = ?',
                array($cm->instance)
            );
            $record->sortorder = $maxsortorder ? $maxsortorder + 1 : 1;
        }

        // Insertamos o actualizamos el registro
        if (!empty($data->id)) {
            $DB->update_record('imagecarousel_images', $record);
            $imageid = $data->id;
        } else {
            $imageid = $DB->insert_record('imagecarousel_images', $record);
        }

        return $imageid;
    }
    
    /**
     * Elimina una imagen del carrusel
     *
     * @param int $carouselid ID del carrusel
     * @param int $imageid Posición de la imagen a eliminar
     * @return bool Éxito o fracaso
     */
    public static function deleteImage($carouselid, $imageid) {
        global $DB;
        
        // Obtener todas las imágenes ordenadas
        $images = $DB->get_records('imagecarousel_images', 
            ['carouselid' => $carouselid], 
            'sortorder ASC'
        );
        
        // Verificar si existe la posición
        $i = 0;
        $targetid = null;
        $targetImage = null;
        foreach ($images as $image) {
            if ($i == $imageid) {
                $targetid = $image->id;
                $targetImage = $image;
                break;
            }
            $i++;
        }
        
        if ($targetid) {
            // Si es una imagen subida, eliminar el archivo físico
            if (!empty($targetImage->is_uploaded) && $targetImage->is_uploaded == 1 && !empty($targetImage->fileid)) {
                $fs = get_file_storage();
                $file = $fs->get_file_by_id($targetImage->fileid);
                if ($file) {
                    $file->delete();
                }
            }
            
            // Eliminar la imagen
            $DB->delete_records('imagecarousel_images', ['id' => $targetid]);
            
            // Reordenar las imágenes restantes
            $sortorder = 0;
            foreach ($images as $image) {
                if ($image->id != $targetid) {
                    $DB->set_field('imagecarousel_images', 'sortorder', $sortorder++, ['id' => $image->id]);
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Mueve una imagen hacia arriba o hacia abajo en el orden
     * 
     * @param int $carouselid ID del carrusel
     * @param int $imageid ID de la imagen a mover
     * @param string $direction Dirección de movimiento ('up' o 'down')
     * @return bool Resultado de la operación
     */
    public static function moveImage($carouselid, $imageid, $direction) {
        global $DB;
        
        // Obtener la imagen actual
        $image = $DB->get_record('imagecarousel_images', array('id' => $imageid, 'carouselid' => $carouselid));
        if (!$image) {
            return false;
        }
        
        // Determinar si buscamos una imagen con orden mayor o menor
        $operator = ($direction === 'up') ? '<' : '>';
        $sort = ($direction === 'up') ? 'sortorder DESC' : 'sortorder ASC';
        
        // Buscar la imagen vecina
        $neighbor = $DB->get_records_select(
            'imagecarousel_images',
            "carouselid = ? AND sortorder $operator ?",
            array($carouselid, $image->sortorder),
            $sort,
            '*',
            0,
            1
        );
        
        if (!$neighbor) {
            return false; // No hay imágenes vecinas en esa dirección
        }
        
        $neighbor = reset($neighbor);
        
    // Intercambiar los valores de sortorder de forma segura
    $temp = $image->sortorder;
    $image->sortorder = $neighbor->sortorder;
    $neighbor->sortorder = $temp;

    // Guardar los cambios
    $DB->update_record('imagecarousel_images', $image);
    $DB->update_record('imagecarousel_images', $neighbor);
        
        return true;
    }
    
    /**
     * Obtiene una imagen específica del carrusel
     * 
     * @param int $carouselid ID del carrusel
     * @param int $imageid ID de la imagen a obtener
     * @return object|false Datos de la imagen o false si no existe
     */
    public static function getImage($carouselid, $imageid) {
        global $DB;
        
        // Obtener la imagen solicitada
        $image = $DB->get_record('imagecarousel_images', array('id' => $imageid, 'carouselid' => $carouselid));
        
        if (!$image) {
            return false;
        }
        
        // Incluir detalles adicionales para facilitar la edición
        if (!empty($image->image)) {
            error_log("Imagen recuperada ID={$imageid}: image={$image->image}, is_uploaded={$image->is_uploaded}");
        } else {
            error_log("Imagen recuperada ID={$imageid} sin valor en el campo 'image'");
        }
        
        return $image;
    }
}
