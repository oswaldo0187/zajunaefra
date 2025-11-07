<?php
/**
 * Clase para manejar el contexto del slider en flexsections
 *
 * @package theme_zajuna
 */

namespace theme_zajuna;

defined('MOODLE_INTERNAL') || die();

/**
 * Clase para preparar datos del slider para templates
 */
class flexsections_slider_context {

    /**
     * Obtiene el contexto completo del slider para el template
     *
     * @param \moodle_page $page Página actual
     * @return array Contexto para el template
     */
    public static function get_slider_context($page) {
        global $OUTPUT, $CFG;

        // Asegurar que las funciones del tema estén cargadas
        require_once($CFG->dirroot . '/theme/zajuna/lib.php');
        
        // Cargar assets del slider
        \theme_zajuna_load_slider_assets($page);
        
        // Obtener datos del slider
        $slider_data = \theme_zajuna_get_slider_data();
        
        // Debug disponible si se necesita
        // if (debugging() && isset($page->course)) {
        //     debugging('Zajuna Slider: Obteniendo contexto para curso ' . $page->course->id . 
        //              '. Imágenes encontradas: ' . count($slider_data['images']), DEBUG_DEVELOPER);
        // }
        
        // Preparar contexto para el template
        $context = [
            'slider' => $slider_data
        ];

        return $context;
    }
} 