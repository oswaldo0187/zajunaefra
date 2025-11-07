<?php
/**
 * Página para la gestión de imágenes del slider
 *
 * @package     local_slider_form
 */
    require_once(__DIR__ . '/../../config.php');
    require_once(__DIR__ . '/classes/forms/Update.php');
    require_once(__DIR__ . '/classes/modal/Modal.php');
    require_once(__DIR__ . '/classes/table/Table_manage.php');
    require_once(__DIR__ . '/lib/usersValidations.php');
    require_once(__DIR__ . '/lib/utilsFunctions.php');

    $context = context_system::instance();
    $PAGE->set_context($context);

    // Establecer el layout para mostrar bloques en el lado izquierdo
    $PAGE->set_pagelayout('admin');

    // Establecer la URL de la página
    $PAGE->set_url(new moodle_url('/local/slider_form/manage_images.php'));

    // Asegurar que el usuario ha iniciado sesión
    require_login();

    // Se cargan los estilos y scripts necesarios.
    $PAGE->requires->css(new moodle_url('./css/formUpdate.css'));
    $PAGE->requires->js(new moodle_url('./js/script.js'));

    // Comprobar la sesión y el rol del usuario
    checkSession('redirect', ['/login/index.php']);
    checkUserRole('redirect', ['/login/index.php']);

    // Título de la página
    $PAGE->set_title('Gestionar Imágenes');
    $PAGE->set_heading('Gestionar Imágenes del Banner de plataforma');

    // Variables de configuración
    $stateOption = optional_param('state_option', '0', PARAM_INT);
    $tableColumns = ['Id', 'Imagen', 'Nombre', 'Visualización','Accion'];
    $perPage = 5; // Paginación de 5 registros
    $page = optional_param('page', 0, PARAM_INT);

    // Verificar si venimos de alguna operación exitosa
    $success = optional_param('success', 0, PARAM_INT);
    $updated = optional_param('updated', 0, PARAM_INT);
    $deleted = optional_param('deleted', 0, PARAM_INT);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_completed']) && $_POST['action_completed'] == '1') {
        $success = 1;
    }

    // Parámetros para los modales
    $updateModalParams = [
        'editModal',
        'Editar Imagen Banner de plataforma',
        [
            ['id' => 'id="closeEditModal"', 'label' => 'Cerrar', 'class' => 'btn-secondary', 'attributes' => 'data-dismiss="modal"'],
            ['label' => 'Guardar cambios', 'class' => 'btn-primary', 'attributes' => 'onclick="updateRecord()"']
        ]
    ];
    
    $alertModalParams = [
        'alertModal',
        'Confirmar',
        [
            ['id' => 'id="closeAlertModal"', 'label' => 'Cerrar', 'class' => 'btn-secondary', 'attributes' => 'data-dismiss="modal"'],
            ['label' => 'Eliminar', 'class' => 'btn-danger', 'attributes' => 'onclick="deleteRecord()"']
        ]
    ];

    // Mostrar encabezado
    echo $OUTPUT->header();

    // Menú de navegación
    echo '
    <div class="nav nav-tabs mb-3" role="tablist">
        <a class="nav-link" href="index.php">Agregar imagen</a>
        <a class="nav-link active" href="manage_images.php">Gestionar imágenes</a>
        <a class="nav-link" href="show_order.php">Orden de Despliegue</a>
    </div>
    ';

    // Mostrar mensaje de éxito si viene de la página de inserción
    if ($success == 1) {
        echo '
        <div class="alert alert-success alert-block fade in alert-dismissible">
            <span id="alert-message">Éxito<br><br>La imagen ha sido agregada correctamente al slider.</span>
            <button type="button" class="close" onclick="closeAlert(this)">
                <span aria-hidden="true">×</span>
                <span class="sr-only">Descartar esta notificación</span>
            </button>
        </div>
        ';
    } elseif ($updated == 1) {
        echo '
        <div class="alert alert-success alert-block fade in alert-dismissible">
            <span id="alert-message">Éxito<br><br>La imagen ha sido actualizada correctamente. En caso de que se haya cambiado el estado, favor dirigirse a la sección "Mis cursos" para visualizar los cambios.</span>
            <button type="button" class="close" onclick="closeAlert(this)">
                <span aria-hidden="true">×</span>
                <span class="sr-only">Descartar esta notificación</span>
            </button>
        </div>
        ';
    } elseif ($deleted == 1) {
        echo '
        <div class="alert alert-success alert-block fade in alert-dismissible">
            <span id="alert-message">Éxito<br><br>La imagen ha sido eliminada de manera correcta.</span>
            <button type="button" class="close" onclick="closeAlert(this)">
                <span aria-hidden="true">×</span>
                <span class="sr-only">Descartar esta notificación</span>
            </button>
        </div>
        ';
    }

    // Alerta para mostrar mensajes
    echo '
    <div class="" role="alert" id="table-alert" style="display: none;">
        <span id="alert-message"></span>
        <button type="button" class="close" onclick="closeAlert(this)">
            <span aria-hidden="true">×</span>
            <span class="sr-only">Descartar esta notificación</span>
        </button>
    </div>
    ';

    // Contenedor principal
    echo '<div class="image-content-manager">';

    // Filtro de estado
    echo '
    <form method="get" action="manage_images.php">
        <select name="state_option" id="state_option" onchange="this.form.submit()">
            <option value="0"' . ($stateOption == '0' ? ' selected' : '') . '>Todas</option>
            <option value="1"' . ($stateOption == '1' ? ' selected' : '') . '>Activas</option>
        </select>
    </form>
    ';
    // Filtro de estado y botón 'Modificar orden' en la misma fila
    // Obtener registros para la tabla
    $quantityOfRecords = getQuantityOfRecords();

    if ($quantityOfRecords > 0) {
        // Configurar consulta
        $queryParams = [
            'conditions' => (($stateOption == 1) ? ['state' => '1'] : []),
            'order' => '',
            'columns' => '*'
        ];

        // Obtener registros
        $records = getRecords($queryParams);

        // Ordenar registros
        if ($quantityOfRecords > 1) {
            $records = sortedByAttribute($records, 'id');
            $records = array_reverse($records);
        }

        // Paginación
        $recordsToShow = array_slice($records, $page * $perPage, $perPage);
        $startIndex = $page * $perPage + 1;
        echo renderTable($tableColumns, $recordsToShow, null, $startIndex);

        // Mostrar paginación
        $totalPages = ceil($quantityOfRecords / $perPage);
        if ($totalPages > 1) {
            echo '<nav><ul class="pagination justify-content-center">';
            for ($i = 0; $i < $totalPages; $i++) {
                $active = ($i == $page) ? ' active' : '';
                echo '<li class="page-item'.$active.'"><a class="page-link" href="?page='.$i.'&state_option='.$stateOption.'">'.($i+1).'</a></li>';
            }
            echo '</ul></nav>';
        }


    } else {
        // No hay registros
        echo renderTable($tableColumns, []);
    }

    // Cerrar contenedor principal
    echo '</div>';

    // Mostrar modales
    echo renderModal($updateModalParams, renderForm('UpdateForm'));
    echo renderModal($alertModalParams, '<p>¿Estás seguro de que deseas eliminar este registro?</p>');

    // Mostrar pie de página
    echo $OUTPUT->footer();

    /**
     * Renderiza un formulario
     * @param string $formClassName Nombre de la clase del formulario a renderizar
     * @return string HTML del formulario renderizado
     */
    function renderForm($formClassName) {
        $formObject = new $formClassName();
        
        ob_start();
        $formObject->display();
        return ob_get_clean();
    }

    /**
     * Renderiza un modal
     * @param array $constructurParams Parámetros para el constructor del modal
     * @param string $contentToShowOnModal Contenido HTML que se mostrará en el modal
     * @return string HTML del modal renderizado
     */
    function renderModal($constructurParams, $contentToShowOnModal) {
        $modal = new Modal(...$constructurParams);
        return $modal->render($contentToShowOnModal);
    }

    /**
     * Renderiza una tabla
     * @param array $columns Columnas de la tabla
     * @param array $records Registros a mostrar en la tabla
     * @param array|null $paginator Configuración del paginador (ya no se usa)
     * @param int $startIndex Índice inicial para la paginación
     * @return string HTML de la tabla renderizada
     */
    function renderTable($columns, $records, $paginator = null, $startIndex = 1) {
        $table = new TableManager($columns);
        return $table->renderTable($records, $startIndex, false);
    }

    /**
     * Obtiene la cantidad de registros en la tabla del slider
     * @param string $tableName Nombre de la tabla (por defecto 'local_slider')
     * @return int Cantidad de registros en la tabla
     */
    function getQuantityOfRecords($tableName = 'local_slider') {
        global $DB;
        return $DB->count_records($tableName);
    }

    /**
     * Obtiene los registros de la tabla del slider según los parámetros de consulta
     * @param array $remainingParams Parámetros para la consulta
     * @param string $tableName Nombre de la tabla (por defecto 'local_slider')
     * @return array Registros obtenidos de la base de datos
     */


    // Función general
function getRecords($remainingParams, $tableName = 'local_slider') {
    global $DB;
    
    $conditions = [];
    $params = [];
    
    if (!empty($remainingParams['conditions'])) {
        foreach ($remainingParams['conditions'] as $field => $value) {
            if ($field === 'state') {
                $conditions[] = $DB->sql_compare_text($field) . " = " . $DB->sql_compare_text('?');
            } else {
                $conditions[] = "$field = ?";
            }
            $params[] = $value;
        }
    }
    
    $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
    $sql = "SELECT * FROM {{$tableName}} $whereClause";
    
    return $DB->get_records_sql($sql, $params);
}
