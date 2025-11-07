/*-------------------------------------------------------------*/
/*--------------------------[VARIABLES]------------------------*/
/*-------------------------------------------------------------*/

let recordId = 0; // Almacena el ID del registro actual que se está editando
let recordToDelete = {}; // Almacena la información del registro que se va a eliminar

// Desactivar alertas de navegación para toda la aplicación
window.addEventListener('DOMContentLoaded', function() {
    // Desactivar cualquier evento beforeunload existente
    window.onbeforeunload = null;
    
    // Interceptar y prevenir futuras asignaciones de onbeforeunload
    const originalDescriptor = Object.getOwnPropertyDescriptor(window, 'onbeforeunload');
    Object.defineProperty(window, 'onbeforeunload', {
        get: function() {
            return null;
        },
        set: function() {
            // Ignorar intentos de establecer onbeforeunload
            return null;
        },
        configurable: true
    });
    
    // Interceptar addEventListener para prevenir eventos beforeunload
    const originalAddEventListener = window.addEventListener;
    window.addEventListener = function(type, listener, options) {
        if (type === 'beforeunload') {
            // Ignorar intentos de agregar evento beforeunload
            return;
        }
        return originalAddEventListener.call(this, type, listener, options);
    };
});

/*-------------------------------------------------------------*/
/*------------------------[END VARIABLES]----------------------*/
/*-------------------------------------------------------------*/

/*-------------------------------------------------------------*/
/*-------------------------[DOM ACTIONS]-----------------------*/
/*-------------------------------------------------------------*/

/**
 * Obtiene los datos de un formulario por su ID y los convierte en un objeto
 * Maneja diferentes tipos de campos (texto, checkbox, archivos)
 * @param {string} formId - ID del formulario a procesar
 * @return {object} Objeto con los datos del formulario
 */
function getFormData(formId) {

    const form = document.getElementById(formId);
    const elements = form.elements;

    let formData = {};

    // Para almacenar roles seleccionados
    let selectedRoles = [];

    const elementsTypes = {

        checkbox: (name, element) => {

            const { checked } = element;

            // Detectar checkboxes de roles (prefijo notifyrole_)
            if (name.startsWith('notifyrole_')) {
                if (checked) {
                    const roleid = name.replace('notifyrole_', '');
                    selectedRoles.push(roleid);
                }
                return; // No añadir campo individual al formData
            }

            formData[name] = (checked) ? '1' : '0';

        },
        file: (name, element) => {

            const { files } = element;

            formData[name] = files[0]; 
    
        },
        'select-multiple': (name, element) => {
            const selected = Array.from(element.options)
                                .filter(opt => opt.selected)
                                .map(opt => opt.value);
            formData[name] = selected.join(',');
        }  

    }; 

    for (const element of elements) {

        const { name, type } = element;

        if(elementsTypes.hasOwnProperty(type)) {

            const { [type]: getElementValue } = elementsTypes;

            getElementValue(name, element);
            continue;

        }

        const { value } = element;

        formData[name] = value;

    }
    
    // Una vez procesados todos los elementos, añadir lista de roles seleccionados
    formData['notifyroles'] = selectedRoles.join(',');
    return formData;

}

/**
 * Carga los datos de un registro en un formulario para su edición
 * @param {string} formId - ID del formulario donde se cargarán los datos
 * @param {object} dataToShowOnForm - Datos que se mostrarán en el formulario
 */
function loadRecordDataOnForm(formId, dataToShowOnForm) {

    const form = document.getElementById(formId);
    const elements = form.elements;

    const attributesToShow = Object.keys(dataToShowOnForm);

    const elementsTypes = {

        checkbox: (element, value) => {

            element.checked = ((value == 1) ? true : false);

        }

    }; 

    for (const element of elements) {

        const { name } = element;

        if(!(attributesToShow.includes(name))) {
            continue;
        }

        const { type } = element;
        const valueToShow = dataToShowOnForm[name];

        if(elementsTypes.hasOwnProperty(type)) {

            const { [type]: sendValueToEleemntType } = elementsTypes;

            sendValueToEleemntType(element, valueToShow);
            continue;

        }

        element.value = valueToShow;

    }
    
}

/* function showAlert(alertId, alertType, message, showTime) {

    const genericStyles = { display: 'block;' };

    const types = {

        success: { 
            
            styles: { ...genericStyles },
            classes: [ 'alert-success' ]   
        
        },
        error: {

            styles: { ...genericStyles },
            classes: [ 'alert-danger' ] 

        },
        get toRemove() {

            return [...this.success.classes, ...this.error.classes]
    
        }

    }

    const alert = document.getElementById(alertId);

    const { toRemove: classesToRemove } = types; 

    alert.classList.remove('alert-hidden', ...classesToRemove);

    const alertSettings = types[alertType];

    const { styles, classes } = alertSettings;

    alert.style.cssText = JSON.stringify(styles).replace(/["{},]/g, '');

    alert.classList.add(classes.join(', '));

    alert.innerHTML = message;

    executeAfterATime(() => {
        alert.classList.add('alert-hidden');
        executeAfterATime(() => {
            alert.style.display = 'none';
        }, 500);
    }, 5000);

} */

/**
 * Muestra un mensaje de alerta con formato según el tipo (éxito o error)
 * @param {string} alertId - ID del elemento de alerta en el DOM
 * @param {string} alertType - Tipo de alerta ('success' o 'error')
 * @param {string} message - Mensaje a mostrar en la alerta
 */
function showAlert(alertId, alertType, message) {

    const genericClasses = [ 'alert', 'alert-block', 'fade', 'in', 'alert-dismissible' ];

    const types = {

        success: { 
            
            title: 'Exito',
            classes: [ 'alert-success', ...genericClasses ]   
        
        },
        error: {

            title: 'Error',
            classes: [ 'alert-danger', ...genericClasses ] 

        }

    }

    const alert = document.getElementById(alertId);

    alert.className = '';
    alert.style.display = '';

    const alertSettings = types[alertType];

    const { title, classes } = alertSettings;

    alert.classList.add(...classes);

    const alertMessage = alert.querySelector('#alert-message');

    alertMessage.innerHTML = `${title}<br><br>${message}`;

}

/**
 * Actualiza la etiqueta de un campo de archivo cuando se selecciona un archivo
 * @param {HTMLElement} input - Elemento de entrada de archivo
 */
function updateFileLabel(input) {

    var { id, files } = input;
    
    var imageName = 'Seleccionar imagen...';

    if (files.length > 0) {
        imageName = files[0].name;
    }

    var label = document.getElementById(`label_${id}`);

    label.textContent = imageName;

}

/**
 * Limpia todos los campos de un formulario y reinicia las etiquetas de archivos
 * @param {string} formId - ID del formulario a limpiar
 */
function clearFileds(formId) {

    const form = document.getElementById(formId);

    form.reset();

    form.querySelectorAll('input[type="file"]').forEach(function(input) {
        updateFileLabel(input);
    });

}

/**
 * Cierra un modal haciendo clic en su botón de cierre
 * @param {string} closeIdModalButton - ID del botón para cerrar el modal
 */
function closeModal(closeIdModalButton) {

    const closeButton = document.getElementById(closeIdModalButton);
                
    if (closeButton) {
        closeButton.click();
    }

}

/**
 * Cierra una alerta al hacer clic en su botón de cierre
 * @param {HTMLElement} buttonElement - Botón que cierra la alerta
 */
function closeAlert(buttonElement) {

    var alertElement = buttonElement.parentElement;
    
    alertElement.style.display = 'none';

}

/**
 * Desplaza suavemente la pantalla hacia la parte superior
 */
function scrollToTop() {

    window.scroll({
        top: 0,
        left: 0,
        behavior: 'smooth'
    });

}

/*-------------------------------------------------------------*/
/*-----------------------[END DOM ACTIONS]---------------------*/
/*-------------------------------------------------------------*/

/*-------------------------------------------------------------*/
/*-----------------------[BUTTONS ACTIONS]---------------------*/
/*-------------------------------------------------------------*/

/**
 * Carga los datos de un registro en el formulario de edición
 * @param {HTMLElement} element - Elemento que contiene los datos del registro
 */
function editData(element) {

    const formId = 'update-record';

    const recorData = JSON.parse(element.getAttribute('data-additional'));

    const { id, desktop_image, mobile_image, desktop_image_name, mobile_image_name, ...dataToShowOnForm } = recorData;
    
    recordId = id;

    clearFileds(formId);

    loadRecordDataOnForm(formId, dataToShowOnForm);

    const desktopImagenNameLabel = document.getElementById('id_desktop_image_name');

    const mobileImagenNameLabel = document.getElementById('id_mobile_image_name');

    desktopImagenNameLabel.textContent = desktop_image_name;

    mobileImagenNameLabel.textContent = mobile_image_name;

}

/**
 * Almacena los datos del registro que se va a eliminar
 * @param {HTMLElement} element - Elemento que contiene los datos del registro
 */
function selectedRecordToDelete(element) {

    recordToDelete = JSON.parse(element.getAttribute('data-additional'));

}

/**
 * Mueve una fila hacia arriba en la tabla (reordenamiento)
 * @param {HTMLElement} button - Botón que desencadena la acción
 */
function moveRowUp(button) {
    var row = button.closest('tr');
    var prevRow = row.previousElementSibling;
    if (prevRow && prevRow.rowIndex !== 0) {
        row.parentNode.insertBefore(row, prevRow);
        updateRowIndexes();
    }
}

/**
 * Mueve una fila hacia abajo en la tabla (reordenamiento)
 * @param {HTMLElement} button - Botón que desencadena la acción
 */
function moveRowDown(button) {
    var row = button.closest('tr');
    var nextRow = row.nextElementSibling;
    if (nextRow) {
        row.parentNode.insertBefore(nextRow, row);
        updateRowIndexes();
    }
}

/**
 * Actualiza los índices de las filas después de reordenarlas
 */
function updateRowIndexes() {
    var table = document.querySelector('table');
    var rows = table.querySelectorAll('tr');
    rows.forEach((row, index) => {
        if (index > 0) {
            row.cells[0].innerText = index;
        }
    });
}

/*-------------------------------------------------------------*/
/*---------------------[END BUTTONS ACTIONS]-------------------*/
/*-------------------------------------------------------------*/

/*-------------------------------------------------------------*/
/*----------------------------[UTILS]--------------------------*/
/*-------------------------------------------------------------*/

/**
 * Elimina propiedades específicas de un objeto
 * @param {object} obj - Objeto del que se eliminarán valores
 * @param {array} valuesToDelete - Array con nombres de propiedades a eliminar
 * @return {object} Objeto sin las propiedades eliminadas
 */
function deleteValuesFromObject(obj, valuesToDelete) {

    const keys = Object.keys(obj);

    const objectWithOutUndesiredValues = keys.reduce((acc, key) => {
    
        if(!valuesToDelete.includes(key)) {
            
            acc[key] = obj[key];
            
        }
        
        return acc;
        
    }, {});

    return objectWithOutUndesiredValues;

}

/**
 * Convierte un objeto a formato de lista HTML para mostrar en alertas
 * @param {object} obj - Objeto a convertir
 * @return {string} Representación HTML del objeto
 */
function objectTolist(obj) {
    
    const changeFor = {
        
        '"': '',
        '{': '',
        '}': '',
        ':': ': ',
        ',': '<br>'
        
    };
    
    obj = JSON.stringify(obj);

    obj = obj.replace(/["{},:]/g, (match) => {
        
        return changeFor[match];
        
    });
    
    return obj;
    
}

/**
 * Ejecuta una función después de un tiempo determinado
 * @param {function} callback - Función a ejecutar
 * @param {number} executionTime - Tiempo en milisegundos
 */
function executeAfterATime(callback, executionTime) {

    setTimeout(callback, executionTime );

}

/**
 * Genera parámetros de solicitud para peticiones AJAX
 * @param {object} data - Datos a incluir en la solicitud
 * @return {FormData} Objeto FormData con los parámetros
 */
function generateRequestParams(data) {

    var formData = new FormData();

    for (const key in data) {
        
        formData.append(key, data[key]);
    
    }

    return  formData;

}

/*-------------------------------------------------------------*/
/*--------------------------[END UTILS]------------------------*/
/*-------------------------------------------------------------*/

/*-------------------------------------------------------------*/
/*-------------------------[VALIDATIONS]-----------------------*/
/*-------------------------------------------------------------*/

/**
 * Objeto que define las reglas de validación para cada campo del formulario
 * Incluye longitud máxima, expresiones regulares y funciones de validación
 */
const validationRulesObject = {
    
    name: {
        
        typeRules: {

            maxLength: 50,
            allowedRegex: /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑàèìòùÀÈÌÒÙâêîôûÂÊÎÔÛäëïöüÄËÏÖÜãõÃÕçÇ.,:;_\-–—'"""''`´^¨¿?¡!@#$%&*()+=[\]|°{}/\r\n ]*$/,
            empty: true

        },
        validationFunctions: [validateTypeString],
        fieldType: 'string',
        required: true
        
    },
    description: {
        
        typeRules: {

            maxLength: 200,
            allowedRegex: /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑàèìòùÀÈÌÒÙâêîôûÂÊÎÔÛäëïöüÄËÏÖÜãõÃÕçÇ.,:;_\-–—'"""''`´^¨¿?¡!@#$%&*()+=[\]|°{}/\r\n ]*$/,           
            type: 'string'

        },
        validationFunctions: [validateTypeString],
        fieldType: 'string',
        required: false

    },
    url: {
        
        typeRules: {
            maxLength: 200
        },
        validationFunctions: [validateTypeString, validateUrl],
        fieldType: 'string',
        required: false
        
    }
    
}

/**
 * Valida los datos del formulario según las reglas definidas
 * @param {object} formData - Datos del formulario a validar
 * @return {object} Objeto con errores encontrados (vacío si no hay errores)
 */
function dataValidation(formData) {

    const spanishFieldName = {

        name: 'Nombre',
        description: 'Descripción',
        image: 'Imagenes',
        url: 'Url'

    } 

    const keys = Object.keys(formData);
    const valuesToNeedValidation = Object.keys(validationRulesObject);

    const validationResult = keys.reduce((acc, key) => {
    
        if(!(valuesToNeedValidation.includes(key))) {
            return acc;
        }

        const validationsRules = validationRulesObject[key]; 
        const valueToCheck = formData[key];
        
        const { validationFunctions } = validationsRules;

        for (let fnc of validationFunctions) {
            
            let errorMessage;
            const functionName = fnc.name;
            const functionParams = [valueToCheck];

            if(functionName.includes('Type')) {

                const { typeRules } = validationsRules;
                
                functionParams.push(typeRules);
            }

            errorMessage = fnc(...functionParams);

            if(errorMessage) {

                key = spanishFieldName[key] || key;
                acc[key] = errorMessage;
                break;

            }

        }

        return acc;
        
    }, {});

    return validationResult;

}

/**
 * Valida que una URL comience con https://
 * @param {string} url - URL a validar
 * @return {string|undefined} Mensaje de error o undefined si es válida
 */
function validateUrl(url) {
    
    if(url === '') {
        return undefined;
    }

    // Si la URL no comienza con http:// o https://, añadir https://
    if(!url.startsWith('http://') && !url.startsWith('https://')) {
        // Si comienza con www., añadir solo https://
        if(url.startsWith('www.')) {
            url = 'https://' + url;
        } else {
            // Si es solo un dominio, añadir https://
            url = 'https://' + url;
        }
    }
    
    // const isHttpsPrefix = url.substring(0, 8);
    
    // if(!(isHttpsPrefix === 'https://')) {
    //     return 'La url no es segura.'
    // }
    
    return undefined;

}

/**
 * Valida campos de texto según reglas específicas:
 * - Campos vacíos
 * - Longitud máxima
 * - Caracteres permitidos por expresión regular
 * @param {string} data - Texto a validar
 * @param {object} validationRules - Reglas de validación
 * @return {string|undefined} Mensaje de error o undefined si es válido
 */
function validateTypeString(data, validationRules) {

    const { empty, maxLength, allowedRegex } = validationRules;

    if((empty) && (data === '')) {
        return `El campo no puede estar vacio.`;
    }

    if((maxLength) && (data.length > maxLength)) {
        return `Se superó el numero maximo de caracteres permitidos: ${maxLength}`
    }

    if((allowedRegex) && (!(data.match(allowedRegex)))) {
        return 'Se incluyeron caracteres no permitidos.'; 
    }

    return undefined;

}

/**
 * Comprueba si hay errores de validación y genera un mensaje adecuado
 * @param {object} validationResults - Resultados de la validación
 * @return {string|undefined} Mensaje de error o undefined si no hay errores
 */
function areThereErrors(validationResults) {

    const quantityOfErrors = Object.keys(validationResults).length;

    if(quantityOfErrors > 0) {

        const pluralOrSingular = (quantityOfErrors > 1) ? 'Se presentaron errores en los campos:' : 'Se presento un error en el campo:';

        return `${pluralOrSingular} <br><br> ${objectTolist(validationResults)}`;

    }

    return undefined;

}

/*-------------------------------------------------------------*/
/*-----------------------[END VALIDATIONS]---------------------*/
/*-------------------------------------------------------------*/

/*-------------------------------------------------------------*/
/*----------------------------[AJAX]---------------------------*/
/*-------------------------------------------------------------*/

/**
 * Inserta un nuevo registro en la base de datos a través de AJAX
 * Valida los datos del formulario antes de enviarlos
 * @param {string} formId - ID del formulario de inserción
 */
function insertRecord(formId) {
    // Deshabilitar confirmación de navegación
    window.onbeforeunload = null;
    
    // Establecer una bandera para prevenir advertencias de navegación
    window.__allowNavigation = true;

    const valuesToDeleteFromFormData = ['_qf__InsertForm'];

    let formData = getFormData(formId);

    formData = deleteValuesFromObject(formData, valuesToDeleteFromFormData);

    const validationResults = dataValidation(formData);

    const errorMessage = areThereErrors(validationResults); 

    if(errorMessage) {
        showAlert('create-form-alert', 'error', errorMessage);
        scrollToTop();
        return null;
    }

    const requestParams = generateRequestParams(formData);

    // Almacenar en variable global la información de los roles seleccionados en formato JSON.
    if (formData['notifyroles']) {
        const roleIds = formData['notifyroles'].split(',').filter(Boolean);
        window.sliderNotifyRolesJson = JSON.stringify({ roles: roleIds });
    } else {
        window.sliderNotifyRolesJson = null;
    }

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        
        if (this.readyState == 4) {

            let responseObj;
            try {
                responseObj = JSON.parse(this.responseText);
            } catch(parseErr) {
                console.error('Respuesta no es JSON:', this.responseText);
                return;
            }
            const { success, error } = responseObj;
            
            const status = (this.status == 200);

            console.log(success);

            if (status) {
                showAlert('create-form-alert', 'success', success);
                scrollToTop();
                clearFileds(formId);
                
                // Redireccionar a manage_images.php después de 2 segundos
                setTimeout(function() {
                    window.location.href = 'manage_images.php';
                }, 1500);
                
            } else {
                showAlert('create-form-alert', 'error', error);
                scrollToTop();
                clearFileds(formId);
            }
        }
    };

    xhttp.open("POST", "insertRecord.php", true);
    xhttp.send(requestParams);
}

/**
 * Actualiza un registro existente en la base de datos a través de AJAX
 * Valida los datos del formulario antes de enviarlos
 */
function updateRecord() {
    // Deshabilitar confirmación de navegación
    window.onbeforeunload = null;
    
    // Establecer una bandera para prevenir advertencias de navegación
    window.__allowNavigation = true;

    const valuesToDeleteFromFormData = ['_qf__UpdateForm'];

    let formData = getFormData('update-record');

    formData = deleteValuesFromObject(formData, valuesToDeleteFromFormData);

    const validationResults = dataValidation(formData);

    const errorMessage = areThereErrors(validationResults); 

    if(errorMessage) {
        showAlert('table-alert', 'error', errorMessage);
        closeModal('closeEditModal');
        clearFileds('update-record');
        return null;
    }

    formData = { id: recordId, ...formData };

    const requestParams = generateRequestParams(formData);

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        
        if (this.readyState == 4) {
            
            const responseObj = JSON.parse(this.responseText);
            const { success, error } = responseObj;

            closeModal('closeEditModal');
            clearFileds('update-record');

            if (this.status == 200) {
    
                showAlert('table-alert', 'success', success);
                
                executeAfterATime(() => {
                    // Asegurarse de que no haya alertas
                    window.onbeforeunload = null;
                    window.__allowNavigation = true;
                    
                    // Recargar página
                    window.location.href = 'manage_images.php?updated=1';
                }, 500);

            } else {

                showAlert('table-alert', 'error', error);
            
            }

        }
    };

    xhttp.open("POST", "updateRecord.php", true);
    xhttp.send(requestParams);

}

/**
 * Elimina un registro de la base de datos a través de AJAX
 * Utiliza la variable global recordToDelete para identificar el registro
 */
function deleteRecord() {
    // Deshabilitar confirmación de navegación
    window.onbeforeunload = null;
    
    // Establecer una bandera para prevenir advertencias de navegación
    window.__allowNavigation = true;

    recordToDelete.sesskey = M.cfg.sesskey;
    const requestParams = JSON.stringify(recordToDelete);

    console.log(recordToDelete);

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        
        if (this.readyState == 4) {
                    
            const responseObj = JSON.parse(this.responseText);
            const { success, error } = responseObj;

            closeModal('closeAlertModal');

            if (this.status == 200) {

                showAlert('table-alert', 'success', success);
                executeAfterATime(() => {
                    // Asegurarse de que no haya alertas
                    window.onbeforeunload = null;
                    window.__allowNavigation = true;
                    
                    // Recargar página
                    window.location.href = 'manage_images.php?deleted=1';
                }, 500);

            } else {

                showAlert('update-form-alert', 'error', error);
            
            }

        }

    };

    xhttp.open("DELETE", "deleteRecord.php", true);
    xhttp.setRequestHeader("Content-Type", "application/json");
    xhttp.send(requestParams);

}

/**
 * Guarda el orden de visualización de las imágenes en el Banner de plataforma
 * Recoge los índices actualizados de la tabla y los envía al servidor
 */
function saveOrder() {
    // Deshabilitar confirmación de navegación
    window.onbeforeunload = null;
    
    // Establecer una bandera para prevenir advertencias de navegación
    window.__allowNavigation = true;

    const table = document.getElementById('display-records');

    // Obtener el valor del filtro actual
    const stateOption = document.getElementById('state_option').value;

    let rows = Array.from(table.rows);

    rows.shift();

    const recordsToUpdate = rows.reduce((acc, row) => {

        const { dataset: { id } } = row;
        let { 0: order_display } = row.cells;
        order_display = order_display.textContent.trim();

        acc.push(JSON.stringify({ id, order_display }));

        return acc;

    }, []);

    const requestParams = JSON.stringify({

        recordsToUpdate,
        sesskey: M.cfg.sesskey

    });

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
     
        if (this.readyState == 4) {
                
            const responseObj = JSON.parse(this.responseText);
            const { success, error } = responseObj;

            if (this.status == 200) {

                showAlert('table-alert', 'success', success);
                // En caso de éxito, esperar un momento y recargar sin alertas
                executeAfterATime(() => {
                    // Asegurarse de que no haya alertas
                    window.onbeforeunload = null;
                    window.__allowNavigation = true;
                    
                    // Redirigir a la vista paginada después de guardar
                    window.location.href = 'show_order.php?state_option=' + stateOption;
                }, 500);

            } else {

                showAlert('table-alert', 'error', error);
                executeAfterATime(() => {
                    // Asegurarse de que no haya alertas
                    window.onbeforeunload = null;
                    window.__allowNavigation = true;
                    
                    // Recargar página y conservar el filtro
                    window.location.href = 'show_order.php?state_option=' + stateOption;
                }, 500);
            
            }
        
        }

    };

    xhttp.open("PUT", "order.php", true);
    xhttp.setRequestHeader("Content-Type", "application/json");
    xhttp.send(requestParams);

}

/*-------------------------------------------------------------*/
/*--------------------------[END AJAX]-------------------------*/
/*-------------------------------------------------------------*/

// Añade listeners a todos los campos de archivo para actualizar las etiquetas cuando cambian
document.querySelectorAll('input[type="file"]').forEach(function(input) {

    input.addEventListener('change', function() {
        updateFileLabel(this);
    });

});