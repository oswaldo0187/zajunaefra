<?php
/**
 * Controlador para eliminar un registro de la tabla del slider
 * 
 * Este archivo maneja la eliminación de registros existentes:
 * - Valida la sesión del usuario y sus permisos
 * - Elimina el registro de la base de datos
 * - Actualiza el caché si el registro era activo
 * 
 * @package     local_slider_form
 */

header('Content-Type: application/json');

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/lib/utilsFunctions.php');
require_once(__DIR__ . '/lib/usersValidations.php');
require_once($CFG->dirroot . '/cache/lib.php');

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {

    try {

        global $DB;

        // Obtiene el nombre de la tabla del plugin desde la configuración
        [ 'table_name' => $pluginTableName ] = $PLUGIN_CONFIG;

        // Validaciones de seguridad
        checkSession('exception', ['Sesión inactiva, por favor inicie sesión.', 403]);
        checkUserRole('exception', ['No estas autorizado para ejecutar esta acción.', 403]);

        // Obtiene datos de la solicitud DELETE
        $requestData = file_get_contents("php://input");
        $requestData = json_decode($requestData, true);

        // Obtiene el token de sesión
        $sesskey = $requestData['sesskey'];

        // Verifica el token CSRF
        checkCsrfToken($sesskey);

        // Extrae el ID y estado del registro a eliminar
        [ 'id' => $recordId, 'state' => $state ] = $requestData;

        // Elimina el registro de la base de datos
        $DB->delete_records($pluginTableName, ['id' => $recordId]);

        // Si el estado era 1 (activo), elimina el caché para reflejar los cambios
        if($state == '1') {
            deleteSliderCache();
        }
        
        // Envía respuesta de éxito
        http_response_code(200);
        echo json_encode(['success' => 'La imagen ha sido eliminada de manera correcta.']);

    } catch(Exception $e) {
        // Manejo de errores
        $status = (($e->getCode() !== 0) ? $e->getCode() : 500);

        http_response_code($status);
        echo json_encode(['error' => $e->getMessage()]);

    }
    
} else {
    // Si el método de la petición no es DELETE
    http_response_code(404);
    echo json_encode([ 'error' => 'Ruta no encontrada.']);

}

