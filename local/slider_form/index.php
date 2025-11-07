<?php
/**
 * Página para la carga de imágenes del slider
 *
 * @package     local_slider_form
 */
    require_once(__DIR__ . '/../../config.php');
    require_once(__DIR__ . '/classes/forms/Insert.php');
    require_once(__DIR__ . '/lib/usersValidations.php');
    require_once(__DIR__ . '/lib/utilsFunctions.php');

    $context = context_system::instance();
    $PAGE->set_context($context);

    // Establecer el layout para mostrar bloques en el lado izquierdo
    $PAGE->set_pagelayout('admin');

    // Establecer la URL de la página
    $PAGE->set_url(new moodle_url('/local/slider_form/index.php'));

    // Asegurar que el usuario ha iniciado sesión
    require_login();

    // Agregar el ítem al menú de navegación
    // $settingsnode = $PAGE->settingsnav->add('Banner de plataforma');
    // $settingsnode->add('Agregar imagen', new moodle_url('/local/slider_form/index.php'));
    // $settingsnode->add('Gestionar imágenes', new moodle_url('/local/slider_form/manage_images.php'));
    // $settingsnode->add('Orden de Despliegue', new moodle_url('/local/slider_form/show_order.php'));

    // Se cargan los estilos y scripts necesarios.
    $PAGE->requires->css(new moodle_url('./css/formUpdate.css'));
    $PAGE->requires->js(new moodle_url('./js/script.js'));

    // Comprobar la sesión y el rol del usuario
    checkSession('redirect', ['/login/index.php']);
    checkUserRole('redirect', ['/login/index.php']);

    // Título de la página
    $PAGE->set_title('Cargar Imagen');
    $PAGE->set_heading('Cargar Imagen para Banner de plataforma');

    // Mostrar encabezado
    echo $OUTPUT->header();

    // Menú de navegación
    echo '
    <div class="nav nav-tabs mb-3" role="tablist">
        <a class="nav-link active" href="index.php">Agregar imagen</a>
        <a class="nav-link" href="manage_images.php">Gestionar imágenes</a>
        <a class="nav-link" href="show_order.php">Orden de Despliegue</a>
    </div>
    ';

    // Mostrar mensaje si viene de otra página
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo '
        <div class="alert alert-success alert-block fade in alert-dismissible">
            <span id="alert-message">Éxito<br><br>El registro se creó de manera correcta.</span>
            <button type="button" class="close" onclick="closeAlert(this)">
                <span aria-hidden="true">×</span>
                <span class="sr-only">Descartar esta notificación</span>
            </button>
        </div>
        ';
    }

    // Mostrar el formulario de inserción
    echo renderForm('InsertForm', []);

    // Mostrar pie de página
    echo $OUTPUT->footer();

    /**
     * Renderiza un formulario
     * @param string $formClassName Nombre de la clase del formulario a renderizar
     * @param array $constructurParams Parámetros para el constructor del formulario
     * @return string HTML del formulario renderizado
     */
    function renderForm($formClassName, $constructurParams) {
        global $CFG;
        
        $formObject = new $formClassName();
        
        ob_start();
        $formObject->display();
        return ob_get_clean();
    }