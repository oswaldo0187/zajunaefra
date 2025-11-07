<?php
namespace theme_zajuna\output;

use renderer_base;

defined('MOODLE_INTERNAL') || die();

/**
 * Theme override for format_flexsections renderer to inject slider context.
 */
class format_flexsections_renderer extends \format_flexsections\output\renderer {

    /**
     * Override render_from_template to add slider context coming from theme.
     *
     * @param string $templatename
     * @param array $context
     * @return string
     */
    public function render_from_template($templatename, $context) {
        global $CFG;

        // Only inject for flexsections templates to avoid side-effects.
        if (strpos($templatename, 'format_flexsections') === 0) {
            require_once($CFG->dirroot . '/theme/zajuna/lib.php');
            try {
                $slider_context = \theme_zajuna_get_slider_data();
                // Inject the slider data depending on the type of the provided context.
                if (is_array($context)) {
                    $context['slider'] = $slider_context;
                } else if (is_object($context)) {
                    // Allow dynamic property assignment (stdClass) or use magic setters.
                    $context->slider = $slider_context;
                }
            } catch (\Throwable $e) {
                // Silent failure – log for devs.
                debugging('theme_zajuna slider injection error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }

        return parent::render_from_template($templatename, $context);
    }
} 