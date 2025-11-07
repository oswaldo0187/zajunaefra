<?php
/**
 * Archivo para agregar una nueva imagen al carrusel
 * Este archivo maneja la interfaz y lógica para agregar nuevas imágenes al carrusel,
 * soportando tanto versiones móviles como de escritorio.
 *
 * @package    mod_imagecarousel
 * @copyright  2024 Zajuna Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/utils/images.php');
require_once($CFG->libdir.'/formslib.php');

// Obtener el id del módulo del curso desde los parámetros de la URL
$id = required_param('id', PARAM_INT);

// Obtener información del curso y del módulo desde la base de datos
$cm = get_coursemodule_from_id('imagecarousel', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('imagecarousel', array('id' => $cm->instance), '*', MUST_EXIST);

// Verificar que el usuario esté autenticado
require_login($course, true, $cm);

// Verificar que el usuario tenga permisos para gestionar el carrusel
$context = context_module::instance($cm->id);
require_capability('mod/imagecarousel:manageitems', $context);

// Configuración de la página
$PAGE->set_url('/mod/imagecarousel/adding_image.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name) . ': ' . get_string('addnewimage', 'mod_imagecarousel'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('addnewimage', 'mod_imagecarousel'));

// Inclusión de JavaScript para controles de color y opacidad
$PAGE->requires->js_call_amd('core/form-colour', 'init', []);
$PAGE->requires->js_call_amd('theme_boost/bootstrap', 'init');
$PAGE->requires->js_amd_inline("
require(['jquery'], function($) {
    // Debug: Imprime información sobre tipos MIME soportados
    console.log('Debug ImageCarousel: Verificando soporte WebP');
    
    // Convertir el campo de texto en un selector de color
    var textColorInput = $('#id_text_color');
    
    // Crear un elemento input type=color
    var colorPicker = $('<input>', {
        type: 'color',
        id: 'color_picker_helper',
        style: 'margin-left: 10px; width: 40px; height: 40px;'
    });
    
    // Agregar el selector de color después del campo de texto
    textColorInput.after(colorPicker);
    
    // Sincronizar el valor inicial
    if (textColorInput.val()) {
        colorPicker.val(textColorInput.val());
    } else {
        colorPicker.val('#000000');
        textColorInput.val('#000000');
    }
    
    // Cuando cambia el selector de color, actualizar el campo de texto
    colorPicker.on('input change', function() {
        textColorInput.val($(this).val());
        console.log('Color seleccionado: ' + $(this).val());
    });
    
    // Cuando cambia el campo de texto, actualizar el selector de color
    textColorInput.on('input change', function() {
        var value = $(this).val();
        if (value && value.charAt(0) !== '#') {
            value = '#' + value;
            $(this).val(value);
        }
        colorPicker.val(value);
    });
    
    function updateOpacityValue(opacityId, valueId) {
        const value = $(opacityId).val();
        $(valueId).text(value + '%');
    }

    // Gestionar opacidad para background
    $('#id_text_background_opacity').on('input', function() {
        updateOpacityValue('#id_text_background_opacity', '#text_background_opacity_value');
    });

    // Inicializar valores de opacidad
    updateOpacityValue('#id_text_background_opacity', '#text_background_opacity_value');
    
    // Gestionar cambios de color del fondo del texto
    $('#text_background').on('input change', function() {
        updateColorPreview('#text_background', '.background-preview');
    });

    // Inicializar previsualizador de fondo
    var bgColor = $('#id_text_background').val() || '#ffffff';
    $('#id_text_background_preview').css('background-color', bgColor);
});
");

// URL para regresar a la página de gestión
$returnurl = new moodle_url('/mod/imagecarousel/manage.php', array('id' => $cm->id));

/**
 * Clase para la creación del formulario de nueva imagen
 * Extiende de moodleform para aprovechar las funcionalidades de formularios de Moodle
 */
class mod_imagecarousel_add_form extends moodleform {
    protected $context;
    
    public function __construct($context) {
        $this->context = $context;
        parent::__construct();
    }
    
    /**
     * Define la estructura del formulario
     * Incluye campos para:
     * - Imágenes de escritorio y móvil
     * - URLs de imágenes
     * - Texto y su personalización
     * - Posicionamiento del texto
     */
    public function definition() {
        global $CFG;
        
        $mform = $this->_form;
        
        // Campos ocultos para el id del módulo
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        
        // Sección para imágenes
        $mform->addElement('header', 'images_section', '<strong>' . get_string('images_section', 'mod_imagecarousel') . '</strong>');
        $mform->setExpanded('images_section', true);
        
        // Configuración para imagen de escritorio
        // Mostrar solo la etiqueta y un indicador de obligatorio. La descripción se mostrará
        // mediante un icono de ayuda (tooltip) para mantener la interfaz limpia.
        $mform->addElement('static', 'desktop_image_label', 
            '<strong>' . get_string('desktop_image_label', 'mod_imagecarousel') . '</strong> <span style="color: red; font-weight: bold;">*</span>', 
            '');
        // Usar la ayuda estándar de Moodle para mostrar la descripción en un icono (?).
        $mform->addHelpButton('desktop_image_label', 'desktop_image_info', 'mod_imagecarousel');
                    
        // Configuración del gestor de archivos para imagen de escritorio
        $options_desktop = array(
            'subdirs' => 0, 
            'maxbytes' => 5242880, // 5MB
            'maxfiles' => 1,
            'accepted_types' => '*'  // Aceptar cualquier tipo de archivo
        );
        $mform->addElement('filemanager', 'image_file_desktop', 
            get_string('image_file_desktop', 'mod_imagecarousel'), null, $options_desktop);
        // Mostrar ayuda específica corta para la imagen de escritorio
        $mform->addHelpButton('image_file_desktop', 'desktop_image_info', 'mod_imagecarousel');
        
        // Campo para URL de imagen de escritorio
        $mform->addElement('text', 'image_desktop', 
            get_string('image_desktop', 'mod_imagecarousel'), array('size' => '60'));
        $mform->setType('image_desktop', PARAM_URL);
        $mform->addHelpButton('image_desktop', 'image_desktop', 'mod_imagecarousel');

        // Configuración para imagen móvil
        // Mostrar solo la etiqueta y un indicador de obligatorio. La descripción se mostrará
        // mediante un icono de ayuda (tooltip) para mantener la interfaz limpia.
        $mform->addElement('static', 'mobile_image_label', 
            '<strong>' . get_string('mobile_image_label', 'mod_imagecarousel') . '</strong> <span style="color: red; font-weight: bold;">*</span>', 
            '');
        // Usar la ayuda estándar de Moodle para mostrar la descripción en un icono (?).
        $mform->addHelpButton('mobile_image_label', 'mobile_image_info', 'mod_imagecarousel');
        
        // Configuración del gestor de archivos para imagen móvil
        $options_mobile = array(
            'subdirs' => 0, 
            'maxbytes' => 5242880, // 5MB
            'maxfiles' => 1,
            'accepted_types' => '*'  // Aceptar cualquier tipo de archivo
        );
        $mform->addElement('filemanager', 'image_file_mobile', 
            get_string('image_file_mobile', 'mod_imagecarousel'), null, $options_mobile);
        // Mostrar ayuda específica corta para la imagen móvil
        $mform->addHelpButton('image_file_mobile', 'mobile_image_info', 'mod_imagecarousel');
        
        // Campo para URL de imagen móvil
        $mform->addElement('text', 'image_mobile', 
            get_string('image_mobile', 'mod_imagecarousel'), array('size' => '60'));
        $mform->setType('image_mobile', PARAM_URL);
        $mform->addHelpButton('image_mobile', 'image_mobile', 'mod_imagecarousel');
        
        // Campos para URL y texto de la imagen
        $mform->addElement('text', 'url', get_string('image_url', 'mod_imagecarousel'), array('size' => '60'));
        $mform->setType('url', PARAM_URL);
        $mform->addHelpButton('url', 'image_url', 'mod_imagecarousel');
        
        $editoroptions = array(
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => -1,
            'changeformat' => 0,
            'context' => $this->context,
            'noclean' => 0,
            'trusttext' => 0
        );
        $mform->addElement('editor', 'text_editor', get_string('text', 'mod_imagecarousel'), null, $editoroptions);
        $mform->setType('text_editor', PARAM_RAW);
        $mform->addHelpButton('text_editor', 'text', 'mod_imagecarousel');
        
        $mform->addElement('text', 'text_url', get_string('text_url', 'mod_imagecarousel'), array('size' => '60'));
        $mform->setType('text_url', PARAM_URL);
        $mform->addHelpButton('text_url', 'text_url', 'mod_imagecarousel');
        
        // Sección de personalización del texto
        $mform->addElement('header', 'text_customization', 
            '<strong>' . get_string('text_customization', 'mod_imagecarousel') . '</strong>');
        $mform->setExpanded('text_customization', true);
        
        // Información sobre unidades de medida
        $mform->addElement('static', 'size_units_info', 
            get_string('size_units_info', 'mod_imagecarousel'),
            get_string('size_units_help', 'mod_imagecarousel'));
        
        // Color del texto
        // Se obtiene el color desde _customdata cuando está disponible o usa el valor predeterminado
        $colorvalue = isset($this->_customdata['current_color']) ? $this->_customdata['current_color'] : '#000000';

        // Campo oculto para que Moodle lo reconozca
        $mform->addElement('hidden', 'text_color', $colorvalue);
        $mform->setType('text_color', PARAM_TEXT);

        // Ahora el HTML personalizado del input visual
        $mform->addElement('html', '
            <div class="form-group row fitem">
                <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                    <label class="d-inline word-break" for="text_color_picker">' . get_string('text_color', 'mod_imagecarousel') . '</label>
                </div>
                <div class="col-md-9 form-inline align-items-start felement">
                    <div class="form-group">
                        <div class="d-flex align-items-center">
                            <div class="input-group bootstrap-colorpicker" style="width: px;">
                                <input type="color" id="text_color_picker" class="form-control"
                                    value="' . $colorvalue  . '"
                                    style="width: 50px; padding: 4px;">
                            </div>
                            <span id="color_code" class="ml-2" style="min-width: 70px;">' . $colorvalue . '</span>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const picker = document.getElementById("text_color_picker");
                    const hidden = document.getElementsByName("text_color")[0];
                    const code = document.getElementById("color_code");

                    picker.addEventListener("input", function () {
                        hidden.value = this.value;
                        code.textContent = this.value;
                    });
                });
            </script>
        ');
        // Finaliza ajuste color del texto

        // Configuración de tamaño del texto
        $mform->addElement('text', 'text_size', 
            get_string('text_size', 'mod_imagecarousel'));
        $mform->setType('text_size', PARAM_TEXT);
        $mform->addHelpButton('text_size', 'text_size', 'mod_imagecarousel');
        $mform->setDefault('text_size', get_string('default_text_size', 'mod_imagecarousel'));
        
        // Posición del texto
        $positions = array(
            'top-left' => get_string('position_top_left', 'mod_imagecarousel'),
            'top-center' => get_string('position_top_center', 'mod_imagecarousel'),
            'top-right' => get_string('position_top_right', 'mod_imagecarousel'),
            'center-left' => get_string('position_center_left', 'mod_imagecarousel'),
            'center' => get_string('position_center', 'mod_imagecarousel'),
            'center-right' => get_string('position_center_right', 'mod_imagecarousel'),
            'bottom-left' => get_string('position_bottom_left', 'mod_imagecarousel'),
            'bottom-center' => get_string('position_bottom_center', 'mod_imagecarousel'),
            'bottom-right' => get_string('position_bottom_right', 'mod_imagecarousel')
        );
        $mform->addElement('select', 'text_position', 
            get_string('text_position', 'mod_imagecarousel'), $positions);
        $mform->setDefault('text_position', 'center');
        $mform->addHelpButton('text_position', 'text_position', 'mod_imagecarousel');
        
        // Ajuste fino de posición
        $mform->addElement('header', 'position_adjustment', '<strong>' .
            get_string('position_adjustment', 'mod_imagecarousel') . '</strong>');
        $mform->setExpanded('position_adjustment', true);
        
        $customposition = array(
             $mform->createElement('text', 'text_position_adjust[top]', 
                 get_string('position_adjust_top', 'mod_imagecarousel'),
                 array('placeholder' => get_string('position_adjust_top_placeholder', 'mod_imagecarousel'), 'size' => '8')),
            $mform->createElement('text', 'text_position_adjust[right]', 
                get_string('position_adjust_right', 'mod_imagecarousel'),
                array('placeholder' => get_string('position_adjust_right_placeholder', 'mod_imagecarousel'), 'size' => '8'))
            // $mform->createElement('text', 'text_position_adjust[bottom]', 
            //     get_string('position_adjust_bottom', 'mod_imagecarousel'),
            //     array('placeholder' => get_string('position_adjust_bottom_placeholder', 'mod_imagecarousel'), 'size' => '8'))
            // $mform->createElement('text', 'text_position_adjust[left]', 
            //     get_string('position_adjust_left', 'mod_imagecarousel'),
            //     array('placeholder' => get_string('position_adjust_left_placeholder', 'mod_imagecarousel'), 'size' => '8'))
        );
        
        $mform->addGroup($customposition, 'text_position_adjust_group', 
            get_string('position_adjustment_desc', 'mod_imagecarousel'), ' ', false);
        $mform->addHelpButton('text_position_adjust_group', 'position_adjustment', 'mod_imagecarousel');
        
        // Establecer el tipo de datos para cada campo de ajuste de posición
        $mform->setType('text_position_adjust[top]', PARAM_TEXT);
        $mform->setType('text_position_adjust[right]', PARAM_TEXT);
        // $mform->setType('text_position_adjust[bottom]', PARAM_TEXT);
        // $mform->setType('text_position_adjust[left]', PARAM_TEXT);
        
        // Establecer valores por defecto para cada campo de ajuste de posición
        $mform->setDefault('text_position_adjust[top]', get_string('default_position_top', 'mod_imagecarousel'));
        $mform->setDefault('text_position_adjust[right]', get_string('default_position_right', 'mod_imagecarousel'));
        // $mform->setDefault('text_position_adjust[bottom]', get_string('default_position_bottom', 'mod_imagecarousel'));
        // $mform->setDefault('text_position_adjust[left]', get_string('default_position_left', 'mod_imagecarousel'));
        
        // Estilos del texto
        $textstyles = array(
            $mform->createElement('checkbox', 'text_style[bold]', '', 
                get_string('text_bold', 'mod_imagecarousel'),
                array('class' => 'mr-1')),
            $mform->createElement('checkbox', 'text_style[italic]', '', 
                get_string('text_italic', 'mod_imagecarousel'),
                array('class' => 'mx-1')),
            $mform->createElement('checkbox', 'text_style[underline]', '', 
                get_string('text_underline', 'mod_imagecarousel'),
                array('class' => 'ml-1'))
        );
        
        $mform->addGroup($textstyles, 'text_style_group', 
            get_string('text_style', 'mod_imagecarousel'), '&nbsp;&nbsp;&nbsp;', false);
        $mform->addHelpButton('text_style_group', 'text_style', 'mod_imagecarousel');
        
        // Fondo del texto
        // Aplicar fondo del texto  
        $backgroundvalue = isset($this->_customdata['current_text_background']) ? $this->_customdata['current_text_background'] : '#ffffff';

        // Campo oculto para que Moodle lo reconozca
        $mform->addElement('hidden', 'text_background', $backgroundvalue);
        $mform->setType('text_background', PARAM_TEXT);

        $mform->addElement('html', '
            <div class="form-group row fitem">
                <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                    <label class="d-inline word-break" for="text_background_picker">' . get_string('text_background', 'mod_imagecarousel') . '</label>
                </div>
                <div class="col-md-9 form-inline align-items-start felement">
                    <div class="form-group">
                        <div class="d-flex align-items-center">
                            <div class="input-group bootstrap-colorpicker" style="width: px;">
                                <input type="color" id="text_background_picker" class="form-control"
                                    value="' . $backgroundvalue  . '"
                                    style="width: 50px; padding: 4px;">
                            </div>
                            <span id="color_code2" class="ml-2" style="min-width: 70px;">' . $backgroundvalue . '</span>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const picker = document.getElementById("text_background_picker");
                    const hidden = document.getElementsByName("text_background")[0];
                    const code = document.getElementById("color_code2");

                    picker.addEventListener("input", function () {
                        hidden.value = this.value;
                        code.textContent = this.value;
                    });
                });
            </script>
        ');

        // Opacidad del fondo
        $mform->addElement('hidden', 'text_background_opacity', $image->text_background_opacity ?? 100);
        $mform->setType('text_background_opacity', PARAM_INT);        
        $mform->addElement('html', '
            <div class="form-group row fitem">
                <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                    <label class="d-inline word-break" for="text_background_opacity">' . get_string('text_background_opacity', 'mod_imagecarousel') . '</label>
                </div>
                <div class="col-md-9 form-inline align-items-start felement">
                    <div class="form-group">
                        <input type="range" min="0" max="100" id="text_background_opacity_range" value="' . ($image->text_background_opacity ?? 100) . '" />
                        <span id="opacity_value">' . ($image->text_background_opacity ?? 100) . '%</span>
                    </div>
                </div>
            </div>
             <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const range = document.getElementById("text_background_opacity_range");
                    const hidden = document.getElementsByName("text_background_opacity")[0];
                    const label = document.getElementById("opacity_value");

                    range.addEventListener("input", function () {
                        hidden.value = this.value;
                        label.textContent = this.value + "%";
                    });
                });
            </script>
        ');
        
        // Padding del texto
        $mform->addElement('text', 'text_padding', 
            get_string('text_padding', 'mod_imagecarousel'));
        $mform->setType('text_padding', PARAM_TEXT);
        $mform->addHelpButton('text_padding', 'text_padding', 'mod_imagecarousel');
        $mform->setDefault('text_padding', get_string('default_text_padding', 'mod_imagecarousel'));
        
        // Radio del borde del texto
        $mform->addElement('text', 'text_border_radius', 
            get_string('text_border_radius', 'mod_imagecarousel'));
        $mform->setType('text_border_radius', PARAM_TEXT);
        $mform->addHelpButton('text_border_radius', 'text_border_radius', 'mod_imagecarousel');
        $mform->setDefault('text_border_radius', get_string('default_text_border_radius', 'mod_imagecarousel'));
        
        // Leyenda para campos obligatorios
        $mform->addElement('static', 'required_fields_note', '', 
            '<div class="form-group mt-3 mb-3">
                <strong>
                    <span style="color: red; font-weight: bold;">*</span> ' . get_string('required_field', 'mod_imagecarousel') . '
                </strong>
            </div>');
        
        // Botones de acciones
        $this->add_action_buttons(true, get_string('savechanges'));
    }
    /**
     * Validación del formulario
     * Verifica que al menos una imagen haya sido subida o proporcionada
     * y valida los tipos de imagen permitidos.
     */
    function validation($data, $files) {
        global $USER;
        
        $errors = parent::validation($data, $files);
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        
        // Validar el límite de 300 palabras en el campo de texto
        if (!empty($data['text_editor']['text'])) {
            $wordCount = str_word_count(strip_tags($data['text_editor']['text']));
            if ($wordCount > 300) {
                $errors['text_editor'] = get_string('error_text_word_limit', 'mod_imagecarousel');
            }
        }
        
        // Verificar archivos de escritorio
        $draftitemid_desktop = file_get_submitted_draft_itemid('image_file_desktop');
        $has_desktop_files = false;
        if ($draftitemid_desktop > 0) {
            $files_desktop = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid_desktop, 'id', false);
            
            foreach ($files_desktop as $file) {
                if (!$file->is_directory()) {
                    $has_desktop_files = true;
                    break;
                }
            }
        }
        
        // Verificar archivos móviles
        $has_mobile_files = false;
        $draftitemid_mobile = file_get_submitted_draft_itemid('image_file_mobile');
        if ($draftitemid_mobile > 0) {
            $files_mobile = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid_mobile, 'id', false);
            
            foreach ($files_mobile as $file) {
                if (!$file->is_directory()) {
                    $has_mobile_files = true;
                    break;
                }
            }
        }
        
        // Solo mostrar error si no hay ninguna imagen (ni escritorio ni móvil)
        if ((empty($data['image_desktop']) && !$has_desktop_files) && 
            (empty($data['image_mobile']) && !$has_mobile_files)) {
            $errors['image_file_desktop'] = get_string('error_no_image', 'mod_imagecarousel');
        }
        
        // Validar tipos de imagen para escritorio
        if ($has_desktop_files) {
            foreach ($files_desktop as $file) {
                if (!$file->is_directory()) {
                    $mimetype = $file->get_mimetype();
                    $allowedtypes = array('image/jpeg', 'image/png', 'image/webp');
                    
                    // Verificar si es un archivo WebP que Moodle detecta incorrectamente
                    $filename = $file->get_filename();
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $is_webp_by_extension = ($extension === 'webp');
                    
                    if (!in_array($mimetype, $allowedtypes) && !($is_webp_by_extension && $mimetype === 'document/unknown')) {
                        $errors['image_file_desktop'] = get_string('error_invalid_image', 'mod_imagecarousel') . 
                            ' [Debug: Tipo detectado: ' . $mimetype . ']. Solo se permiten imágenes JPEG, PNG o WebP.';
                        break;
                    }
                    
                    $filepath = $file->copy_content_to_temp();
                    if ($filepath) {
                        $imageinfo = @getimagesize($filepath);
                        
                        // Para archivos WebP, getimagesize puede fallar en algunas versiones de PHP
                        // Verificamos directamente si es un archivo WebP válido
                        if (!$imageinfo && $is_webp_by_extension) {
                            // Verificar si el archivo comienza con la firma de WebP (RIFF....WEBP)
                            $is_valid_webp = false;
                            $handle = @fopen($filepath, 'rb');
                            if ($handle) {
                                $header = fread($handle, 12);
                                fclose($handle);
                                
                                // La firma de WebP consiste en "RIFF" seguido de 4 bytes de tamaño y "WEBP"
                                if (strlen($header) >= 12 && substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP') {
                                    $is_valid_webp = true;
                                    // Para validar altura de WebP sin getimagesize, podemos usar otras bibliotecas
                                    // pero por ahora, vamos a confiar en la extensión y la firma
                                    // y saltarnos la validación de dimensiones para WebP
                                }
                            }
                            
                            if (!$is_valid_webp) {
                                $errors['image_file_desktop'] = get_string('error_invalid_image', 'mod_imagecarousel');
                            }
                        } else if (!$imageinfo) {
                            $errors['image_file_desktop'] = get_string('error_invalid_image', 'mod_imagecarousel');
                        } else {
                            // La validación de altura de 720px se ha desactivado para permitir imágenes de cualquier altura
                            // incluyendo WebP y otros formatos
                        }
                        @unlink($filepath);
                    }
                }
            }
        }
        
        // Validar tipos de imagen para móvil
        if ($has_mobile_files) {
            foreach ($files_mobile as $file) {
                if (!$file->is_directory()) {
                    $mimetype = $file->get_mimetype();
                    $allowedtypes = array('image/jpeg', 'image/png', 'image/webp');
                    
                    // Verificar si es un archivo WebP que Moodle detecta incorrectamente
                    $filename = $file->get_filename();
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $is_webp_by_extension = ($extension === 'webp');
                    
                    if (!in_array($mimetype, $allowedtypes) && !($is_webp_by_extension && $mimetype === 'document/unknown')) {
                        $errors['image_file_mobile'] = get_string('error_invalid_image', 'mod_imagecarousel') . 
                            ' [Debug: Tipo detectado: ' . $mimetype . ']. Solo se permiten imágenes JPEG, PNG o WebP.';
                        break;
                    }
                    
                    $filepath = $file->copy_content_to_temp();
                    if ($filepath) {
                        $imageinfo = @getimagesize($filepath);
                        
                        // Para archivos WebP, getimagesize puede fallar en algunas versiones de PHP
                        // Verificamos directamente si es un archivo WebP válido
                        if (!$imageinfo && $is_webp_by_extension) {
                            // Verificar si el archivo comienza con la firma de WebP (RIFF....WEBP)
                            $is_valid_webp = false;
                            $handle = @fopen($filepath, 'rb');
                            if ($handle) {
                                $header = fread($handle, 12);
                                fclose($handle);
                                
                                // La firma de WebP consiste en "RIFF" seguido de 4 bytes de tamaño y "WEBP"
                                if (strlen($header) >= 12 && substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP') {
                                    $is_valid_webp = true;
                                    // Para móvil no verificamos altura específica
                                }
                            }
                            
                            if (!$is_valid_webp) {
                                $errors['image_file_mobile'] = get_string('error_invalid_image', 'mod_imagecarousel');
                            }
                        } else if (!$imageinfo) {
                            $errors['image_file_mobile'] = get_string('error_invalid_image', 'mod_imagecarousel');
                        }
                        @unlink($filepath);
                    }
                }
            }
        }
        
        return $errors;
    }
}

 // Añadir el color actual para el selector de color
 $customdata['current_color'] = $image->text_color ?? '#000000';
 // Añadir el color de fondo actual para el selector de color
 $customdata['current_text_background'] = $image->text_background ?? '#ffffff';

// Crear instancia del formulario con los datos personalizados
$mform = new mod_imagecarousel_add_form(null, $customdata);

// Configurar datos iniciales del formulario
$mform->set_data(array(
    'cmid' => $cm->id,
    'id' => $id
));

// Procesar el formulario
if ($mform->is_cancelled()) {
    // Si se cancela, volver a la página de gestión
    redirect($returnurl);
} else if ($fromform = $mform->get_data()) {
    // Procesar los datos del formulario
    $timenow = time();
    
    try {
        // Procesar las imágenes subidas y convertirlas a Base64
        mod_imagecarousel_process_uploaded_images($fromform);
        
        // Crear un registro para la tabla imagecarousel_images
        $imagerecord = new stdClass();
        $imagerecord->carouselid = $cm->instance;
        
        // Asignar las imágenes desde los archivos subidos o desde las URLs proporcionadas
        // Para imagen desktop
        if (isset($fromform->desktop_image)) {
            // Si se procesó un archivo subido
            $imagerecord->desktop_image = $fromform->desktop_image;
            $imagerecord->desktop_image_name = isset($fromform->desktop_image_name) ? $fromform->desktop_image_name : null;
        } else if (!empty($fromform->image_desktop)) {
            // Si se proporcionó una URL directamente
            $imagerecord->desktop_image = $fromform->image_desktop;
            $imagerecord->desktop_image_name = basename($fromform->image_desktop);
        } else {
            $imagerecord->desktop_image = null;
            $imagerecord->desktop_image_name = null;
        }
        
        // Para imagen mobile
        if (isset($fromform->mobile_image)) {
            // Si se procesó un archivo subido
            $imagerecord->mobile_image = $fromform->mobile_image;
            $imagerecord->mobile_image_name = isset($fromform->mobile_image_name) ? $fromform->mobile_image_name : null;
        } else if (!empty($fromform->image_mobile)) {
            // Si se proporcionó una URL directamente
            $imagerecord->mobile_image = $fromform->image_mobile;
            $imagerecord->mobile_image_name = basename($fromform->image_mobile);
        } else {
            $imagerecord->mobile_image = null;
            $imagerecord->mobile_image_name = null;
        }
        
        // Modificar la URL principal para asegurar que URLs externas tengan el protocolo correcto
        if (isset($fromform->url) && !empty($fromform->url)) {
            $url = $fromform->url;
            // Verificar si la URL ya tiene un protocolo
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                // No tiene protocolo, verificar si parece una URL externa
                if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
                    // Es probablemente una URL externa, agregar https://
                    $url = 'https://' . $url;
                }
            }
            $imagerecord->url = $url;
        } else {
            $imagerecord->url = null;
        }
        
        $imagerecord->text = isset($fromform->text_editor['text']) ? $fromform->text_editor['text'] : null;
        
        // Modificar la URL del texto para asegurar que URLs externas tengan el protocolo correcto
        if (isset($fromform->text_url) && !empty($fromform->text_url)) {
            $url = $fromform->text_url;
            // Verificar si la URL ya tiene un protocolo
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                // No tiene protocolo, verificar si parece una URL externa
                if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
                    // Es probablemente una URL externa, agregar https://
                    $url = 'https://' . $url;
                }
            }
            $imagerecord->text_url = $url;
        } else {
            $imagerecord->text_url = null;
        }
        
        // Procesar el color del texto
        if (!empty($fromform->text_color)) {
            // Validar que sea un color hexadecimal
            $color = trim($fromform->text_color);
            // Asegurarse de que empiece con #
            if ($color[0] !== '#') {
                $color = '#' . $color;
            }
            // Asegurarse de que sea un color hexadecimal válido
            if (preg_match('/^#([a-fA-F0-9]{3}){1,2}$/', $color)) {
                $imagerecord->text_color = $color;
            } else {
                // Si no es válido, usar el valor por defecto
                $imagerecord->text_color = '#000000';
            }
        } else {
            // Si está vacío, usar el valor por defecto
            $imagerecord->text_color = '#000000';
        }
        
        $imagerecord->text_size = isset($fromform->text_size) ? $fromform->text_size : null;
        $imagerecord->text_position = isset($fromform->text_position) ? $fromform->text_position : null;
        
        // Ajustes de posición
        if (isset($fromform->text_position_adjust['top'])) {
            $imagerecord->text_position_top = $fromform->text_position_adjust['top'];
        }
        if (isset($fromform->text_position_adjust['right'])) {
            $imagerecord->text_position_right = $fromform->text_position_adjust['right'];
        }
        if (isset($fromform->text_position_adjust['bottom'])) {
            $imagerecord->text_position_bottom = $fromform->text_position_adjust['bottom'];
        }
        if (isset($fromform->text_position_adjust['left'])) {
            $imagerecord->text_position_left = $fromform->text_position_adjust['left'];
        }
        
        // Estilos de texto
        if (isset($fromform->text_style['bold'])) {
            $imagerecord->text_style_bold = $fromform->text_style['bold'];
        }
        if (isset($fromform->text_style['italic'])) {
            $imagerecord->text_style_italic = $fromform->text_style['italic'];
        }
        if (isset($fromform->text_style['underline'])) {
            $imagerecord->text_style_underline = $fromform->text_style['underline'];
        }
        
        // Fondo del texto
        $imagerecord->text_background = isset($fromform->text_background) ? $fromform->text_background : null;
        $imagerecord->text_background_opacity = isset($fromform->text_background_opacity) ? $fromform->text_background_opacity : 0;
        $imagerecord->text_padding = isset($fromform->text_padding) ? $fromform->text_padding : null;
        $imagerecord->text_border_radius = isset($fromform->text_border_radius) ? $fromform->text_border_radius : null;
        
        // Obtener el máximo valor actual de sortorder y agregar 10
        $max_sortorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {imagecarousel_images} WHERE carouselid = ?', array($cm->instance));
        $imagerecord->sortorder = ($max_sortorder !== null) ? $max_sortorder + 10 : 10;
        
        // Crear una nueva imagen
        $DB->insert_record('imagecarousel_images', $imagerecord);
        
        // Actualizar tiempo de modificación del módulo
        $DB->set_field('imagecarousel', 'timemodified', $timenow, array('id' => $cm->instance));
        
        // Redirigir a la página de gestión con mensaje de éxito
        redirect($returnurl, get_string('image_added', 'mod_imagecarousel'), null, \core\output\notification::NOTIFY_SUCCESS);
    } catch (Exception $e) {
        // Mostrar mensaje de error y mantener al usuario en el formulario
        \core\notification::error($e->getMessage());
    }
}

// Mostrar el formulario
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('addnewimage', 'mod_imagecarousel'));
$mform->display();
echo $OUTPUT->footer();
