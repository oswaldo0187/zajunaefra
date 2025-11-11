/**
 * Zajuna slider visibility toggle handler
 *
 * @module     theme_zajuna/slider_actions
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/notification'], function($, Notification) {
    'use strict';

    /**
     * Initialize the slider action handlers
     */
    var init = function() {
        console.log('Zajuna slider actions initialized');
        
        // Handle visibility toggle clicks
        $(document).on('click', '.zajuna-slider-toggle-visibility', function(e) {
            e.preventDefault();
            console.log('Toggle visibility clicked');
            
            var $link = $(this);
            var action = $link.data('action'); // 'show' or 'hide'
            var courseid = $link.data('courseid');
            var visible = (action === 'show') ? 1 : 0;
            
            console.log('Action:', action, 'Course ID:', courseid, 'Visible:', visible);
            
            // Confirm action
            var confirmMsg = (action === 'show') 
                ? 'El banner será visible para los aprendices. ¿Continuar?' 
                : 'El banner se ocultará de los aprendices. ¿Continuar?';
            
            if (!confirm(confirmMsg)) {
                return;
            }
            
            // Build URL with sesskey as GET parameter
            var url = M.cfg.wwwroot + '/local/slider/toggle_visibility.php' +
                      '?sesskey=' + M.cfg.sesskey +
                      '&courseid=' + courseid +
                      '&visible=' + visible;
            
            console.log('Making request to:', url);
            // Make AJAX request using GET
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response);
                    
                    if (response.success) {
                        // Show success notification
                        Notification.addNotification({
                            message: response.message,
                            type: 'success'
                        });
                        // Reload the page to reflect changes
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        Notification.exception({
                            message: response.message || 'Error al actualizar la visibilidad del slider'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    
                    Notification.exception({
                        message: 'Error: ' + error + '. Revisa la consola para más detalles.'
                    });
                }
            });
        });
    };
    return {
        init: init
    };
});
