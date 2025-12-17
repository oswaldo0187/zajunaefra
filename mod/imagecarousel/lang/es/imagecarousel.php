<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    mod_imagecarousel
 * @copyright  2024 Zajuna Team
 * @author     Zajuna Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Strings básicos del módulo
$string['modulename'] = 'Carrusel de imágenes';
$string['modulenameplural'] = 'Carruseles de imágenes';
$string['modulename_help'] = 'El módulo Carrusel de Imágenes te permite crear presentaciones de imágenes en tu curso.';
$string['pluginname'] = 'Carrusel de imágenes';
$string['pluginadministration'] = 'Administración del carrusel de imágenes';
$string['availability'] = 'Disponibilidad';
$string['availablefrom'] = 'Fecha y hora de disponibilidad';
$string['availablefrom_help'] = 'Seleccione la fecha y hora a partir de la cual el carrusel será visible para los estudiantes.';
$string['availableuntil'] = 'Fecha y hora de caducidad';
$string['availableuntil_help'] = 'Seleccione la fecha y hora hasta la cual el carrusel permanecerá visible. Dejar vacío para no establecer límite.';
$string['availableuntil_error'] = 'La fecha y hora de caducidad no puede ser anterior a la fecha y hora de disponibilidad.';
$string['imagecarousel'] = 'Carrusel de Imágenes';
$string['imagecarouselname'] = 'Nombre';
$string['imagecarouselname_help'] = 'Nombre del carrusel de imágenes';
$string['imagecarouselsettings'] = 'Configuración general';
$string['imagecarousel:addinstance'] = 'Agregar un nuevo carrusel de imágenes';
$string['imagecarousel:view'] = 'Ver carrusel de imágenes';

$string['manageimages'] = 'Administrar Imágenes';
$string['addnewimage'] = 'Agregar nueva imagen';

// Cadenas de la tabla
$string['position'] = 'Posición';
$string['preview'] = 'Vista previa';
$string['image_url'] = 'URL de la imagen';
$string['text'] = 'Texto';
$string['text_url'] = 'URL del texto';
$string['actions'] = 'Acciones';
$string['moveup'] = 'Mover arriba';
$string['movedown'] = 'Mover abajo';
$string['visibility'] = 'Visibilidad';
$string['visible'] = 'Visible';
$string['hidden'] = 'Oculta';
$string['image_visibility_enabled'] = 'La imagen se mostrará en el carrusel';
$string['image_visibility_disabled'] = 'La imagen ya no se mostrará en el carrusel';
$string['image_visibility_toggle_error'] = 'No se pudo actualizar la visibilidad de la imagen';

// Mensajes
$string['position_warning'] = 'Nota: Al usar las flechas arriba/abajo podrás reordenar las imágenes. Los cambios se aplicarán de forma inmediata.';

// Cadenas del formulario de edición
$string['edit_image'] = 'Editar imagen';
$string['add_image'] = 'Agregar nueva imagen';
$string['image_url_help'] = 'Ingrese la URL de redirección al dar click sobre la imagen del carrusel. Ejemplo: www.google.com (redirección a google)';
$string['text_help'] = 'Texto dinámico para mostrar sobre la imagen. Favor tener en cuenta la personalización de color, tamaño, posición y estilo del texto para una mejor visualización.';
$string['text_url_help'] = 'Ingrese la URL de redirección al dar click sobre el texto dinámico del carrusel. Ejemplo: www.google.com (redirección a google)';
$string['save_changes'] = 'Guardar cambios';
$string['cancel'] = 'Cancelar';
$string['file_picker'] = 'Selector de archivos';
$string['file_picker_help'] = 'Puedes subir una imagen para el carrusel o seleccionar una del selector de archivos. El tamaño recomendado es de 1920x720 píxeles para escritorio y 1680x720 píxeles para móvil. Si subes un archivo, el campo URL de imagen será ignorado.';
$string['file_area_description'] = 'Archivos de imágenes del Slider Informativo';
$string['image_requirements'] = 'Imágenes del Slider Informativo';
$string['image_url_optional'] = 'Alternativamente, puedes proporcionar una URL de imagen externa';
$string['error_no_image'] = 'Debes subir una imagen o proporcionar una URL de imagen';
$string['error_invalid_image'] = 'El archivo no es una imagen válida. Formatos aceptados: JPG, PNG, WEBP.';
$string['error_empty_title'] = 'El título del carrusel no puede estar vacío';

// Personalización del texto
$string['text_customization'] = 'Personalización del texto';
$string['text_color'] = 'Color del texto';
$string['text_color_help'] = 'Color del texto que aparece sobre la imagen';
$string['text_size'] = 'Tamaño del texto';
$string['text_size_help'] = 'Tamaño del texto en em (ej: 1em = 16px). Si usted desea usar texto responsivo, puede usar unidades em. Si desea usar un tamaño fijo, puede usar unidades px.';
$string['text_size_explanation'] = 'Si usted desea usar texto responsivo, puede usar unidades em. Si desea usar un tamaño fijo, puede usar unidades px.';
$string['text_position'] = 'Posición base';
$string['text_position_help'] = 'Selecciona la posición inicial donde se colocará el texto en la imagen';
$string['text_position_custom'] = 'Posición personalizada';
$string['text_position_custom_help'] = 'Valores de posición personalizados (arriba, derecha, abajo, izquierda)';
$string['text_background'] = 'Fondo del texto';
$string['text_background_explanation'] = 'Elige la opacidad para el fondo del texto';
$string['text_padding'] = 'Relleno del texto';
$string['text_padding_help'] = 'Relleno alrededor del texto (ej: 0.25em = 4px)';
$string['text_border_radius'] = 'Radio del borde';
$string['text_border_radius_help'] = 'Radio del borde del contenedor del texto (ej: 0.25em = 4px)';

// Opciones de estilo del texto
$string['text_style'] = 'Estilo del texto';
$string['text_style_help'] = 'Opciones de estilo para el texto generado dinámicamente';
$string['text_bold'] = 'Negrita';
$string['text_italic'] = 'Cursiva';
$string['text_underline'] = 'Subrayado';

// Opciones de posición
$string['position_top_left'] = 'Superior izquierda';
$string['position_top_center'] = 'Superior centro';
$string['position_top_right'] = 'Superior derecha';
$string['position_center_left'] = 'Centro izquierda';
$string['position_center'] = 'Centro';
$string['position_center_right'] = 'Centro derecha';
$string['position_bottom_left'] = 'Inferior izquierda';
$string['position_bottom_center'] = 'Inferior centro';
$string['position_bottom_right'] = 'Inferior derecha';
$string['position_custom'] = 'Posición personalizada';

// Etiquetas de posición
$string['position_top'] = 'Arriba';
$string['position_right'] = 'Derecha';
$string['position_bottom'] = 'Abajo';
$string['position_left'] = 'Izquierda';

// Placeholders de posición
$string['position_top_placeholder'] = 'Distancia desde arriba (ej: 10px)';
$string['position_right_placeholder'] = 'Distancia desde la derecha (ej: 20px)';
$string['position_bottom_placeholder'] = 'Distancia desde abajo (ej: 30px)';
$string['position_left_placeholder'] = 'Distancia desde la izquierda (ej: 40px)';

// Ajuste de posición
$string['position_adjustment'] = 'Ajuste fino de posición';
$string['position_adjustment_desc'] = 'Ajusta la posición del texto desde la posición base';
$string['position_adjust_top'] = 'Desde arriba';
$string['position_adjust_right'] = 'Desde la derecha';
$string['position_adjust_bottom'] = 'Desde abajo';
$string['position_adjust_left'] = 'Desde la izquierda';
$string['position_adjust_top_placeholder'] = '↓↑ ±10px';
$string['position_adjust_right_placeholder'] = '←→ ±10px';
// $string['position_adjust_bottom_placeholder'] = '↑↓ ±10px';
// $string['position_adjust_left_placeholder'] = '← ±10px';
$string['position_adjustment_help'] = 'Ajusta la distancia desde la posición base seleccionada. La primera casilla representa el eje Y (desplazamiento vertical) y la segunda casilla representa el eje X (desplazamiento horizontal).
Valores positivos mueven el texto hacia abajo/derecha. Valores negativos mueven el texto hacia arriba/izquierda. NOTA: Para mantener el texto de forma responsiva, se recomienda usar unidades em.';

$string['text_color_opacity'] = 'Opacidad del texto';
$string['text_background_opacity'] = 'Opacidad del fondo';

// Valores por defecto
$string['default_text_size'] = '1em';
$string['default_text_size_explanation'] = 'Si usted desea usar texto responsivo, puede usar unidades em. Si desea usar un tamaño fijo, puede usar unidades px.';
$string['default_text_padding'] = '0em';
$string['default_text_border_radius'] = '0em';
$string['default_position_top'] = '±0em ↓↑';
$string['default_position_right'] = '±0em ←→';
// $string['default_position_bottom'] = '±0em ↓';
// $string['default_position_left'] = '±0em ←';

// Información de unidades de medida
$string['size_units_info'] = 'Información sobre unidades de medida';
$string['size_units_help'] = 'Puedes usar diferentes unidades CSS para los tamaños:
• px (píxeles): Tamaño fijo (ej: 16px)
• em: Relativo al tamaño del elemento padre (ej: 1.2em)
• rem: Relativo al tamaño del elemento raíz (ej: 1.2rem)
• %: Porcentaje del elemento padre (ej: 120%)
• vw/vh: Relativo al ancho/alto de la ventana (ej: 5vw)';

$string['delete_image'] = 'Eliminar imagen';
$string['delete_image_confirmation'] = 'Confirmar eliminación';
$string['delete_image_confirmation_desc'] = '¿Está seguro de que desea eliminar esta imagen del carrusel?';
$string['image_deleted'] = 'Imagen eliminada correctamente';
$string['delete_error'] = 'Error al eliminar la imagen';
$string['image_moved_up'] = 'Imagen movida hacia arriba';
$string['image_moved_down'] = 'Imagen movida hacia abajo';
$string['save_changes'] = 'Cambios guardados correctamente';

$string['file_upload'] = 'Subir imagen';

// Soporte para versiones de escritorio y móvil
$string['images_section'] = 'Imágenes del Slider Informativo';
$string['desktop_image_label'] = 'Imagen para escritorio';
$string['desktop_image_info'] = 'imagen de escritorio.';
$string['desktop_image_info_help'] = 'Esta imagen se mostrará en dispositivos de pantalla grande (computadoras, tablets). La resolución recomendada es de 1920×720 píxeles. <strong>Favor de cargar al menos una imagen o URL de imagen</strong>';
$string['mobile_image_label'] = 'Imagen para móvil';
$string['mobile_image_info'] = 'imagen móvil.';
$string['mobile_image_info_help'] = 'Esta imagen se mostrará en dispositivos móviles. Si no se proporciona, se usará la imagen de escritorio en todos los dispositivos. La resolución recomendada es de 1680×720 píxeles (horizontal móvil). <strong>Favor de cargar al menos una imagen o URL de imagen</strong>';
$string['image_file_desktop'] = 'Subir imagen para escritorio';
$string['image_desktop'] = 'O usar URL para imagen de escritorio';
$string['image_desktop_help'] = 'Aquí puedes ingresar una URL para la imagen de escritorio';
$string['image_file_mobile'] = 'Subir imagen para móvil';
$string['image_mobile'] = 'O usar URL para imagen móvil';
$string['image_mobile_help'] = 'Aquí puedes ingresar una URL para la imagen para dispositivos móviles';
$string['error_no_desktop_image'] = 'Debes subir una imagen o proporcionar una URL para la versión de escritorio';
$string['error_text_word_limit'] = 'El texto no puede exceder las 70 palabras';

// Capacidades
$string['imagecarousel:addinstance'] = 'Añadir un nuevo carrusel de imágenes';
$string['imagecarousel:view'] = 'Ver carrusel de imágenes';
$string['imagecarousel:manageitems'] = 'Gestionar imágenes del Slider Informativo';

// Errores adicionales
$string['no_image_specified'] = 'No se ha especificado qué imagen editar';
$string['image_not_found'] = 'La imagen solicitada no existe';
$string['invalid_action'] = 'Acción no válida';
$string['noimagesfound'] = 'No se encontraron imágenes en este carrusel';
$string['error_no_file_uploaded'] = 'No se encontró el archivo subido. Por favor, seleccione una imagen.';
$string['error_no_desktop_image'] = 'Debes subir una imagen o proporcionar una URL para la versión de escritorio';
$string['error_no_image'] = 'Debes subir al menos una imagen (escritorio o móvil) o proporcionar una URL';


// Eliminación de imágenes
$string['delete_image_title'] = 'Eliminar imagen';
$string['delete_image_confirm'] = '¿Estás seguro de que deseas eliminar esta imagen? Esta acción no se puede deshacer.';
$string['image_deleted'] = 'Imagen eliminada con éxito.';
$string['error_deleting_image'] = 'Error al eliminar la imagen.';

// Image creation and updating
$string['image_added'] = 'Imagen agregada correctamente.';
$string['image_updated'] = 'Imagen actualizada correctamente';
$string['imagesaved'] = 'Imagen guardada correctamente.';

// Sección de imágenes actuales
$string['current_images'] = 'Imágenes actuales';
$string['desktop_image_label'] = 'Imagen de escritorio';
$string['mobile_image_label'] = 'Imagen móvil';

// Errores de validación
$string['invalid_image_id'] = 'ID de imagen no válido o no especificado';
$string['error_desktop_height'] = 'Se recomienda para la imagen de escritorio una altura de 720px.';

// Indicadores de campos del formulario
$string['required_field'] = 'Obligatorio';