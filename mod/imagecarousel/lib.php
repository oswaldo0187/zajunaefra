<?php
defined('MOODLE_INTERNAL') || die();
// agrega una instancia de la actividad - AGREGAR UN REGISTRO EN LA TABLA imagecarousel
function imagecarousel_add_instance($imagecarousel) {
    global $DB;
    // agrega el tiempo de creación
    $imagecarousel->timecreated = time();
    // agrega el tiempo de modificación
    $imagecarousel->timemodified = time();
    // Asegurar campos de disponibilidad por compatibilidad (si no vienen en el formulario)
    if (!isset($imagecarousel->availablefrom)) {
        $imagecarousel->availablefrom = 0;
    }
    if (!isset($imagecarousel->availableuntil)) {
        $imagecarousel->availableuntil = 0;
    }
    // agrega el registro en la tabla imagecarousel
    return $DB->insert_record('imagecarousel', $imagecarousel);
}
// actualiza una instancia de la actividad - ACTUALIZAR UN REGISTRO EN LA TABLA imagecarousel
function imagecarousel_update_instance($imagecarousel) {
    global $DB;
    // actualiza el tiempo de modificación
    $imagecarousel->timemodified = time();  
    // actualiza el id de la instancia
    $imagecarousel->id = $imagecarousel->instance;
    // Asegurar campos de disponibilidad por compatibilidad
    if (!isset($imagecarousel->availablefrom)) {
        $imagecarousel->availablefrom = 0;
    }
    if (!isset($imagecarousel->availableuntil)) {
        $imagecarousel->availableuntil = 0;
    }
    // actualiza el registro en la tabla imagecarousel
    return $DB->update_record('imagecarousel', $imagecarousel);
}
// elimina una instancia de la actividad - ELIMINAR UN REGISTRO EN LA TABLA imagecarousel
function imagecarousel_delete_instance($id) {
    global $DB;
    // verifica si el registro existe
    if (!$imagecarousel = $DB->get_record('imagecarousel', array('id' => $id))) {
        // retorna false si el registro no existe
        return false;
    }
    
    // Eliminar todos los archivos subidos
    $context = context_module::instance(
        $DB->get_field('course_modules', 'id', 
            ['instance' => $id, 'module' => $DB->get_field('modules', 'id', ['name' => 'imagecarousel'])]
        )
    );
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_imagecarousel');
    
    // Eliminar registros de imágenes
    $DB->delete_records('imagecarousel_images', ['carouselid' => $id]);
    
    // elimina el registro en la tabla imagecarousel
    $DB->delete_records('imagecarousel', array('id' => $imagecarousel->id));
    // retorna true si el registro se eliminó correctamente
    return true;
}

// muestra la información de la actividad - MOSTRAR LA INFORMACIÓN del carrusel de imágenes
function imagecarousel_cm_info_view(cm_info $cm) {
    global $CFG, $PAGE, $OUTPUT, $DB;
    
    require_once($CFG->dirroot . '/mod/imagecarousel/classes/utils/images.php');
    
    // Obtener la instancia del módulo para obtener el ID
    $moduleinstance = $DB->get_record('imagecarousel', array('id' => $cm->instance), '*', MUST_EXIST);

    // Si el carrusel está programado: ocultarlo fuera del rango (a menos que el usuario esté editando)
    $now = time();
    if (empty($PAGE->user_is_editing())) {
        if (!empty($moduleinstance->availablefrom) && $moduleinstance->availablefrom > 0 && $now < $moduleinstance->availablefrom) {
            // No mostrar contenido antes de la fecha de disponibilidad
            $cm->set_content('');
            return;
        }
        if (!empty($moduleinstance->availableuntil) && $moduleinstance->availableuntil > 0 && $now > $moduleinstance->availableuntil) {
            // No mostrar contenido después de la fecha de caducidad
            $cm->set_content('');
            return;
        }
    }
    
    // Obtener las imágenes desde la base de datos
    $imagesobj = Images::getImages($moduleinstance->id, true);
    
    // Convertir los objetos a arrays para la plantilla
    $images = array();
    $index = 0;
    foreach ($imagesobj as $image) {
        $img = array(
            'id' => $image->id,
            'url' => $image->url,
            'text' => $image->text,
            'text_color' => $image->text_color,
            'text_size' => $image->text_size,
            'text_position' => $image->text_position,
            'text_url' => $image->text_url,
            'text_background' => $image->text_background,
            'text_padding' => $image->text_padding,
            'text_border_radius' => $image->text_border_radius,
            'text_color_opacity' => $image->text_color_opacity ?? 100,
            'text_background_opacity' => $image->text_background_opacity ?? 0,
            'text_position_top' => $image->text_position_top ?? '',
            'text_position_right' => $image->text_position_right ?? '',
            'text_position_bottom' => $image->text_position_bottom ?? '',
            'text_position_left' => $image->text_position_left ?? '',
            'text_style_bold' => $image->text_style_bold ?? false,
            'text_style_italic' => $image->text_style_italic ?? false,
            'text_style_underline' => $image->text_style_underline ?? false,
            'index' => $index,
            'first' => ($index === 0)
        );
        
        // Procesar imágenes para dispositivos específicos
        
        // Procesar imagen de escritorio si existe
        if (!empty($image->desktop_image)) {
            // Verificar si es una URL o Base64
            if (filter_var($image->desktop_image, FILTER_VALIDATE_URL)) {
                // Es una URL
                $img['desktop_image'] = $image->desktop_image;
            } else {
                // Es Base64, determinar el tipo de imagen
                $imageType = 'jpeg'; // Por defecto
                if (!empty($image->desktop_image_name)) {
                    $extension = strtolower(pathinfo($image->desktop_image_name, PATHINFO_EXTENSION));
                    if ($extension === 'png') {
                        $imageType = 'png';
                    } elseif ($extension === 'webp') {
                        $imageType = 'webp';
                    }
                }
                // Añadir el prefijo completo
                $img['desktop_image'] = 'data:image/' . $imageType . ';base64,' . $image->desktop_image;
            }
        }
        
        // Procesar imagen móvil si existe
        if (!empty($image->mobile_image)) {
            // Verificar si es una URL o Base64
            if (filter_var($image->mobile_image, FILTER_VALIDATE_URL)) {
                // Es una URL
                $img['mobile_image'] = $image->mobile_image;
            } else {
                // Es Base64, determinar el tipo de imagen
                $imageType = 'jpeg'; // Por defecto
                if (!empty($image->mobile_image_name)) {
                    $extension = strtolower(pathinfo($image->mobile_image_name, PATHINFO_EXTENSION));
                    if ($extension === 'png') {
                        $imageType = 'png';
                    } elseif ($extension === 'webp') {
                        $imageType = 'webp';
                    }
                }
                // Añadir el prefijo completo
                $img['mobile_image'] = 'data:image/' . $imageType . ';base64,' . $image->mobile_image;
            }
        }
        
        // Si no hay imágenes específicas, usar la imagen genérica como fallback
        if (empty($img['desktop_image']) && empty($img['mobile_image'])) {
            if (!empty($image->desktop_image)) {
                // Verificar si es una URL o Base64
                if (filter_var($image->desktop_image, FILTER_VALIDATE_URL)) {
                    // Es una URL
                    $img['image'] = $image->desktop_image;
                } else {
                    // Es Base64, determinar el tipo de imagen
                    $imageType = 'jpeg'; // Por defecto
                    if (!empty($image->desktop_image_name)) {
                        $extension = strtolower(pathinfo($image->desktop_image_name, PATHINFO_EXTENSION));
                        if ($extension === 'png') {
                            $imageType = 'png';
                        } elseif ($extension === 'webp') {
                            $imageType = 'webp';
                        }
                    }
                    // Añadir el prefijo completo
                    $img['image'] = 'data:image/' . $imageType . ';base64,' . $image->desktop_image;
                }
            } else if (!empty($image->mobile_image)) {
                // Verificar si es una URL o Base64
                if (filter_var($image->mobile_image, FILTER_VALIDATE_URL)) {
                    // Es una URL
                    $img['image'] = $image->mobile_image;
                } else {
                    // Es Base64, determinar el tipo de imagen
                    $imageType = 'jpeg'; // Por defecto
                    if (!empty($image->mobile_image_name)) {
                        $extension = strtolower(pathinfo($image->mobile_image_name, PATHINFO_EXTENSION));
                        if ($extension === 'png') {
                            $imageType = 'png';
                        } elseif ($extension === 'webp') {
                            $imageType = 'webp';
                        }
                    }
                    // Añadir el prefijo completo
                    $img['image'] = 'data:image/' . $imageType . ';base64,' . $image->mobile_image;
                }
            }
        }
        
        // Procesar opacidades si están definidas
        if (isset($image->text_color) && isset($image->text_color_opacity) && $image->text_color_opacity < 100) {
            $img['text_color'] = mod_imagecarousel_hex2rgba($image->text_color, $image->text_color_opacity);
        }
        
        if (isset($image->text_background) && isset($image->text_background_opacity) && $image->text_background_opacity > 0) {
            $img['text_background'] = mod_imagecarousel_hex2rgba($image->text_background, $image->text_background_opacity);
        }
        
        // Procesar posición personalizada
        $text_position_custom = array();
        if (!empty($image->text_position_top)) {
            $text_position_custom['top'] = $image->text_position_top;
        }
        if (!empty($image->text_position_right)) {
            $text_position_custom['right'] = $image->text_position_right;
        }
        if (!empty($image->text_position_bottom)) {
            $text_position_custom['bottom'] = $image->text_position_bottom;
        }
        if (!empty($image->text_position_left)) {
            $text_position_custom['left'] = $image->text_position_left;
        }
        
        if (!empty($text_position_custom)) {
            $img['text_position_custom'] = $text_position_custom;
        }
        
        // Procesar estilos de texto
        $text_style = array();
        if (!empty($image->text_style_bold)) {
            $text_style['bold'] = true;
        }
        if (!empty($image->text_style_italic)) {
            $text_style['italic'] = true;
        }
        if (!empty($image->text_style_underline)) {
            $text_style['underline'] = true;
        }
        
        if (!empty($text_style)) {
            $img['text_style'] = $text_style;
        }
        
        $images[] = $img;
        $index++;
    }
    
    $data = [
        'uniqid' => uniqid(),
        'interval' => 5000,
        'images' => $images,
        'hasMultipleImages' => count($images) > 1
    ];
    
    $content = $OUTPUT->render_from_template('mod_imagecarousel/carousel', $data);
    if ($PAGE->user_is_editing()) {
        $url = new moodle_url('/mod/imagecarousel/manage.php', ['id' => $cm->id]);
        $button = $OUTPUT->single_button($url, get_string('manageimages', 'mod_imagecarousel'));
        $content .= $button;
    }
    $cm->set_content($content);
}

/**
 * Serves the files from the imagecarousel file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise
 */
function mod_imagecarousel_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    // Depuración: registrar parámetros recibidos
    error_log("pluginfile llamado con: filearea=$filearea, itemid=" . (isset($args[0]) ? $args[0] : 'none') . 
              ", filename=" . (isset($args[1]) ? $args[1] : 'none'));

    // Verificar que estamos en el contexto de un módulo
    if ($context->contextlevel != CONTEXT_MODULE) {
        error_log("Error: Contexto incorrecto - " . $context->contextlevel);
        return false;
    }

    // Exigir inicio de sesión para acceder a los archivos
    require_login($course, true, $cm);
    
    // Verificar que el área de archivos solicitada sea válida para este módulo
    if ($filearea !== 'images') {
        error_log("Error: Área de archivos incorrecta - " . $filearea);
        return false;
    }

    // Extraer los parámetros de la ruta para identificar el archivo específico
    $itemid = array_shift($args); // Este es el ID de la imagen en la tabla imagecarousel_images
    if (empty($itemid)) {
        error_log("Error: itemid vacío");
        return false;
    }
    
    $filename = array_pop($args);
    if (empty($filename)) {
        error_log("Error: filename vacío");
        return false;
    }
    
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    // Verificar que la instancia del módulo exista en la base de datos
    if (!$DB->get_record('imagecarousel', array('id' => $cm->instance))) {
        error_log("Error: No se encontró la instancia del módulo - " . $cm->instance);
        return false;
    }
    
    // Verificar que la imagen exista en la base de datos
    $image = $DB->get_record('imagecarousel_images', array('id' => $itemid, 'carouselid' => $cm->instance));
    if (!$image) {
        error_log("Error: No se encontró la imagen - itemid=$itemid, carouselid=" . $cm->instance);
        return false;
    }

    // Obtener el sistema de almacenamiento de archivos de Moodle
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_imagecarousel', $filearea, $itemid, $filepath, $filename);
    
    // Si el archivo no existe directamente, intentar encontrarlo por nombre en esta área
    if (!$file || $file->is_directory()) {
        // Buscar todos los archivos en esta área
        $files = $fs->get_area_files($context->id, 'mod_imagecarousel', $filearea, $itemid, 'id', false);
        
        $fileFound = false;
        foreach ($files as $possibleFile) {
            if ($possibleFile->get_filename() === $filename) {
                $file = $possibleFile;
                $fileFound = true;
                error_log("Archivo encontrado por nombre: " . $filename);
                break;
            }
        }
        
        // Si aún no encontramos el archivo, buscar en todas las imágenes de este carrusel
        if (!$fileFound) {
            $allImages = $DB->get_records('imagecarousel_images', array('carouselid' => $cm->instance));
            foreach ($allImages as $carouselImage) {
                $imageFiles = $fs->get_area_files($context->id, 'mod_imagecarousel', $filearea, $carouselImage->id, 'id', false);
                foreach ($imageFiles as $possibleFile) {
                    if ($possibleFile->get_filename() === $filename) {
                        $file = $possibleFile;
                        $fileFound = true;
                        error_log("Archivo encontrado en otra imagen del carrusel: ID=" . $carouselImage->id);
                        break 2; // Salir de ambos bucles
                    }
                }
            }
        }
        
        // Si todavía no encontramos el archivo, registrar error y devolver false
        if (!$fileFound) {
            // Depuración: mostrar archivos disponibles en este área
            $filesList = "";
            foreach ($files as $f) {
                if (!$f->is_directory()) {
                    $filesList .= $f->get_filepath() . $f->get_filename() . ", ";
                }
            }
            error_log("Error: Archivo no encontrado - contexto={$context->id}, componente=mod_imagecarousel, " .
                     "filearea={$filearea}, itemid={$itemid}, filepath={$filepath}, filename={$filename}");
            error_log("Archivos disponibles: " . $filesList);
            return false;
        }
    }

    // Enviar el archivo al navegador con una cache de 1 día
    error_log("Éxito: Enviando archivo - " . $file->get_filename());
    send_stored_file($file, 86400, 0, $forcedownload, $options);
    return true;
}

/**
 * Convierte un color hexadecimal y un valor de opacidad a formato rgba para CSS
 *
 * @param string $hex Color en formato hexadecimal (#RRGGBB)
 * @param int $opacity Valor de opacidad (0-100)
 * @return string Color en formato rgba(r,g,b,a) para CSS
 */
function mod_imagecarousel_hex2rgba($hex, $opacity) {
    // Si no hay color o es transparente
    if (empty($hex) || $hex === 'transparent') {
        return 'transparent';
    }

    $hex = str_replace('#', '', $hex);
    
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    
    $opacity = max(0, min(100, $opacity)) / 100; // Asegurar que esté entre 0 y 1
    
    return "rgba($r, $g, $b, $opacity)";
}

/**
 * Convierte imágenes a Base64
 *
 * @param array $images Array con información de las imágenes
 * @return array Array con las imágenes convertidas a Base64
 */
function mod_imagecarousel_images_to_base64($images) {
    $result = [];
    
    foreach ($images as $key => $image) {
        if (isset($image['tmp_name']) && file_exists($image['tmp_name'])) {
            $content = file_get_contents($image['tmp_name']);
            if ($content !== false) {
                $result[$key] = base64_encode($content);
            }
        }
    }
    
    return $result;
}

/**
 * Genera nombres de imágenes para el registro
 *
 * @param array $images Array con información de las imágenes
 * @return array Array asociativo con los nombres de las imágenes
 */
function mod_imagecarousel_generate_image_names($images) {
    $keys = array_keys($images);
    $result = array_map(function($key) use ($images) {
        // Extraer 'desktop' o 'mobile' del nombre del campo filemanager
        $type = str_replace('image_file_', '', $key);
        return [$type . "_image_name" => $images[$key]["name"]];
    }, $keys);
    return array_merge(...$result);
}

/**
 * Verifica el contenido de las imágenes
 *
 * @param array $images Array con información de las imágenes
 * @return array Array con mensajes de error si hay imágenes no válidas
 */
function mod_imagecarousel_check_images($images) {
    $errors = [];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5242880; // 5MB
    
    foreach ($images as $key => $image) {
        // Verificar si la imagen existe
        if (!isset($image['tmp_name']) || !file_exists($image['tmp_name'])) {
            continue;
        }
        
        // Verificar el tipo de archivo
        if (!in_array($image['type'], $allowedTypes)) {
            $errors[] = "La imagen '$image[name]' no es un formato válido. Use JPG, PNG o WebP.";
        }
        
        // Verificar el tamaño del archivo
        if ($image['size'] > $maxSize) {
            $errors[] = "La imagen '$image[name]' excede el tamaño máximo permitido (5MB).";
        }
    }
    
    return $errors;
}

/**
 * Verifica si se han subido imágenes y las convierte a Base64
 *
 * @param object $data Datos del formulario
 * @return void
 */
function mod_imagecarousel_process_uploaded_images(&$data) {
    global $USER;
    
    // 1. Procesar archivos de filemanager
    if (!empty($data->image_file_desktop)) {
        // Procesar imagen de escritorio
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $draftitemid = $data->image_file_desktop;
        
        // Obtener archivos del área draft
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
        
        error_log("Procesando image_file_desktop: draftitemid=$draftitemid, archivos encontrados: " . count($draftfiles));
        
        foreach ($draftfiles as $file) {
            if (!$file->is_directory()) {
                error_log("Procesando archivo: " . $file->get_filename());
                // Obtener contenido del archivo y convertir a Base64
                $content = $file->get_content();
                if ($content) {
                    $data->desktop_image = base64_encode($content);
                    $data->desktop_image_name = $file->get_filename();
                    error_log("Imagen desktop convertida a Base64, tamaño: " . strlen($data->desktop_image));
                    break;
                }
            }
        }
    }
    
    // 2. Procesar imagen móvil
    if (!empty($data->image_file_mobile)) {
        // Procesar imagen móvil
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $draftitemid = $data->image_file_mobile;
        
        // Obtener archivos del área draft
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
        
        error_log("Procesando image_file_mobile: draftitemid=$draftitemid, archivos encontrados: " . count($draftfiles));
        
        foreach ($draftfiles as $file) {
            if (!$file->is_directory()) {
                error_log("Procesando archivo móvil: " . $file->get_filename() . ", tipo: " . $file->get_mimetype());
                // Obtener contenido del archivo y convertir a Base64
                $content = $file->get_content();
                if ($content) {
                    $data->mobile_image = base64_encode($content);
                    $data->mobile_image_name = $file->get_filename();
                    error_log("Imagen móvil convertida a Base64, tamaño: " . strlen($data->mobile_image));
                    break;
                } else {
                    error_log("No se pudo obtener contenido del archivo móvil");
                }
            }
        }
    } else {
        error_log("No se encontró image_file_mobile para procesar");
    }
    
    // 3. Procesar imágenes desde $_FILES (cuando no se usa filemanager)
    $images = array();
    
    if (!empty($_FILES['image_file_desktop']['name'])) {
        $images['desktop'] = $_FILES['image_file_desktop'];
    }
    
    if (!empty($_FILES['image_file_mobile']['name'])) {
        $images['mobile'] = $_FILES['image_file_mobile'];
    }
    
    if (!empty($images)) {
        error_log("Procesando imágenes desde $_FILES: " . json_encode(array_keys($images)));
        
        // Procesar cada imagen
        foreach ($images as $type => $image) {
            if (isset($image['tmp_name']) && file_exists($image['tmp_name'])) {
                $content = file_get_contents($image['tmp_name']);
                if ($content !== false) {
                    $data->{$type . '_image'} = base64_encode($content);
                    $data->{$type . '_image_name'} = $image['name'];
                    error_log("Imagen $type convertida a Base64 desde _FILES, tamaño: " . strlen($data->{$type.'_image'}));
                }
            }
        }
    }
}

/**
 * Registra elementos personalizados de formulario
 */
function mod_imagecarousel_before_standard_html_head() {
    global $CFG, $PAGE;
    
    // Registrar el elemento de color si aún no está registrado
    $colorelementfile = $CFG->dirroot . '/mod/imagecarousel/classes/form/color_element.php';
    if (file_exists($colorelementfile)) {
        require_once($colorelementfile);
    }
    
    return '';
}


