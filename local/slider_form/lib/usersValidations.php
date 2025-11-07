<?php
/**
 * Funciones de validación relacionadas con usuarios
 * 
 * Este archivo contiene funciones para validar la sesión del usuario,
 * permisos de rol y tokens CSRF para proteger las operaciones del slider.
 * 
 * @package     local_slider_form
 */

    require_once(__DIR__ . '/utilsFunctions.php');

    $functions = [

        'redirect' => 'redirectTo',
        'exception' => 'createException'

    ];

    /**
     * Verifica si el usuario ha iniciado sesión
     * 
     * @param string $action Acción a realizar si la sesión no está activa ('redirect' o 'exception')
     * @param array $callbackParams Parámetros para la función de callback
     */
    function checkSession($action, $callbackParams) {

        global $functions;

        $sessionStatus = isloggedin();

        if(!($sessionStatus)) {
            call_user_func($functions[$action], ...$callbackParams);
        }

    }

    /**
     * Verifica si el usuario tiene el rol necesario para gestionar el slider
     * 
     * @param string $action Acción a realizar si el usuario no tiene permisos ('redirect' o 'exception')
     * @param array $callbackParams Parámetros para la función de callback
     */
    function checkUserRole($action, $callbackParams) {

        global $functions;

        $context = context_system::instance();

        $capability = has_capability('local/slider_form:manage', $context);

        if (!$capability) {
            call_user_func($functions[$action], ...$callbackParams);
        }

    }

    /**
     * Verifica la validez del token CSRF para proteger contra ataques CSRF
     * 
     * @param string $sesskey Token CSRF a verificar
     * @throws Exception Si el token no es válido o no está presente
     */
    function checkCsrfToken($sesskey) {

        if(!(isset($sesskey))) {
            throw new Exception('Clave de sesion no proporcionada.', 403);
        }

        if((!(gettype($sesskey) === 'string')) || (empty($sesskey)) ) {
            throw new Exception('Formato de clave de sesion incorrecto.', 403);
        }

        $isAValidKey = confirm_sesskey($sesskey);

        if(!$isAValidKey) {
            throw new Exception('clave de sesión incorrecta.', 403);
        }

    }

