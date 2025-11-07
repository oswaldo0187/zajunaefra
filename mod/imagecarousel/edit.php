<?php
require('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/utils/images.php');
require_once("$CFG->libdir/formslib.php");

/**
 * Página para agregar o editar imágenes del carrusel
 * 
 * @package    mod_imagecarousel
 * @copyright  2024 Zajuna Team
 * @author     Zajuna Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Obtener parámetros
$id = required_param('id', PARAM_INT);
$imageid = optional_param('imageid', -1, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA); // Cambiado a string vacío por defecto

// Obtener la información del curso y del módulo
$cm = get_coursemodule_from_id('imagecarousel', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('imagecarousel', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

// Verificar que el usuario tenga permisos para gestionar el carrusel
$context = context_module::instance($cm->id);
require_capability('mod/imagecarousel:manageitems', $context);

// Determinamos la acción basada en el ID de la imagen (editar o agregar)
if ($imageid > 0) {
    $action = 'edit';
} else {
    $action = 'add';
}

// Configurar la página
$PAGE->set_url('/mod/imagecarousel/edit.php', array('id' => $cm->id, 'imageid' => $imageid));
$PAGE->set_title($action === 'edit' ? get_string('edit_image', 'mod_imagecarousel') : get_string('add_image', 'mod_imagecarousel'));
$PAGE->set_heading(format_string($course->fullname));

// Al inicio del archivo después de los requires
$PAGE->requires->js_call_amd('core/form-colour', 'init', []);
$PAGE->requires->js_call_amd('theme_boost/bootstrap', 'init');

// Después de configurar la página
$PAGE->requires->js_amd_inline("
require(['jquery'], function($) {
    function updateColorPreview(inputId, previewClass) {
        var color = $(inputId).val();
        $(previewClass).css('background-color', color);
    }

    // Gestionar cambios de color
    $('#text_color').on('input change', function() {
        updateColorPreview('#text_color', '.color-preview');
    });

    // Gestionar cambios de color del fondo del texto
    $('#text_background').on('input change', function() {
        updateColorPreview('#text_background', '.background-preview');
    });

    // Inicializar previsualizadores
    updateColorPreview('#text_color', '.color-preview');
    
    // Solo mantenemos el código para background opacity
    function updateOpacityValue(opacityId, valueId) {
        const value = $(opacityId).val();
        $(valueId).text(value + '%');
    }
    
    $('#id_text_background_opacity').on('input', function() {
        updateOpacityValue('#id_text_background_opacity', '#text_background_opacity_value');
    });
    
    // Inicializar valores de opacidad para background
    updateOpacityValue('#id_text_background_opacity', '#text_background_opacity_value');
});
");


// Definir el formulario
class mod_imagecarousel_edit_form extends moodleform
{
    protected $context;

    /**
     * Constructor del formulario
     *
     * @param mixed $action URL del formulario
     * @param mixed $customdata Datos personalizados para el formulario
     * @param string $method Método del formulario (post o get)
     * @param string $target Target del formulario
     * @param mixed $attributes Atributos del formulario
     * @param bool $editable Si el formulario es editable
     */
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true)
    {
        if (isset($customdata['context'])) {
            $this->context = $customdata['context'];
        }
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }

    // Función para convertir un color hexadecimal a RGBA
    public function hexToRgba($hex, $opacity) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $a = max(0, min(1, $opacity / 100));

    return "rgba($r, $g, $b, $a)";
}

    public function definition()
    {
        global $CFG;

        $mform = $this->_form;

        // Ocultar campo para el ID del módulo
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        // Añadir campo oculto para el id del parámetro de la URL
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Añadir campo oculto para el id de la imagen
        $mform->addElement('hidden', 'imageid');
        $mform->setType('imageid', PARAM_INT);

        // Mostrar preview de las imágenes actuales
        if (!empty($this->_customdata['current_desktop_image']) || !empty($this->_customdata['current_mobile_image'])) {
            $mform->addElement('header', 'current_images', get_string('current_images', 'mod_imagecarousel'));
            $mform->setExpanded('current_images', true);

            $preview = '<div class="current-images-preview d-flex flex-wrap justify-content-start align-items-start gap-3">';

            // Preview imagen desktop
            if (!empty($this->_customdata['current_desktop_image'])) {
                $current_desktop = $this->_customdata['current_desktop_image'];
                $preview .= '<div class="preview-container" style="margin-right: 20px;">';
                $preview .= '<p><strong>' . get_string('desktop_image_label', 'mod_imagecarousel') . ':</strong> ' . $current_desktop['name'] . '</p>';
                if (filter_var($current_desktop['url'], FILTER_VALIDATE_URL)) {
                    $preview .= '<img src="' . $current_desktop['url'] . '" style="max-width: 200px; height: auto; border-radius: 8px;">';
                } else {
                    $preview .= '<img src="data:image/' . $current_desktop['type'] . ';base64,' . $current_desktop['url'] . '" style="max-width: 200px; height: auto; border-radius: 8px;">';
                }
                $preview .= '</div>';
            }

            // Preview imagen mobile
            if (!empty($this->_customdata['current_mobile_image'])) {
                $current_mobile = $this->_customdata['current_mobile_image'];
                $preview .= '<div class="preview-container">';
                $preview .= '<p><strong>' . get_string('mobile_image_label', 'mod_imagecarousel') . ':</strong> ' . $current_mobile['name'] . '</p>';
                if (filter_var($current_mobile['url'], FILTER_VALIDATE_URL)) {
                    $preview .= '<img src="' . $current_mobile['url'] . '" style="max-width: 100px; height: auto; border-radius: 8px;">';
                } else {
                    $preview .= '<img src="data:image/' . $current_mobile['type'] . ';base64,' . $current_mobile['url'] . '" style="max-width: 100px; height: auto; border-radius: 8px;">';
                }
                $preview .= '</div>';
            }

            $preview .= '</div>';
            $mform->addElement('html', $preview);

            // Sección para permitir reemplazar las imágenes actuales (subir nuevas)
            $mform->addElement('header', 'images_section', get_string('images_section', 'mod_imagecarousel'));
            $mform->setExpanded('images_section', true);

            // Opciones comunes para filemanager
            $options_files = array(
                'subdirs' => 0,
                'maxbytes' => 5242880, // 5MB
                'maxfiles' => 1,
                'accepted_types' => '*'
            );

            // Campo filemanager para imagen de escritorio (reemplazar)
            $mform->addElement('static', 'desktop_image_replace_label',
                '<strong>' . get_string('desktop_image_label', 'mod_imagecarousel') . '</strong>', '');
            $mform->addHelpButton('desktop_image_replace_label', 'desktop_image_info', 'mod_imagecarousel');
            $mform->addElement('filemanager', 'image_file_desktop', get_string('image_file_desktop', 'mod_imagecarousel'), null, $options_files);
            $mform->addElement('text', 'image_desktop', get_string('image_desktop', 'mod_imagecarousel'), array('size' => '60'));
            $mform->setType('image_desktop', PARAM_URL);

            // Campo filemanager para imagen móvil (reemplazar)
            $mform->addElement('static', 'mobile_image_replace_label',
                '<strong>' . get_string('mobile_image_label', 'mod_imagecarousel') . '</strong>', '');
            $mform->addHelpButton('mobile_image_replace_label', 'mobile_image_info', 'mod_imagecarousel');
            $mform->addElement('filemanager', 'image_file_mobile', get_string('image_file_mobile', 'mod_imagecarousel'), null, $options_files);
            $mform->addElement('text', 'image_mobile', get_string('image_mobile', 'mod_imagecarousel'), array('size' => '60'));
            $mform->setType('image_mobile', PARAM_URL);
        }

        // Campo para el texto
        $mform->addElement('textarea', 'text', get_string('text', 'mod_imagecarousel'), array('rows' => 3, 'cols' => 60));
        $mform->setType('text', PARAM_CLEANHTML);
        $mform->addHelpButton('text', 'text', 'mod_imagecarousel');

        // Campo para la URL del texto
        $mform->addElement('text', 'text_url', get_string('text_url', 'mod_imagecarousel'), array('size' => '60'));
        $mform->setType('text_url', PARAM_URL);
        $mform->addHelpButton('text_url', 'text_url', 'mod_imagecarousel');

        // Sección de personalización del texto
        $mform->addElement(
            'header',
            'text_customization',
            get_string('text_customization', 'mod_imagecarousel')
        );
        $mform->setExpanded('text_customization', true);

        // Información sobre unidades de medida
        $mform->addElement(
            'static',
            'size_units_info',
            get_string('size_units_info', 'mod_imagecarousel'),
            get_string('size_units_help', 'mod_imagecarousel')
        );

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

        // Tamaño del texto
        $mform->addElement('text', 'text_size', get_string('text_size', 'mod_imagecarousel'));
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
        $mform->addElement(
            'select',
            'text_position',
            get_string('text_position', 'mod_imagecarousel'),
            $positions
        );
        $mform->setDefault('text_position', 'center');
        $mform->addHelpButton('text_position', 'text_position', 'mod_imagecarousel');

        // Ajuste fino de posición
        $mform->addElement(
            'header',
            'position_adjustment',
            get_string('position_adjustment', 'mod_imagecarousel')
        );
        $mform->setExpanded('position_adjustment', true);

        $customposition = array(
             $mform->createElement(
                 'text',
                 'text_position_adjust[top]',
                 get_string('position_adjust_top', 'mod_imagecarousel'),
                 array('placeholder' => get_string('position_adjust_top_placeholder', 'mod_imagecarousel'), 'size' => '8')
             ),
            $mform->createElement(
                'text',
                'text_position_adjust[right]',
                get_string('position_adjust_right', 'mod_imagecarousel'),
                array('placeholder' => get_string('position_adjust_right_placeholder', 'mod_imagecarousel'), 'size' => '8')
            )
            // $mform->createElement(
            //      'text',
            //      'text_position_adjust[bottom]',
            //      get_string('position_adjust_bottom', 'mod_imagecarousel'),
            //      array('placeholder' => get_string('position_adjust_bottom_placeholder', 'mod_imagecarousel'), 'size' => '8')
            //  )
            // $mform->createElement(
            //     'text',
            //     'text_position_adjust[left]',
            //     get_string('position_adjust_left', 'mod_imagecarousel'),
            //     array('placeholder' => get_string('position_adjust_left_placeholder', 'mod_imagecarousel'), 'size' => '8')
            // )
        );
        $mform->addGroup(
            $customposition,
            'text_position_adjust_group',
            get_string('position_adjustment_desc', 'mod_imagecarousel'),
            ' ',
            false
        );
        $mform->addHelpButton('text_position_adjust_group', 'position_adjustment', 'mod_imagecarousel');

        // Establecer el tipo de datos para cada campo de ajuste de posición
        $mform->setType('text_position_adjust[top]', PARAM_TEXT);
        $mform->setType('text_position_adjust[right]', PARAM_TEXT);
        // $mform->setType('text_position_adjust[bottom]', PARAM_TEXT);
        // $mform->setType('text_position_adjust[left]', PARAM_TEXT);

        // Estilos del texto
        $textstyles = array(
            $mform->createElement(
                'checkbox',
                'text_style[bold]',
                '',
                get_string('text_bold', 'mod_imagecarousel'),
                array('class' => 'mr-1')
            ),
            $mform->createElement(
                'checkbox',
                'text_style[italic]',
                '',
                get_string('text_italic', 'mod_imagecarousel'),
                array('class' => 'mx-1')
            ),
            $mform->createElement(
                'checkbox',
                'text_style[underline]',
                '',
                get_string('text_underline', 'mod_imagecarousel'),
                array('class' => 'ml-1')
            )
        );
        $mform->addGroup(
            $textstyles,
            'text_style_group',
            get_string('text_style', 'mod_imagecarousel'),
            '&nbsp;&nbsp;&nbsp;',
            false
        );
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

        // Relleno del texto
        $mform->addElement(
            'text',
            'text_padding',
            get_string('text_padding', 'mod_imagecarousel')
        );
        $mform->setType('text_padding', PARAM_TEXT);
        $mform->addHelpButton('text_padding', 'text_padding', 'mod_imagecarousel');
        $mform->setDefault('text_padding', get_string('default_text_padding', 'mod_imagecarousel'));

        // Radio del borde
        $mform->addElement(
            'text',
            'text_border_radius',
            get_string('text_border_radius', 'mod_imagecarousel')
        );
        $mform->setType('text_border_radius', PARAM_TEXT);
        $mform->addHelpButton('text_border_radius', 'text_border_radius', 'mod_imagecarousel');
        $mform->setDefault('text_border_radius', get_string('default_text_border_radius', 'mod_imagecarousel'));

        // Botones del formulario
        $this->add_action_buttons();
    }

    /**
     * Función de validación del formulario
     * Verifica que los datos ingresados sean correctos antes de procesarlos
     *
     * @param array $data Datos del formulario
     * @param array $files Archivos subidos
     * @return array Errores encontrados
     */
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        
        // Validar el límite de 300 palabras en el campo de texto
        if (!empty($data['text'])) {
            $wordCount = str_word_count(strip_tags($data['text']));
            if ($wordCount > 300) {
                $errors['text'] = get_string('error_text_word_limit', 'mod_imagecarousel');
            }
        }
        
        return $errors;
    }
}

// URL para regresar a la página de gestión
$returnurl = new moodle_url('/mod/imagecarousel/manage.php', array('id' => $cm->id));

// Verificar si se está editando una imagen existente
if ($imageid > 0) {
    // Usar directSQL para obtener la imagen y asegurarnos que existe
    $image = $DB->get_record('imagecarousel_images', array('id' => $imageid, 'carouselid' => $moduleinstance->id));

    if (!$image) {
        redirect($returnurl, get_string('image_not_found', 'mod_imagecarousel'), null, \core\output\notification::NOTIFY_ERROR);
    }

    // Preparar datos de la imagen actual para el formulario
    $customdata = array('context' => $context);

    // Preparar imagen de escritorio si existe
    if (!empty($image->desktop_image)) {
        $customdata['current_desktop_image'] = array(
            'name' => $image->desktop_image_name,
            'url' => $image->desktop_image,
            'type' => strtolower(pathinfo($image->desktop_image_name, PATHINFO_EXTENSION))
        );
    }

    // Preparar imagen móvil si existe
    if (!empty($image->mobile_image)) {
        $customdata['current_mobile_image'] = array(
            'name' => $image->mobile_image_name,
            'url' => $image->mobile_image,
            'type' => strtolower(pathinfo($image->mobile_image_name, PATHINFO_EXTENSION))
        );
    }

    // Añadir el color actual para el selector de color
    $customdata['current_color'] = $image->text_color ?? '#000000';
    // Añadir el color de fondo actual para el selector de color
    $customdata['current_text_background'] = $image->text_background ?? '#ffffff';


    // Crear instancia del formulario con los datos personalizados
    $mform = new mod_imagecarousel_edit_form(null, $customdata);
    // Preparar datos iniciales del formulario
    $formdata = (array) $image;
    $formdata['id'] = $id;
    $formdata['cmid'] = $cm->id;
    $formdata['imageid'] = $imageid;
    $formdata['action'] = 'edit';

    // Preparar campos de texto y personalización
    $formdata['text'] = $image->text ?? '';
    $formdata['text_url'] = $image->text_url ?? '';
    $formdata['text_color'] = $image->text_color ?? get_string('text_color', 'mod_imagecarousel');
    $formdata['text_size'] = $image->text_size ?? get_string('default_text_size', 'mod_imagecarousel');
    $formdata['text_position'] = $image->text_position ?? 'center';
    $formdata['text_background'] = $image->text_background ?? get_string('text_background', 'mod_imagecarousel');
    $formdata['text_background_opacity'] = $image->text_background_opacity ?? 100;
    $formdata['text_padding'] = $image->text_padding ?? get_string('default_text_padding', 'mod_imagecarousel');
    $formdata['text_border_radius'] = $image->text_border_radius ?? get_string('default_text_border_radius', 'mod_imagecarousel');

    // Preparar ajustes de posición
    $formdata['text_position_adjust'] = array(
        'top' => $image->text_position_top ?? '',
        'right' => $image->text_position_right ?? ''
        //'bottom' => $image->text_position_bottom ?? ''
        // 'left' => $image->text_position_left ?? ''
    );

    // Preparar estilos de texto
    $formdata['text_style'] = array(
        'bold' => $image->text_style_bold ?? 0,
        'italic' => $image->text_style_italic ?? 0,
        'underline' => $image->text_style_underline ?? 0
    );

    // Mantener la URL principal (verificar si tiene un nuevo valor)
    if (isset($formdata['url']) && !empty($formdata['url'])) {
        $url = $formdata['url'];
        // Verificar si la URL ya tiene un protocolo
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            // No tiene protocolo, verificar si parece una URL externa
            if (preg_match('/^www\.|^\w+\.\w+/i', $url)) {
                // Es probablemente una URL externa, agregar https://
                $url = 'https://' . $url;
            }
        }
        $formdata['url'] = $url;
    } else {
        $formdata['url'] = $image->url; // Mantener la URL existente
    }

    // Establecer los datos en el formulario
    $mform->set_data($formdata);
} else {
    // Crear instancia del formulario para nueva imagen
    $mform = new mod_imagecarousel_edit_form(null, array('context' => $context));

    // Configurar datos iniciales del formulario
    $mform->set_data(array(
        'cmid' => $cm->id,
        'id' => $id
    ));
}

// Procesamiento del formulario
if ($mform->is_cancelled()) {
    // Si se cancela, volver a la página de gestión
    redirect($returnurl);
} else if ($formdata = $mform->get_data()) {
    try {
        if ($action === 'edit') {
            // Verificar que tenemos un ID válido
            if (empty($imageid) || $imageid <= 0) {
                throw new moodle_exception('invalid_image_id', 'mod_imagecarousel');
            }

            // Obtener la imagen existente para mantener los datos actuales
            $existingimage = $DB->get_record('imagecarousel_images', array('id' => $imageid));
            if (!$existingimage) {
                throw new moodle_exception('image_not_found', 'mod_imagecarousel');
            }

            // Crear el registro para actualizar
            $imagerecord = new stdClass();
            $imagerecord->id = $imageid; // ID de la imagen a actualizar

            // Mantener los datos de las imágenes y el orden
            $imagerecord->desktop_image = $existingimage->desktop_image;
            $imagerecord->desktop_image_name = $existingimage->desktop_image_name;
            $imagerecord->mobile_image = $existingimage->mobile_image;
            $imagerecord->mobile_image_name = $existingimage->mobile_image_name;
            $imagerecord->sortorder = $existingimage->sortorder;
            $imagerecord->carouselid = $existingimage->carouselid;

            // Procesar posibles imágenes subidas para reemplazo (convierte a base64 como en adding_image)
            // Esta función está en lib.php y rellenará campos como desktop_image/mobile_image y *_name
            if (function_exists('mod_imagecarousel_process_uploaded_images')) {
                // $formdata es el objeto con los datos del formulario
                mod_imagecarousel_process_uploaded_images($formdata);

                // Si se subió una nueva imagen de escritorio (convertida a base64 en $formdata->desktop_image), reemplazarla
                if (isset($formdata->desktop_image) && !empty($formdata->desktop_image)) {
                    $imagerecord->desktop_image = $formdata->desktop_image;
                    $imagerecord->desktop_image_name = $formdata->desktop_image_name ?? $existingimage->desktop_image_name;
                } else if (!empty($formdata->image_desktop)) {
                    // Si se proporcionó una URL en el campo de URL, usarla
                    $imagerecord->desktop_image = $formdata->image_desktop;
                    $imagerecord->desktop_image_name = basename($formdata->image_desktop);
                }

                // Si se subió una nueva imagen móvil (convertida a base64 en $formdata->mobile_image), reemplazarla
                if (isset($formdata->mobile_image) && !empty($formdata->mobile_image)) {
                    $imagerecord->mobile_image = $formdata->mobile_image;
                    $imagerecord->mobile_image_name = $formdata->mobile_image_name ?? $existingimage->mobile_image_name;
                } else if (!empty($formdata->image_mobile)) {
                    // Si se proporcionó una URL en el campo de URL, usarla
                    $imagerecord->mobile_image = $formdata->image_mobile;
                    $imagerecord->mobile_image_name = basename($formdata->image_mobile);
                }
            }

            // Mantener la URL principal o actualizarla (verificar si tiene un nuevo valor)
            if (isset($formdata->url) && !empty($formdata->url)) {
                $url = $formdata->url;
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
                $imagerecord->url = $existingimage->url; // Mantener la URL existente
            }

            // Actualizar los campos de texto y personalización
            $imagerecord->text = $formdata->text;

            // Modificar la URL del texto para asegurar que URLs externas tengan el protocolo correcto
            if (isset($formdata->text_url) && !empty($formdata->text_url)) {
                $url = $formdata->text_url;
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

            $imagerecord->text_color = isset($formdata->text_color) ? $formdata->text_color : '#000000';
            $imagerecord->text_size = $formdata->text_size;
            $imagerecord->text_position = $formdata->text_position;

            // Procesar ajustes de posición
            if (isset($formdata->text_position_adjust)) {
                $imagerecord->text_position_top = $formdata->text_position_adjust['top'];
                $imagerecord->text_position_right = $formdata->text_position_adjust['right'];
                //$imagerecord->text_position_bottom = $formdata->text_position_adjust['bottom'];
                // $imagerecord->text_position_left = $formdata->text_position_adjust['left'];
            }

            // Procesar estilos de texto
            $imagerecord->text_style_bold = !empty($formdata->text_style['bold']);
            $imagerecord->text_style_italic = !empty($formdata->text_style['italic']);
            $imagerecord->text_style_underline = !empty($formdata->text_style['underline']);

            // Procesar fondo y bordes
            $imagerecord->text_background = isset($formdata->text_background) ? $formdata->text_background : '#ffffff';
            $imagerecord->text_background_opacity = $formdata->text_background_opacity ?? 100;
            $imagerecord->text_padding = $formdata->text_padding;
            $imagerecord->text_border_radius = $formdata->text_border_radius;

            // Actualizar el registro en la base de datos
            if (!$DB->update_record('imagecarousel_images', $imagerecord)) {
                throw new moodle_exception('error_updating_image', 'mod_imagecarousel');
            }

            // Actualizar tiempo de modificación del módulo
            $DB->set_field('imagecarousel', 'timemodified', time(), array('id' => $cm->instance));

            // Redirigir a la página de gestión con mensaje de éxito
            redirect($returnurl, get_string('image_updated', 'mod_imagecarousel'), null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            // Si es agregar, redireccionar a la página de agregar
            redirect(new moodle_url('/mod/imagecarousel/adding_image.php', array('id' => $cm->id)));
        }
    } catch (Exception $e) {
        \core\notification::error($e->getMessage());
    }
}

// Mostrar el formulario
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
