// Zajuna slider visibility toggle functionality
(function() {
    'use strict';
    
    console.log('Zajuna slider visibility script loaded');
    
    // Wait for document ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVisibilityToggle);
    } else {
        initVisibilityToggle();
    }
    
    function initVisibilityToggle() {
        console.log('Initializing visibility toggle');
        
        // Use event delegation on document
        document.addEventListener('click', function(e) {
            // Check if clicked element is the visibility toggle link
            var target = e.target.closest('.zajuna-slider-toggle-visibility');
            if (!target) return;
            
            e.preventDefault();
            console.log('Toggle clicked');
            
            var action = target.dataset.action;
            var courseid = target.dataset.courseid;
            var visible = (action === 'show') ? 1 : 0;
            
            console.log('Action:', action, 'Course:', courseid, 'Visible:', visible);
            
            // Confirmación
            var confirmMsg = (action === 'show') 
                ? 'El banner será visible para los aprendices. ¿Continuar?' 
                : 'El banner se ocultará de los aprendices. ¿Continuar?';
            
            if (!confirm(confirmMsg)) {
                console.log('User cancelled');
                return;
            }
            
            // Construir URL con sesskey
            var url = M.cfg.wwwroot + '/local/slider/toggle_visibility.php' +
                      '?sesskey=' + M.cfg.sesskey +
                      '&courseid=' + courseid +
                      '&visible=' + visible;
            
            console.log('Request URL:', url);
            
            // Hacer petición AJAX usando fetch
            fetch(url)
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    console.log('Response:', data);
                    
                    if (data.success) {
                        alert('✓ ' + data.message);
                        // Recargar página
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo actualizar'));
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    alert('Error de conexión: ' + error.message);
                });
        });
    }
})();
