<?php
/**
 * Archivo para la gestión y visualización del slider en el sitio
 *
 * Este archivo contiene las funciones necesarias para cargar los activos del slider,
 * generar el HTML del slider y obtener las imágenes de la base de datos.
 *
 * @package     local_slider
 */

    require_once(__DIR__ . '/utils.php');

    /**
     * Carga los archivos CSS y JavaScript necesarios para el funcionamiento del slider
     *
     * Esta función carga el CSS y JavaScript de Swiper desde CDN y también el script
     * personalizado para controlar el comportamiento del slider según el tamaño de pantalla.
     */
    function loadSwiperAssets() {

        global $PAGE;

        $PAGE->requires->css(new moodle_url('https://unpkg.com/swiper/swiper-bundle.min.css'));
        $PAGE->requires->js(new moodle_url('https://unpkg.com/swiper/swiper-bundle.min.js'));
        $PAGE->requires->js(new moodle_url('/local/slider/js/screenScript.js'));


        $PAGE->requires->js_init_code('
            
            require(["jquery"], function($) {
                $(document).ready(function() {
                    
                    var swiper = new Swiper(".mySwiper", {
                        spaceBetween: 30,
                        centeredSlides: true,
                        autoplay: {
                            delay: 2500,
                            disableOnInteraction: false,
                        },
                        pagination: {
                            el: ".swiper-pagination",
                            clickable: true,
                        },
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                    });

                });
            
            });

        ');
        
    }

    /**
     * Genera el HTML del slider con las imágenes proporcionadas
     *
     * @param array $images Arreglo con información de las imágenes a mostrar en el slider
     * @return string HTML generado para el slider
     */
    function generateSlider($images, $iscourse = false) {

        global $OUTPUT;

    // If not inside a course, constrain the slider height to match the course view appearance.
    // Use a flex container and center the image so we show the whole image proportionally
    // instead of cropping it with object-fit:cover.
    $swiperStyle = $iscourse ? '' : 'style="max-height:300px; display:flex; align-items:center; justify-content:center; overflow:hidden;"';

        $slider = '
            
            <div class="swiper mySwiper" ' . $swiperStyle . '>
                <div class="swiper-wrapper">
                    [REPLACE]
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        
    ';

        // Default slide placeholder. If not in a course, constrain image height and use object-fit to crop.
        $slides = [
            (
                $iscourse
                ? '
                    <div class="swiper-slide">
                        <a href="#">
                            <img src="'. $OUTPUT->image_url('default-image', 'local_slider')->out() .'" alt="Slide" style="width: 100%;">
                        </a>
                    </div>
                  '
                    : '
                    <div class="swiper-slide">
                        <a href="#">
                            <img src="'. $OUTPUT->image_url('default-image', 'local_slider')->out() .'" alt="Slide" style="width:100%; height:auto; max-height:300px; display:block;">
                        </a>
                    </div>
                  '
            )

        ];

        foreach ($images as $image) {
            
			[ 'desktop_image' => $imageDesktop, 'mobile_image' => $imageMobile, 'url' => $url ] = (array) $image;
            
            // Asegurar que la URL tenga el protocolo correcto
            if ($url && !empty($url) && $url != '#') {
                // Verificar si la URL ya tiene un protocolo
                if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                    $url = 'https://' . $url;
                }
            }
            
            // For non-course pages, force a max-height and keep the image proportional (no cropping).
            $imgStyle = $iscourse ? 'width: 100%;' : 'width:100%; height:auto; max-height:300px; display:block;';

            $slides[] = '

                <div class="swiper-slide" data-desktop="'.$imageDesktop.'" data-mobile="'.$imageMobile.'">
                    <a href="'.($url ? $url : '#').'" '.($url ? 'target="_blank"' : '').'>
                        <img src="" alt="" style="'.$imgStyle.'">
                    </a>
                </div>

            ';

		}

		if(count($slides) > 1) {
			array_shift($slides);
		}
		
		$slides = implode('', $slides);

        $slider = str_replace('[REPLACE]', $slides, $slider);

        return $slider;

    }

    /**
     * Obtiene las imágenes activas de la base de datos
     *
     * @return array Arreglo con información de las imágenes activas
     */
    function getImages($onlycourse = false) {
    global $DB;

    // Base de la consulta: solo imágenes con estado activo en plataforma
    $sql = "SELECT * FROM {local_slider} WHERE " . $DB->sql_compare_text('state') . " = ?";
    $params = ['1'];

    // Si solicitamos imágenes para mostrar dentro de un curso, filtrar también por course_state
    if ($onlycourse) {
        $sql .= " AND course_state = ?";
        $params[] = '1';
    }

    // Ejecuta la consulta de forma segura
    $images = $DB->get_records_sql($sql, $params);

    // Ordenar si hay más de un resultado
    if (count($images) > 1) {
        $images = sortedByAttribute($images, 'order_display');
    }

    return $images;
}

    /**
     * Punto de entrada principal para mostrar el slider en la página
     *
     * Esta función carga los activos, obtiene las imágenes del caché o la base de datos,
     * genera el HTML del slider y lo devuelve para su visualización.
     *
     * @return string HTML completo del slider con título
     */
    function slider() {

        loadSwiperAssets();

        $title = '<h6 id="instance-5-header" 
        class="card-title text-uppercase bg-white p-2 font-weight-bold  bg-white " 
        style="color: #39a900; border-radius:12px; 
        box-shadow:0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);">'
        .get_string('title', 'local_slider').'</h6>'; 

        global $COURSE;

        // Detectar si estamos en una página de curso (id > 1). Si es así, pedimos solo imágenes habilitadas para curso.
        $iscourse = (isset($COURSE) && !empty($COURSE->id) && intval($COURSE->id) > 1);

        $cache = cache::make('local_slider', 'imagecache');
        $cachekey = $iscourse ? 'images_course' : 'images_site';

        $images = $cache->get($cachekey);

        if (!$images) {
            $images = getImages($iscourse);
            $cache->set($cachekey, serialize($images));
        } else {
            $images = unserialize($images);
        }

    $slider = generateSlider($images, $iscourse);

        $htmlContent = $title.$slider;

        return $htmlContent;

    }