<?php
namespace theme_zajuna\output;

defined('MOODLE_INTERNAL') || die();

class core_renderer extends \core_renderer {
    
    /**
     * Sobrescribe el método que genera la plantilla 'dropdown'
     * 
     * @param mixed $data
     * @return string
     */
    protected function render_my_dropdown($data) {
        // Usa la plantilla 'my/dropdown.mustache' del tema 'zajuna'
        return $this->render_from_template('my/dropdown', $data);
    }

    // Métodos del renderer removidos - ahora usando override directo de flexsections content class
}
