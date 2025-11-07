<?php
namespace theme_zajuna\format_flexsections\output\courseformat;

use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Override de la clase content de format_flexsections para inyectar el slider
 *
 * Se coloca en theme/zajuna/classes/format_flexsections/output/courseformat
 * con namespace theme_zajuna\format_flexsections\output\courseformat para que
 * Moodle la detecte automáticamente.
 *
 * @package   theme_zajuna
 */
class content extends \format_flexsections\output\courseformat\content {

    /**
     * Añade datos del slider al contexto del template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $CFG;

        // Obtener contexto original de la clase padre.
        $data = parent::export_for_template($output);

        try {
            // Cargar helpers del tema.
            require_once($CFG->dirroot . '/theme/zajuna/lib.php');

            // Obtener datos del slider.
            $slider_data = \theme_zajuna_get_slider_data();

            // Inyectar en contexto si hay imágenes.
            $data->slider = $slider_data;

            // Log para depuración.
            error_log('ZAJUNA SLIDER OVERRIDE: Injected slider into flexsections context. Images: ' . count($slider_data['images']));
        } catch (\Throwable $e) {
            // Registrar fallo pero continuar sin slider.
            error_log('ZAJUNA SLIDER OVERRIDE: Failed to inject slider - ' . $e->getMessage());
        }

        return $data;
    }
} 