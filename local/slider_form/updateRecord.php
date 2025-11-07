<?php
/**
 * Controlador para actualizar un registro en la tabla del slider
 * 
 * Este archivo maneja la actualización de registros existentes:
 * - Valida los datos del formulario
 * - Procesa imágenes si se han proporcionado
 * - Actualiza el registro en la base de datos
 * - Actualiza el caché para reflejar los cambios
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

// Configuración del controlador con valores que deben eliminarse de la petición
$controllerSettings = [

    'requestValuesToDelete' => ['sesskey', 'desktop_image', 'mobile_image' ]
    
];


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {

        global $USER, $DB;

        // Obtiene el nombre de la tabla del plugin desde la configuración
        [ 'table_name' => $pluginTableName ] = $PLUGIN_CONFIG;

        // Valores de la petición
        $record = $_POST;
        $images = $_FILES;

        // Extrae datos clave de la petición
        ['sesskey' => $sesskey, 'id' => $id, 'name' => $recordName, 'state' => $state] = $record;
        
        // Obtiene los valores a eliminar de la configuración
        ['requestValuesToDelete' => $requestValuesToDelete] = $controllerSettings; 

        // Elimina valores no permitidos de la petición
        $record = deleteValuesFromArray($record, $requestValuesToDelete);

        // Validaciones de seguridad
        checkSession('exception', ['Sesión inactiva, por favor inicie sesión.', 403]);
        checkUserRole('exception', ['No estas autorizado para ejecutar esta acción.', 403]);
        checkCsrfToken($sesskey);

        // Verifica si hay imágenes adjuntas en la petición
        $areThereImages = (count($images) > 0);

        $imagesNames = [];

        // Procesa imágenes si hay alguna
        if ($areThereImages) {

            // Genera nombres para las imágenes
            $imagesNames = generateImagesNames($images);
            // Verifica que no se use la misma imagen para diferentes dispositivos
            checkImagesNotBeTheSame($imagesNames);

        }

        // Prepara parámetros para buscar duplicados
        $queryParams = array_merge(['id' => $id, 'name' => $recordName], $imagesNames);
        // Busca registros repetidos excluyendo el registro actual
        $records = searchRepeatedRecords('update', $queryParams);
        // Verifica si hay registros repetidos
        checkThereAreNotRepeatedRecords($records, $queryParams);

        // Procesa imágenes adicionales si existen
        if ($areThereImages) {
            
            // Valida el contenido de las imágenes
            $notAllowedImages = checkImagesContent($images);

            // Si hay errores en las imágenes, lanza excepción
            if (count($notAllowedImages) > 0) {
                throw new Exception(arrayKeyToStringAsList($notAllowedImages), 400);
            }

            // Convierte imágenes a formato base64
            $images = imagesToBase64($images);

            // Combina todos los datos en el registro
            $record = [ ...$images, ...$imagesNames, ...$record ];

        }

        // Extrae el estado del registro
        [ 'state' => $state ] = $record;

        // Asegurar que exista course_state en el registro (0 por defecto)
        if (!isset($record['course_state'])) {
            $record['course_state'] = '0';
        }

        // Obtiene la fecha actual
        $date = getCurrentDate();

        // Agrega valores adicionales al registro
        $remainingValues = [
        
            // Si el estado es 0 (inactivo), establece order_display como null
            ...(($state === 0) ? [ 'order_display' => null ] : []),
            'updated_by' => $USER->id,
            'updated_at' => $date
        
        ];

        // Combina todos los valores en el registro
        $record = [...$record, ...$remainingValues];
        
        $record = (object) [...$record, ...$remainingValues];
        
    // Convertir a objeto y actualizar el registro en la base de datos
    $record = (object) $record;
    $DB->update_record($pluginTableName, $record);

        // Elimina el caché para reflejar los cambios
        deleteSliderCache(); 
        
        // Envía respuesta de éxito
        http_response_code(200);
        echo json_encode(['success' => 'El registro se actulizo de manera correcta.']);
        
    } catch(Exception $e) {
        // Manejo de errores
        $status = (($e->getCode() !== 0) ? $e->getCode() : 500);

        http_response_code($status);
        echo json_encode(['error' => $e->getMessage()]);

    }

} else {
    // Si el método de la petición no es POST
    http_response_code(404);
    echo json_encode([ 'error' => 'Ruta no encontrada.']);
    
}