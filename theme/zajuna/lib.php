<?php

// Every file should have GPL and copyright in the header - we skip it in tutorials but you should not skip it for real.

// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();

// We will add callbacks here as we add features to our theme.

	/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string All fixed Sass for this theme.
 */
function theme_zajuna_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';

    $fs = get_file_storage();

    // Main CSS - Get the CSS from theme Classic.
    $scss .= file_get_contents($CFG->dirroot . '/theme/classic/scss/classic/pre.scss');
    $scss .= file_get_contents($CFG->dirroot . '/theme/classic/scss/preset/default.scss');
    $scss .= file_get_contents($CFG->dirroot . '/theme/classic/scss/classic/post.scss');

    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    $pre = file_get_contents($CFG->dirroot . '/theme/zajuna/scss/pre.scss');

    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->dirroot . '/theme/zajuna/scss/post.scss');

    // Combine them together.
    return $pre . "\n" . $scss . "\n" . $post;
}

/**
 * Funciones del Slider para el tema Zajuna
 * Adaptadas desde local_slider plugin
 */

/**
 * Obtiene las imágenes activas del slider desde la base de datos
 *
 * @return array Arreglo con información de las imágenes activas
 */
/**
 * Obtiene las imágenes activas del slider desde la base de datos
 *
 * @return array Arreglo con información de las imágenes activas
 */
function theme_zajuna_get_slider_images() {
    global $DB, $COURSE, $PAGE;

    try {
        $dbman = $DB->get_manager();
        // Detectar si estamos en una página de curso para filtrar por course_state
        $iscourse = (isset($COURSE) && !empty($COURSE->id) && intval($COURSE->id) > 1);
        
        // Verificar si el usuario actual puede editar el curso (es profesor/admin)
        $context = \context_course::instance($COURSE->id);
        $can_edit = has_capability('moodle/course:update', $context);

        // Primero intenta con la tabla local_slider
        if ($dbman->table_exists('local_slider')) {
            $sql = "SELECT id, desktop_image, mobile_image, url, order_display, visible_to_students
                    FROM {local_slider}
                    WHERE " . $DB->sql_compare_text('state') . " = ?";
            $params = ['1'];

            if ($iscourse) {
                // Mostrar solo las imágenes que también están habilitadas para curso
                $sql .= " AND course_state = ?";
                $params[] = '1';
                
                // Si el usuario NO puede editar (es estudiante), filtrar por visibilidad
                if (!$can_edit) {
                    $sql .= " AND visible_to_students = ?";
                    $params[] = '1';
                }
            }

            $sql .= " ORDER BY order_display ASC";
            $records = $DB->get_records_sql($sql, $params);

            if (!empty($records)) {
                return array_values($records);
            }
        }

        // Si no encuentra nada, intenta con la tabla imagecarousel
        if ($dbman->table_exists('imagecarousel')) {
            $sql = "SELECT id, desktop_image, mobile_image, url, order_display
                    FROM {imagecarousel}
                    WHERE " . $DB->sql_compare_text('state') . " = ?
                    ORDER BY order_display ASC";
            $records = $DB->get_records_sql($sql, ['1']);

            if (!empty($records)) {
                return array_values($records);
            }
        }

        // Si no hay registros, devolver vacío
        return [];

    } catch (Exception $e) {
        debugging("Error en theme_zajuna_get_slider_images: " . $e->getMessage(), DEBUG_DEVELOPER);
        return [];
    }
}





/**
 * Obtiene los datos del slider formateados para el template Mustache
 *
 * @return array Datos del slider para el template
 */
function theme_zajuna_get_slider_data() {
    global $COURSE, $PAGE;
    
    $images = theme_zajuna_get_slider_images();
    
    if (empty($images)) {
        return ['has_images' => false, 'images' => [], 'can_edit' => false, 'is_visible_to_students' => true];
    }
    
    // Verificar si el usuario puede editar
    $context = \context_course::instance($COURSE->id);
    $can_edit = has_capability('moodle/course:update', $context);
    $is_editing = $PAGE->user_is_editing();
    
    // Determinar el estado de visibilidad (usamos el primer registro como referencia)
    $is_visible_to_students = true;
    if (isset($images[0]->visible_to_students)) {
        $is_visible_to_students = ($images[0]->visible_to_students === '1');
    }
    
    $formatted_images = [];
    foreach ($images as $index => $image) {
        // Preparar URL
        $url = !empty($image->url) ? trim($image->url) : '';
        if ($url && $url !== '#' && !preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }
        
        $formatted_images[] = [
            'id' => $image->id,
            'index' => $index,
            'first' => ($index === 0),
            'desktop_image' => $image->desktop_image,
            'mobile_image' => $image->mobile_image,
            'url' => $url ?: '#',
            'has_url' => !empty($url) && $url !== '#'
        ];
    }
    
    return [
        'has_images' => true,
        'images' => $formatted_images,
        'title' => '',
        'can_edit' => $can_edit,
        'is_editing' => $is_editing,
        'is_visible_to_students' => $is_visible_to_students,
        'courseid' => $COURSE->id
    ];
}

/**
 * Carga los assets necesarios para el slider
 *
 * @param moodle_page $page Objeto de página de Moodle
 */
function theme_zajuna_load_slider_assets($page) {
    // JavaScript personalizado para el carousel Bootstrap 4 y visibilidad del slider
    $page->requires->js_init_code('
        require(["jquery"], function($) {
            console.log("Zajuna slider scripts starting...");
            
            // Esperar a que el DOM esté listo
            $(document).ready(function() {
                console.log("Zajuna slider scripts loaded - DOM ready");
                
                // ============== VISIBILITY TOGGLE FUNCTIONALITY ==============
                console.log("Setting up visibility toggle...");
                
                // Event delegation para manejar clics en el menú de acciones
                $(document).on("click", ".zajuna-slider-toggle-visibility", function(e) {
                    e.preventDefault();
                    console.log("Visibility toggle clicked!");
                    
                    var link = $(this);
                    var action = link.data("action");
                    var courseid = link.data("courseid");
                    
                    console.log("Action:", action, "Course ID:", courseid);
                    
                    // Determinar el mensaje de confirmación
                    var confirmMessage = (action === "hide") 
                        ? "¿Está seguro que desea ocultar el banner a los aprendices?"
                        : "¿Está seguro que desea mostrar el banner a los aprendices?";
                    
                    if (!confirm(confirmMessage)) {
                        console.log("User cancelled action");
                        return;
                    }
                    
                    console.log("User confirmed, sending AJAX request");
                    
                    // Realizar la petición AJAX
                    $.ajax({
                        url: M.cfg.wwwroot + "/local/slider/toggle_visibility.php",
                        method: "POST",
                        data: {
                            courseid: courseid,
                            sesskey: M.cfg.sesskey
                        },
                        dataType: "json"
                    })
                    .done(function(response) {
                        console.log("AJAX response:", response);
                        
                        if (response.success) {
                            alert(response.message);
                            window.location.reload();
                        } else {
                            alert("Error: " + (response.message || "Unknown error"));
                        }
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        console.error("AJAX error:", textStatus, errorThrown);
                        console.error("Response:", jqXHR.responseText);
                        alert("Error al cambiar la visibilidad: " + textStatus);
                    });
                });
                
                console.log("Visibility toggle setup complete");
                
                // ============== CAROUSEL FUNCTIONALITY ==============
                function initZajunaCarousel() {
                    if ($("#zajunaCarousel").length === 0) {
                        return;
                    }
                    
                    // Configurar imágenes responsivas
                    function setResponsiveImages() {
                        $(".carousel-item").each(function() {
                            var $item = $(this);
                            var $img = $item.find(".zajuna-slider-img");
                            var desktopSrc = $item.data("desktop");
                            var mobileSrc = $item.data("mobile");
                            
                            if (!desktopSrc) return;
                            
                            // Determinar qué imagen usar
                            var imgSrc = (window.innerWidth <= 768 && mobileSrc) ? mobileSrc : desktopSrc;
                            
                            // Si no tiene prefijo data:image, añadirlo
                            if (!imgSrc.startsWith("data:image/")) {
                                imgSrc = "data:image/jpeg;base64," + imgSrc;
                            }
                            
                            $img.attr("src", imgSrc);
                            $img.attr("alt", "Slider Image");
                        });
                    }
                    
                    // Configurar imágenes iniciales
                    setResponsiveImages();
                    
                    // Configurar carousel Bootstrap 4 - Comentado temporalmente
                    // El carousel de Bootstrap se inicializa automáticamente con data-ride="carousel"
                    // $("#zajunaCarousel").carousel({
                    //     interval: 4000,
                    //     pause: "hover",
                    //     wrap: true
                    // });
                    
                    // Manejar cambios de tamaño de ventana
                    $(window).on("resize", function() {
                        setResponsiveImages();
                    });
                    
                    // Manejar botón de mostrar/ocultar slider
                    $("#zajunaSliderCollapse").on("show.bs.collapse", function() {
                        $(".zajuna-slider-toggle-text").text("Ocultar");
                        $(".zajuna-slider-toggle-icon").removeClass("fa-chevron-down").addClass("fa-chevron-up");
                    });
                    
                    $("#zajunaSliderCollapse").on("hide.bs.collapse", function() {
                        $(".zajuna-slider-toggle-text").text("Mostrar");
                        $(".zajuna-slider-toggle-icon").removeClass("fa-chevron-up").addClass("fa-chevron-down");
                    });
                }
                
                initZajunaCarousel();
            });
        });
    ');
}