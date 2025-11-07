<?php
// local/slider_form/settings.php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // Añadir directamente al menú principal de administración en lugar de crear una categoría separada
    $ADMIN->add('root', new admin_externalpage(
        'local_slider_form_manage',
        get_string('manage_slider', 'local_slider_form'),
        new moodle_url('/local/slider_form/index.php')
    ));
}