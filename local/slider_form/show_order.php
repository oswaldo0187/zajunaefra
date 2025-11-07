<?php
/**
 * Página para administrar el orden de despliegue de las imágenes del slider
 *
 * @package     local_slider_form
 */
    require_once(__DIR__ . '/../../config.php');
    require_once(__DIR__ . '/classes/table/Table_order.php');
    require_once(__DIR__ . '/lib/usersValidations.php');
    require_once(__DIR__ . '/lib/utilsFunctions.php');

    $context = context_system::instance();
    $PAGE->set_context($context);

    // Establecer el layout para mostrar bloques en el lado izquierdo
    $PAGE->set_pagelayout('admin');

    // Establecer la URL de la página
    $PAGE->set_url(new moodle_url('/local/slider_form/show_order.php'));

    // Asegurar que el usuario ha iniciado sesión
    require_login();

    // Se cargan los estilos y scripts necesarios.
    $PAGE->requires->css(new moodle_url('./css/formUpdate.css'));
    $PAGE->requires->js(new moodle_url('./js/script.js'));

    // Comprobar la sesión y el rol del usuario
    checkSession('redirect', ['/login/index.php']);
    checkUserRole('redirect', ['/login/index.php']);

    // Título de la página
    $PAGE->set_title('Orden de Despliegue');
    $PAGE->set_heading('Orden de Despliegue de Imágenes - Banner de plataforma');

    // Verificar si venimos de una operación exitosa
    $ordered = optional_param('ordered', 0, PARAM_INT);
    
    // Variables de configuración
    $stateOption = optional_param('state_option', '0', PARAM_INT);
    $perPage = 5; // Paginación de 5 registros
    $page = optional_param('page', 0, PARAM_INT);

    // Detectar modo de orden
    $mode = optional_param('mode', '', PARAM_ALPHA);

    // Mostrar encabezado
    echo $OUTPUT->header();

    // Menú de navegación
    echo '
    <div class="nav nav-tabs mb-3" role="tablist">
        <a class="nav-link" href="index.php">Agregar imagen</a>
        <a class="nav-link" href="manage_images.php">Gestionar imágenes</a>
        <a class="nav-link active" href="show_order.php">Orden de Despliegue</a>
    </div>
    ';

    // Mostrar mensaje de éxito si viene de ordenar imágenes
    if ($ordered == 1) {
        echo '
        <div class="alert alert-success alert-block fade in alert-dismissible">
            <span id="alert-message">Éxito<br><br>El orden de las imágenes ha sido actualizado correctamente.</span>
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

    // Agregar estilos personalizados para el botón 'Modificar orden'
    echo '<style>
        .btn-warning.modificar-orden {
            color: #fff;
            background-color: #39a900;
            border-color: #39a900;
            line-height: 1.5;
            border-radius: .25rem;
            transition: background 0.2s, border 0.2s;
        }
        .btn-warning.modificar-orden:hover {
            color: #fff;
            background-color: #2c8300;
            border-color: #287600;
        }
    </style>';

    // Contenedor principal
    echo '<div class="image-content-manager">';

    // Filtro de estado
    echo '
    <form method="get" action="show_order.php">
        <select name="state_option" id="state_option" onchange="this.form.submit()">
            <option value="0"' . ($stateOption == '0' ? ' selected' : '') . '>Todas</option>
            <option value="1"' . ($stateOption == '1' ? ' selected' : '') . '>Activas</option>
        </select>
    </form>
    ';

    // Columnas de la tabla
    $tableColumns = ['Orden', 'Imagen', 'Nombre', 'Visualización','Acción'];

    // Obtener registros con o sin filtro según la opción seleccionada
    $queryParams = [
        'conditions' => (($stateOption == 1) ? ['state' => '1'] : [])
    ];

    // Obtener registros
    $records = getRecords($queryParams);
    $quantityOfRecords = count($records);

    // Paginación
    $recordsToShow = array_slice($records, $page * $perPage, $perPage);

    // Verificar si hay registros para mostrar
    if (empty($records)) {
        echo '<div class="alert alert-info">No hay imágenes disponibles para ordenar.</div>';
    }
    
    // Agregar botón de guardar si hay más de un registro
    $actionButton = '';
    if ($quantityOfRecords > 1) {
        // Ordenar registros por orden de despliegue
        foreach ($records as $key => $record) {
            if (empty($record->order_display)) {
                $records[$key]->order_display = 9999;
            }
        }
        $records = sortedByAttribute($records, 'order_display');
        // $records = array_reverse($records); // Si se requiere orden inverso
        $recordsToShow = array_slice($records, $page * $perPage, $perPage);
        $actionButton = '<div class="d-flex justify-content-center mt-3"><a href="#" class="btn btn-primary" onclick="saveOrder()">Guardar</a></div>';
    }

    // Botón 'Modificar orden' (solo en modo paginado)
    $modificarOrdenBtn = '';
    if ($mode !== 'order' && $quantityOfRecords > 1) {
        $modificarOrdenBtn = '<div class="d-flex justify-content-start mt-3">'
            .'<a href="show_order.php?mode=order&state_option='.$stateOption.'" class="btn btn-warning modificar-orden">Modificar orden</a>'
            .'</div>';
    }

    // Mostrar tabla con paginación o todos los registros según el modo
    if ($quantityOfRecords > 0) {
        if ($mode === 'order' && $quantityOfRecords > 1) {
            // Vista de orden: mostrar todos los registros sin paginación
            $startIndex = 1;
            echo renderTable($tableColumns, $records, true, $startIndex);
            // Botón Guardar
            echo '<div class="d-flex justify-content-center mt-3">'
                .'<a href="#" class="btn btn-primary" onclick="saveOrder()">Guardar</a>'
                .'</div>';
        } else {
            // Vista paginada
            $startIndex = $page * $perPage + 1;
            echo renderTable($tableColumns, $recordsToShow, false, $startIndex);
            echo $modificarOrdenBtn;
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
        }
    }
    
    // Cerrar el contenedor principal
    echo '</div>';

    // Mostrar pie de página
    echo $OUTPUT->footer();

    /**
     * Renderiza una tabla
     * @param array $columns Columnas de la tabla
     * @param array $records Registros a mostrar en la tabla
     * @param bool $dragAndDropButtons Si se deben mostrar botones para ordenar
     * @param int $startIndex Índice inicial para la numeración de la columna ID
     * @return string HTML de la tabla renderizada
     */
    function renderTable($columns, $records, $dragAndDropButtons = false, $startIndex = 1) {
        $table = new TableManager($columns);
        return $table->renderTable($records, $startIndex, $dragAndDropButtons);
    }

    /**
     * Obtiene los registros de la tabla del slider según los parámetros de consulta
     * @param array $remainingParams Parámetros para la consulta
     * @param string $tableName Nombre de la tabla (por defecto 'local_slider')
     * @return array Registros obtenidos de la base de datos
     */
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