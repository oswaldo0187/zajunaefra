<?php
/**
 * Funciones de utilidad para el formulario del slider
 * 
 * Este archivo contiene una colección de funciones auxiliares utilizadas
 * por el formulario de gestión del slider para realizar diversas tareas
 * como búsqueda, manipulación de datos, caché, etc.
 * 
 * @package     local_slider_form
 */

    /**
     * Busca registros repetidos en la base de datos
     * 
     * Permite buscar registros duplicados según los parámetros proporcionados,
     * adaptando la consulta según si es para crear o actualizar.
     * 
     * @param string $endpoint Acción a realizar ('create' o 'update')
     * @param array $queryParams Parámetros de consulta para la búsqueda
     * @return array Registros encontrados que coinciden con los criterios
     */
    function searchRepeatedRecords($endpoint, $queryParams) {
        
        global $DB;
    
        $actionAcordingToEndpoint = [

            'create' => function($params) use ($DB) {

                $sql = "SELECT name, desktop_image_name, mobile_image_name FROM {local_slider} WHERE name = :name OR desktop_image_name = :desktop_image_name OR mobile_image_name = :mobile_image_name"; 

                $records = $DB->get_records_sql($sql, $params);
                
                return $records;

            },
            'update' => function($params) use ($DB) {

                $id = array_shift($params);

                $sql = "SELECT name, desktop_image_name, mobile_image_name FROM {local_slider} WHERE ".generateQueryConditions($params, 'OR')." AND id != :id";        
                
                $params = [ 'id' => $id, ...$params ];

                $records = $DB->get_records_sql($sql, $params);
                
                return $records;

            }

        ];
        
        $actionToDo = $actionAcordingToEndpoint[$endpoint];
        $records = $actionToDo($queryParams);
    
        return $records;

    }

    /**
     * Genera condiciones de consulta SQL a partir de un array de valores
     * 
     * @param array $values Valores para generar condiciones
     * @param string $logicalOperator Operador lógico a usar ('AND' u 'OR')
     * @return string Condiciones SQL generadas
     */
    function generateQueryConditions($values, $logicalOperator) {
        
        $keys = array_keys($values);
        
        $queryConditions = array_map(function($key) {
            return "$key = :$key";
        }, $keys);
        
        $queryConditions = implode(" $logicalOperator ", $queryConditions);
        
        if ($logicalOperator === 'OR') {
            $queryConditions = "($queryConditions)";
        }
        
        return $queryConditions;

    }

    /**
     * Traduce nombres de atributos a español para mostrar mensajes amigables
     * 
     * @param string $attribute Nombre del atributo a traducir
     * @return string Nombre del atributo traducido al español
     */
    function spanishAttributesNames($attribute) {

        $spanishValues = [

            'name' => 'Nombre',
            'desktop_image_name' => 'Imagen de escritorio',
            'mobile_image_name' => 'Imagen para dispositivos moviles'


        ];

        $value = $spanishValues[$attribute];

        return (isset($value) ? $value : $attribute);

    }

    /**
     * Verifica que no existan registros repetidos y lanza una excepción si los hay
     * 
     * @param array $records Registros encontrados en la búsqueda
     * @param array $inputData Datos de entrada a verificar
     * @throws Exception Si se encuentran registros repetidos
     */
    function checkThereAreNotRepeatedRecords($records, $inputData) {

        $spanishValues = [

            'name' => 'Nombre',
            'desktop_image_name' => 'Imagen de escritorio',
            'mobile_image_name' => 'Imagen dispositivos mobiles'

        ];
        
        $quantityOfFoundedRecords = count($records);

        if($quantityOfFoundedRecords === 0) {
            return;
        }

        $recordsValues = array_reduce($records, function($carry, $record) {
            $record = (array) $record;
            return array_merge($carry, array_values($record));
        }, []);

        $existingValues = array_reduce(array_keys($inputData), function($acc, $key) use ($inputData, $recordsValues, $spanishValues) {
            
            $value = $inputData[$key];

            if (in_array($value, $recordsValues)) {
                $spanishKey = $spanishValues[$key];
                $acc[$spanishKey] = $value;
            }

            return $acc;

        }, []);

        $existingValues = arrayKeyToStringAsList($existingValues);

        $errorMessage = ($quantityOfFoundedRecords === 1) ? 'Ya existe un registro con el dato proporcionado en el campo:<br><br>'.$existingValues : 'Ya existen registros con los datos proporcionados en los campos:<br><br>'.$existingValues;

        throw new Exception($errorMessage, 409);

    }

    /**
     * Genera un array con los nombres de las imágenes
     * 
     * @param array $images Array con información de las imágenes
     * @return array Array asociativo con los nombres de las imágenes
     */
    function generateImagesNames($images) {
        
        $keys = array_keys($images);

        $result = array_map(function($key) use ($images) {
            return [$key . "_name" => $images[$key]["name"]];
        }, $keys);

        $names = array_merge(...$result);

        return $names;

    }

    /**
     * Genera valores para las imágenes organizándolas en un formato específico
     * 
     * @param array $images Array con información de las imágenes
     * @return array Array estructurado con valores, nombres y claves de las imágenes
     */
    function generateImageValues($images) {

        $images = array_reduce(array_keys($images), function($acc, $key) use ($images) {
            
            $obj = $images[$key];
            
            $acc['values'][] = $obj;
            $acc['names'][$key . '_name'] = $obj['name'];
            $acc['keys'][] = $key;

            return $acc;
            
        }, ['names' => [], 'values' => [], 'keys' => []]);

        return $images;

    }

    /**
     * Elimina valores específicos de un array
     * 
     * @param array $array Array original
     * @param array $keysToDelete Claves a eliminar del array
     * @return array Array resultante sin las claves eliminadas
     */
    function deleteValuesFromArray($array, $keysToDelete) {

        return array_diff_key($array, array_flip($keysToDelete));

    }

    /**
     * Elimina el caché del slider para forzar la recarga de imágenes
     */
    function deleteSliderCache() {

        $cache = cache::make('local_slider', 'imagecache');
        $cache->delete('images');
  
    }

    /**
     * Obtiene la fecha y hora actual en formato Y-m-d H:i:s
     * 
     * @return string Fecha y hora actual formateada
     */
    function getCurrentDate() {
        
        $dateTime = new DateTime();
    
        $currentDate = $dateTime->format('Y-m-d H:i:s');
    
        return $currentDate;
        
    }

    /**
     * Convierte un array asociativo a una cadena con formato de lista
     * 
     * @param array $arr Array a convertir
     * @return string Cadena formateada como lista
     */
    function arrayKeyToStringAsList($arr) {
    
        $changeFor = [
        
            '"' => '',
            '{' => '',
            '}' => '',
            ':' => ': ',
            ',' => '<br>'
        
        ];
    
        $jsonStr = json_encode($arr, JSON_UNESCAPED_UNICODE);
    
        $result = preg_replace_callback('/["{},:]/', function($matches) use ($changeFor) {
            return $changeFor[$matches[0]];
        }, $jsonStr);
    
        return $result;
    
    }

    function includedOrNotIncludedElements($arr1, $arr2, $action = true) {
        
        $actions = [
        
            'included' => function($element) use ($arr2) {
                return in_array($element, $arr2);
            },
            'notIncluded' => function($element) use ($arr2) {
                return !in_array($element, $arr2);
            }

        ];
        
        $callback = ($action) ? $actions['included'] : $actions['notIncluded'];
        $result = array_filter($arr1, $callback);
    
        return $result;
    
    }

    function sortedByAttribute($arrayObject, $attribute) {

        usort($arrayObject, function($a, $b) use ($attribute) {

            if ($a->{$attribute} === null) return 1;
            if ($b->{$attribute} === null) return -1;
            
            return $a->{$attribute} - $b->{$attribute};

        }); 

        return $arrayObject;

    }

    function sortRecordsByOrderDisplay($records) {

        usort($records, function($a, $b) {

            if ($a->orderdisplay === null) return 1;
            if ($b->orderdisplay === null) return -1;
            
            return $a->orderdisplay - $b->orderdisplay;
        
        });

        return $records;

    }

    function redirectTo($url, $queryParams = []) {

        redirect(new moodle_url($url, $queryParams));
    
    }
    
    function createException($massage, $status) {
    
        throw new Exception($massage, $status);
    
    }

    function imagesToBase64($images) {
        
        array_walk($images, function(&$image, $key) {
            
            [ 'tmp_name' => $tmpName ] = $image;

            $content = file_get_contents($tmpName);

            $image = base64_encode($content);

        });

        return $images;

    }

    function throwExceptionIfNotEmpty($array, $exceptionMessages, $exceptionStatus = 500) {

        $quantityOfErrors = count($array);

        if($quantityOfErrors > 0) {

            [ 'singular' => $singular, 'plural' => $plural ] = $exceptionMessages;

            $message = ($quantityOfErrors === 1) ? $singular : $plural;

            throw new Exception($message, $exceptionStatus);
        
        }

    }