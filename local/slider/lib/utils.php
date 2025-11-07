<?php
/**
 * Funciones de utilidad para el plugin de slider
 * 
 * Este archivo contiene funciones auxiliares que pueden ser utilizadas
 * por otras partes del plugin de slider.
 * 
 * @package     local_slider
 */

    /**
     * Ordena un array de objetos por un atributo específico
     * 
     * Esta función toma un array de objetos y lo ordena según el valor
     * de un atributo especificado. Los elementos con atributo nulo
     * se colocan al final del array.
     * 
     * @param array $arrayObject Array de objetos a ordenar
     * @param string $attribute Nombre del atributo por el cual ordenar
     * @return array Array ordenado según el atributo especificado
     */
    function sortedByAttribute($arrayObject, $attribute) {

        usort($arrayObject, function($a, $b) use ($attribute) {

            if ($a->{$attribute} === null) return 1;
            if ($b->{$attribute} === null) return -1;
            
            return $a->{$attribute} - $b->{$attribute};

        }); 

        return $arrayObject;

    }