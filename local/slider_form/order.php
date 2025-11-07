<?php
/**
 * Controlador para actualizar el orden de visualización de imágenes del slider
 * 
 * Este archivo maneja la actualización del orden de visualización de las imágenes:
 * - Valida la sesión del usuario y sus permisos
 * - Procesa los datos de ordenamiento
 * - Actualiza los registros en la base de datos utilizando transacciones
 * - Actualiza el caché para reflejar los cambios
 * 
 * @package     local_slider_form
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/lib/utilsFunctions.php');
require_once(__DIR__ . '/lib/usersValidations.php');
require_once($CFG->dirroot . '/cache/lib.php');

if ($_SERVER["REQUEST_METHOD"] == "PUT") {

    try {

        global $DB;

        // Obtiene el nombre de la tabla del plugin desde la configuración
        [ 'table_name' => $pluginTableName ] = $PLUGIN_CONFIG;

        // Validaciones de seguridad
        checkSession('exception', ['Sesión inactiva, por favor inicie sesión.', 403]);
        checkUserRole('exception', ['No estas autorizado para ejecutar esta acción.', 403]);

        // Obtiene datos de la solicitud PUT
        $requestData = file_get_contents("php://input");
        $requestData = json_decode($requestData, true);

        // Obtiene el token de sesión
        $sesskey = $requestData['sesskey'];

        // Verifica el token CSRF
        checkCsrfToken($sesskey);

        // Obtiene los registros a actualizar
        ["recordsToUpdate" => $recordsToUpdate] = $requestData;

        // Inicia una transacción para garantizar que todos los registros se actualicen correctamente
        $transaction = $DB->start_delegated_transaction();

        // Actualiza cada registro con su nuevo orden
        foreach ($recordsToUpdate as $record) {

            // Decodifica el registro JSON a un array asociativo
            $record = json_decode($record, true);

            // Verifica que la decodificación JSON haya sido exitosa
            if(!(json_last_error() === JSON_ERROR_NONE)) {
                throw new Exception('Error al convertir el objeto.', 500);
            }

            // Actualiza el registro en la base de datos
            $DB->update_record($pluginTableName, $record);

        }

        // Confirma la transacción si todo ha sido exitoso
        $transaction->allow_commit();

        // Elimina el caché para reflejar los cambios
        deleteSliderCache();

        // Envía respuesta de éxito
        http_response_code(200);
        echo json_encode(['success' => 'El orden de las imágenes se actualizó de manera correcta.']);

    } catch(Exception $e) {
        // Si hay una excepción y existe una transacción activa, revertir los cambios
        if(isset($transaction)) {
            $transaction->rollback($e);
        }
        
        // Manejo de errores
        $status = (($e->getCode() !== 0) ? $e->getCode() : 500);

        http_response_code($status);
        echo json_encode(['error' => $e->getMessage()]);

    }
    
} else {
    // Si el método de la petición no es PUT
    http_response_code(404);
    echo json_encode([ 'error' => 'Ruta no encontrada.']);

}

