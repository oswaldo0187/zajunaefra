/**
 * Script para cargar los estilos del carrusel y manejar la inicialización
 *
 * @module     mod_imagecarousel/load_styles
 * @copyright  2024 Zajuna Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Función para cargar los estilos del carrusel
 */
function cargarEstilos() {
  // Verificar si el CSS ya está cargado
  if (!document.getElementById("imagecarousel-styles")) {
    // Agregar los estilos del carrusel
    var link = document.createElement("link");
    link.id = "imagecarousel-styles";
    link.rel = "stylesheet";
    link.type = "text/css";
    link.href = M.cfg.wwwroot + "/mod/imagecarousel/styles.css";
    document.getElementsByTagName("head")[0].appendChild(link);
  }
  
  // Inicializar todos los carruseles después de cargar los estilos
  inicializarCarruseles();
}

/**
 * Función para inicializar todos los carruseles en la página
 */
function inicializarCarruseles() {
  // Esperar un momento para asegurar que los estilos se han aplicado
  setTimeout(function() {
    // Buscar todos los carruseles
    var carousels = document.querySelectorAll('.carousel.slide');
    
    if (carousels.length > 0) {
      console.log('Inicializando ' + carousels.length + ' carruseles');
      
      // Inicializar cada carrusel según la biblioteca disponible
      carousels.forEach(function(carousel) {
        try {
          var interval = parseInt(carousel.getAttribute('data-interval')) || 8000;
          
          // Intentar con Bootstrap 4/5 nativo primero
          if (typeof bootstrap !== 'undefined' && typeof bootstrap.Carousel !== 'undefined') {
            new bootstrap.Carousel(carousel, {
              interval: interval,
              wrap: true,
              keyboard: true,
              touch: true
            });
            console.log('Carrusel inicializado con Bootstrap nativo: ' + carousel.id);
          } 
          // Luego intentar con jQuery si está disponible
          else if (typeof jQuery !== 'undefined' && typeof jQuery.fn.carousel !== 'undefined') {
            jQuery(carousel).carousel({
              interval: interval,
              wrap: true,
              keyboard: true,
              touch: true
            });
            console.log('Carrusel inicializado con jQuery: ' + carousel.id);
          } else {
            console.warn('No se pudo inicializar el carrusel: ' + carousel.id + '. Bootstrap no disponible.');
          }
        } catch (error) {
          console.error('Error al inicializar carrusel: ' + carousel.id, error);
        }
      });
    }
  }, 300); // Esperar 300ms para asegurar que los estilos se han aplicado
}

// Ejecutar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", cargarEstilos);

// Exportar las funciones para uso en otros módulos
export {
  cargarEstilos,
  inicializarCarruseles
};
