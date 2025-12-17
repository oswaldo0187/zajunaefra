<?php
// Este archivo es parte de Moodle - http://moodle.org/
require('../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__.'/classes/utils/images.php');

// Obtener el id del módulo del curso
$id = required_param('id', PARAM_INT);

// Obtener la información del curso y del módulo
$cm = get_coursemodule_from_id('imagecarousel', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('imagecarousel', array('id' => $cm->instance), '*', MUST_EXIST);

// Verificar acceso al curso
require_login($course, true, $cm);

// Configurar la página
$PAGE->set_url('/mod/imagecarousel/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

// Detectar si el usuario está en un dispositivo móvil
$is_mobile = false;
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($agent, 'mobile') !== false || strpos($agent, 'android') !== false || 
        strpos($agent, 'iphone') !== false || strpos($agent, 'ipad') !== false || 
        strpos($agent, 'ipod') !== false || strpos($agent, 'tablet') !== false) {
        $is_mobile = true;
    }
}

// Alternativamente, usar la función de Moodle para detección móvil si está disponible
if (method_exists('\core_useragent', 'get_device_type')) {
    $devicetype = \core_useragent::get_device_type();
    $is_mobile = ($devicetype === 'mobile' || $devicetype === 'tablet');
}

// Mostrar el encabezado de la página
echo $OUTPUT->header();

// Definir contexto del módulo
$contextmodule = context_module::instance($cm->id);

// Mostrar la introducción si está definida
if (!empty($moduleinstance->intro)) {
    echo $OUTPUT->box(format_module_intro('imagecarousel', $moduleinstance, $cm->id), 'generalbox mod_introbox', 'imagecarouselintro');
}

// Obtener solo las imágenes visibles
$all_images = Images::getImages($moduleinstance->id, true);

// Agregar depuración
error_log("ImageCarousel: Cargando " . count($all_images) . " imágenes para el carrusel ID: " . $moduleinstance->id);

// Mostrar todas las imágenes sin filtrar por dispositivo
$images_to_show = $all_images;

// Verificar si hay imágenes para mostrar
if (empty($images_to_show)) {
    // No hay imágenes para mostrar
    echo $OUTPUT->notification(get_string('noimagesfound', 'imagecarousel'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

// ID único para este carrusel (permite múltiples carruseles en la misma página)
$carouselid = 'imagecarousel_' . $cm->id;

// Configuración del carrusel
$autoplay = $moduleinstance->autoplay ?? false;
$autoplaytime = $moduleinstance->autoplaytime ?? 5000;
$showcontrols = $moduleinstance->showcontrols ?? true;
$showindicators = $moduleinstance->showindicators ?? true;
$width = $moduleinstance->width ?? '100%';
$height = $moduleinstance->height ?? '400px';

// Iniciar contenedor del carrusel
echo '<div class="carousel-container">';
echo '<div id="' . $carouselid . '" class="carousel slide" data-ride="carousel">';

// Depuración
error_log("ImageCarousel: Mostrando " . count($images_to_show) . " imágenes en total");

// Agregar indicadores si están habilitados
if ($showindicators && !empty($images_to_show)) {
    echo '<ol class="carousel-indicators">';
    $count = count($images_to_show);
    for ($i = 0; $i < $count; $i++) {
        $activeclass = ($i == 0) ? ' class="active"' : '';
        echo '<li data-target="#' . $carouselid . '" data-slide-to="' . $i . '"' . $activeclass . '></li>';
    }
    echo '</ol>';
}

// Iniciar interior del carrusel
echo '<div class="carousel-inner">';

// Mostrar todas las imágenes
$first_item = true;
foreach ($images_to_show as $image) {
    $activeclass = $first_item ? ' active' : '';
    $first_item = false;
    
    // Determinar qué imagen mostrar (mobile o desktop)
    $image_src = '';
    $image_name = '';
    $image_class = '';

    // Mostrar ambas imágenes si existen
    if (!empty($image->desktop_image)) {
        $image_src = $image->desktop_image;
        $image_name = $image->desktop_image_name;
        $image_class = 'desktop-view';
        error_log("ImageCarousel: Mostrando imagen desktop para ID: " . $image->id);
    } elseif (!empty($image->mobile_image)) {
        $image_src = $image->mobile_image;
        $image_name = $image->mobile_image_name;
        $image_class = 'mobile-view';
        error_log("ImageCarousel: Mostrando imagen mobile para ID: " . $image->id);
    }
    
    error_log("ImageCarousel: Datos de imagen - tipo fuente: " . 
             (!empty($image_src) && filter_var($image_src, FILTER_VALIDATE_URL) ? 'URL' : 'Base64/otro') . 
             ", longitud: " . (!empty($image_src) ? (strlen($image_src) > 100 ? strlen($image_src) . ' chars' : 'corta') : 'vacía'));
    
    echo '<div class="carousel-item' . $activeclass . '">';
    
    // Mostrar la imagen con enlace si es necesario
    if (!empty($image->url)) {
        echo '<a href="' . $image->url . '" target="_blank">';
    }
    
    // Determinar si la imagen es una URL o Base64
    if (!empty($image_src) && filter_var($image_src, FILTER_VALIDATE_URL)) {
        // Es una URL externa
        echo '<img class="d-block w-100 ' . $image_class . '" src="' . $image_src . '" alt="">';
    } else {
        // Es una imagen en Base64
        $imageData = $image_src;
        // Determinar el tipo de imagen basado en el nombre del archivo
        $imageType = 'jpeg'; // Por defecto
        $extension = '';
        if (!empty($image_name)) {
            $extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        }
        if ($extension === 'png') {
            $imageType = 'png';
        } elseif ($extension === 'webp') {
            $imageType = 'webp';
        } else {
            $imageType = 'jpeg';
        }
        
        // Verificar si los datos de imagen son válidos
        if (empty($imageData)) {
            echo '<!-- Error: Datos de imagen vacíos -->';
            echo '<div class="alert alert-warning">Imagen no disponible</div>';
        } else {
            // Verificar si los datos ya contienen el prefijo data:image
            if (!empty($imageData) && strpos($imageData, 'data:image/') === 0) {
                // Usar los datos tal como están
                echo '<img class="d-block w-100 ' . $image_class . '" src="' . $imageData . '" alt="">';
            } else {
                // Agregar el prefijo data:image para imágenes Base64
                echo '<img class="d-block w-100 ' . $image_class . '" src="data:image/' . $imageType . ';base64,' . $imageData . '" alt="">';
            }
        }
    }
    
    if (!empty($image->url)) {
        echo '</a>';
    }
    
    echo '</div>'; // Fin del elemento del carrusel
}

echo '</div>'; // Fin del interior del carrusel

// Mostrar controles de navegación si están habilitados
if ($showcontrols && !empty($images_to_show)) {
    echo '<a class="carousel-control-prev" href="#' . $carouselid . '" role="button" data-slide="prev">
        <span style="margin-left: 2em;" class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#' . $carouselid . '" role="button" data-slide="next">
        <span style="margin-right: 2em;" class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>';
}

echo '</div>'; // Fin del contenedor del carrusel

// Si hay autoplay, agregar JavaScript
if ($autoplay) {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        try {
            var carousel = document.getElementById("' . $carouselid . '");
            if (typeof bootstrap !== "undefined" && bootstrap.Carousel) {
                var carousel_instance = new bootstrap.Carousel(carousel, {
                    interval: ' . $autoplaytime . ',
                    wrap: true
                });
            } else {
                // Fallback para versiones anteriores de Bootstrap
                $(carousel).carousel({
                    interval: ' . $autoplaytime . ',
                    wrap: true
                });
            }
        } catch (e) {
            console.error("Error initializing carousel:", e);
        }
    });
    </script>';
}

// Agregar JavaScript para mejorar la experiencia en todos los dispositivos
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    // Referencia al carrusel
    var carousel = document.getElementById("' . $carouselid . '");
    if (!carousel) return;

    
    // Mejorar soporte para eventos táctiles en todos los dispositivos
    var touchStartX = 0;
    var touchEndX = 0;
    
    carousel.addEventListener("touchstart", function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    carousel.addEventListener("touchend", function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);
    
    function handleSwipe() {
        var threshold = 50; // Umbral para detectar deslizamiento
        var swipeDistance = Math.abs(touchEndX - touchStartX);
        
        if (swipeDistance > threshold) {
            try {
                if (touchEndX < touchStartX) {
                    // Deslizar a la izquierda - siguiente
                    if (typeof bootstrap !== "undefined" && bootstrap.Carousel) {
                        var carouselInstance = bootstrap.Carousel.getInstance(carousel);
                        if (carouselInstance) {
                            carouselInstance.next();
                        } else {
                            // Fallback para versiones anteriores
                            $(carousel).carousel("next");
                        }
                    } else {
                        // Fallback para versiones anteriores
                        $(carousel).carousel("next");
                    }
                } else {
                    // Deslizar a la derecha - anterior
                    if (typeof bootstrap !== "undefined" && bootstrap.Carousel) {
                        var carouselInstance = bootstrap.Carousel.getInstance(carousel);
                        if (carouselInstance) {
                            carouselInstance.prev();
                        } else {
                            // Fallback para versiones anteriores
                            $(carousel).carousel("prev");
                        }
                    } else {
                        // Fallback para versiones anteriores
                        $(carousel).carousel("prev");
                    }
                }
            } catch (e) {
                console.error("Error handling swipe:", e);
            }
        }
    }
    
    // Asegurar que los controles de navegación funcionen correctamente
    var prevButton = carousel.querySelector(".carousel-control-prev");
    var nextButton = carousel.querySelector(".carousel-control-next");
    
    if (prevButton && nextButton) {
        // Para asegurar que los controles funcionen en todos los dispositivos
        prevButton.addEventListener("click", function(e) {
            e.preventDefault();
            try {
                if (typeof bootstrap !== "undefined" && bootstrap.Carousel) {
                    var carouselInstance = bootstrap.Carousel.getInstance(carousel);
                    if (carouselInstance) {
                        carouselInstance.prev();
                    } else {
                        // Fallback para versiones anteriores
                        $(carousel).carousel("prev");
                    }
                } else {
                    // Fallback para versiones anteriores
                    $(carousel).carousel("prev");
                }
            } catch (e) {
                console.error("Error navigating carousel:", e);
            }
        });
        
        nextButton.addEventListener("click", function(e) {
            e.preventDefault();
            try {
                if (typeof bootstrap !== "undefined" && bootstrap.Carousel) {
                    var carouselInstance = bootstrap.Carousel.getInstance(carousel);
                    if (carouselInstance) {
                        carouselInstance.next();
                    } else {
                        // Fallback para versiones anteriores
                        $(carousel).carousel("next");
                    }
                } else {
                    // Fallback para versiones anteriores
                    $(carousel).carousel("next");
                }
            } catch (e) {
                console.error("Error navigating carousel:", e);
            }
        });
    }
});
</script>';

// Mostrar pie de página
echo $OUTPUT->footer(); 