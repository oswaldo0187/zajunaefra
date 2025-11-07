<?php 
define('AJAX_SCRIPT', true);
/**
 * Controlador para insertar un registro en la tabla del slider
 * 
 * Este archivo se encarga de:
 * - Validar los datos del formulario
 * - Eliminar valores no permitidos
 * - Generar nombres de imágenes
 * - Verificar si existen imágenes duplicadas
 * - Insertar el registro en la base de datos
 * - Actualizar el caché si es necesario
 * 
 * @package     local_slider_form
 */
header('Content-Type: application/json');

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/lib/fieldsValidations.php');
require_once(__DIR__ . '/lib/utilsFunctions.php');
require_once(__DIR__ . '/lib/usersValidations.php');
require_once($CFG->dirroot . '/cache/lib.php');

    // Configuración del controlador.
    $controllerSettings = [

        // Valores no permitidos de la petición.
        'requestValuesToDelete' => ['sesskey', 'desktop_image', 'mobile_image', 'sendnotification', 'notifyroles' ],

        // Imágenes requeridas.
        'requieredImages' => ['desktop_image', 'mobile_image']
        
    ];

// Si el método de la petición es POST, se ejecuta el siguiente código.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {   

        // Variables globales.
        global $DB, $USER, $SESSION, $PAGE;
        $PAGE->set_context(context_system::instance());
        $PAGE->set_url('/local/slider_form/insertRecord.php');

        // Configuración del plugin.
        [ 'table_name' => $pluginTableName ] = $PLUGIN_CONFIG;

        // Valores de la petición.
        $record = $_POST;
        // Capturar parámetros de notificación antes de limpiar.
        $sendnotification = $record['sendnotification'] ?? '0';
        $notifyrolesraw = $record['notifyroles'] ?? '';
        $notifyroles = array_filter(explode(',', $notifyrolesraw));
        
        // Imágenes de la petición.
        $images = $_FILES;

        // Se obtiene el valor de la sesión.
        [ 'sesskey' => $sesskey ] = $record;

        // Se obtiene el valor de las variables de configuración.
        [ 'requestValuesToDelete' => $requestValuesToDelete ] = $controllerSettings; 
        
        // Se eliminan los valores no permitidos de la petición.
        $record = deleteValuesFromArray($record, $requestValuesToDelete);
        
        // Se verifica si la sesión está activa.
        checkSession('exception', ['Sesión inactiva, por favor inicie sesión.', 403]);

        // Se verifica si el usuario tiene el rol de administrador.
        checkUserRole('exception', ['No estas autorizado para ejecutar esta acción.', 403]);

        // Se verifica si el token de la sesión es válido.
        checkCsrfToken($sesskey);

        // Se obtiene el valor de las imágenes requeridas.
        [ 'requieredImages' => $requieredImages ] = $controllerSettings;

        // Se verifica si las imágenes requeridas existen.
        checkRequiredImages($images, $requieredImages);
        
        // Se generan los nombres de las imágenes.
        $imagesNames = generateImagesNames($images);

        // Se verifica que no se haya seleccionado la misma imagen para dispositivos diferentes.
        checkImagesNotBeTheSame($imagesNames);

        // Se obtiene el valor del nombre del registro.
        [ 'name' => $recordName ] = $record;

        // Se obtiene el valor de los valores de la consulta.
        $queryValues = [ 'name' => $recordName, ...$imagesNames ];

        // Se buscan registros repetidos.
        $records = searchRepeatedRecords('create', $queryValues);

        // Se verifica si hay registros repetidos.
        checkThereAreNotRepeatedRecords($records, $queryValues);

        // Se verifican las imágenes.
        $notAllowedImages = checkImagesContent($images);

        // Se obtiene la cantidad de imágenes no permitidas.
        $quantityOfNotAllowedImages = count($notAllowedImages);

        // Se verifica si hay imágenes no permitidas.
        if($quantityOfNotAllowedImages > 0) {
            throw new Exception(arrayKeyToStringAsList($notAllowedImages), 500);
        }

        // Se convierten las imágenes a base64.
        $images = imagesToBase64($images);

        // Se obtiene la fecha actual.
        $date = getCurrentDate();

        // Se obtienen los valores restantes.
        $remainingValues = [
        
            ...$images,
            ...$imagesNames,
            'order_display' => null,
            'created_by' => $USER->id,
            'updated_by' => null,
            'created_at' => $date,
            'updated_at' => $date
        
        ];
        
        // Se combinan los valores del registro con los valores restantes.
        $record = (object) [ ...$record, ...$remainingValues ];

        // Asegurar que se incluyan valores por defecto para course_state si no viene en la petición
        if (!property_exists($record, 'course_state')) {
            $record->course_state = '0';
        }

        // Se inserta el registro en la base de datos.
        $DB->insert_record($pluginTableName, $record);
        
        // Se verifica si el estado del registro es 1 (activo).
        // Si es así, se elimina el caché para que se muestren los cambios.
        if($record->state === '1') {
            deleteSliderCache();
        } 

        // === Almacenamiento simple de roles seleccionados en formato JSON ===
        if ($sendnotification === '1' && !empty($notifyroles)) {
            $SESSION->slider_notify_roles_json = json_encode($notifyroles, JSON_UNESCAPED_UNICODE);
        }

        // Se establece el código de estado de la respuesta.
        http_response_code(200);

        // Se envía la respuesta al cliente incluyendo el estado de la notificación.
        echo json_encode([
            'success' => 'El registro se creó de manera correcta.'
        ]);
        
        
    } catch(Exception $e) {
        // En caso de error, se captura la excepción y se envía una respuesta de error.
        $status = (($e->getCode() !== 0) ? $e->getCode() : 500);

        // Se establece el código de estado de la respuesta.
        http_response_code($status);

        // Se envía la respuesta al cliente.
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);

    }
    
} else {
    // Si el método de la petición no es POST, se envía una respuesta de error.
    // Se establece el código de estado de la respuesta.
    http_response_code(404);

    // Se envía la respuesta al cliente.
    echo json_encode([ 'error' => 'Ruta no encontrada.']);

}