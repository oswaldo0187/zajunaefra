<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_imagecarousel_mod_form extends moodleform_mod {        

    public function definition() {
        // Utiliza el formulario de moodleform_mod
        $mform = $this->_form;
        // Sección General
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Nombre de la actividad
        $mform->addElement('text', 'name', get_string('name') . ' <span class="text-danger">*</span>', array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Descripción
        $this->standard_intro_elements();

    // Sección Disponibilidad (programación)
    $mform->addElement('header', 'availability', get_string('availability', 'mod_imagecarousel'));
    $mform->addElement('date_time_selector', 'availablefrom', get_string('availablefrom', 'mod_imagecarousel'), array('optional' => true));
    $mform->addHelpButton('availablefrom', 'availablefrom', 'mod_imagecarousel');
    $mform->addElement('date_time_selector', 'availableuntil', get_string('availableuntil', 'mod_imagecarousel'), array('optional' => true));
    $mform->addHelpButton('availableuntil', 'availableuntil', 'mod_imagecarousel');
    $mform->setType('availablefrom', PARAM_INT);
    $mform->setType('availableuntil', PARAM_INT);

        // Elementos estándar de los módulos
        $this->standard_coursemodule_elements();

        // Botones del formulario
        $this->add_action_buttons();
    }

    /**
     * Validación del formulario en el servidor.
     * Evita que la fecha de caducidad sea anterior a la fecha de disponibilidad.
     *
     * @param array $data Datos enviados por el formulario
     * @param array $files Archivos enviados
     * @return array Errores por campo
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Ambos campos son opcionales; solo validar si se proporcionan ambos
        if (!empty($data['availablefrom']) && !empty($data['availableuntil'])) {
            if ((int)$data['availableuntil'] < (int)$data['availablefrom']) {
                // Añadir mensaje de error al campo availableuntil 
                $errors['availableuntil'] = get_string('availableuntil_error', 'mod_imagecarousel');
            }
        }

        return $errors;
    }
}
