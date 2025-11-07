<?php
/**
 * Funciones de validación para los campos del formulario del slider
 * 
 * Este archivo contiene funciones que validan las entradas del usuario 
 * relacionadas con las imágenes y otros campos del formulario del slider.
 * 
 * @package     local_slider_form
 */

    require_once(__DIR__ . '/utilsFunctions.php');

    /**
     * Verifica que no se haya seleccionado la misma imagen para dispositivos de escritorio y móviles
     * 
     * @param array $imagesNames Array asociativo con los nombres de las imágenes
     * @throws Exception Si se detecta que se usó la misma imagen para ambos dispositivos
     */
    function checkImagesNotBeTheSame($imagesNames) {

        $names = array_values($imagesNames);

        $uniqueNames = array_unique($names);

        if(!(count($names) === count($uniqueNames))) {
            throw new Exception('Se incluyo la misma imagen para ambos dispositivos.', 400);
        }

    }

    /**
     * Verifica que se hayan proporcionado las imágenes requeridas
     * 
     * @param array $images Array con las imágenes proporcionadas
     * @param array $requieredImages Array con los nombres de las imágenes requeridas
     * @throws Exception Si faltan imágenes requeridas
     */
    function checkRequiredImages($images, $requieredImages) {

        $quantityOfImages = count($images);

        if($quantityOfImages === 2) {
            return;
        }

        if($quantityOfImages === 0) {
            throw new Exception('No se incluyo la imagen de escritorio y la imagen para dispositivos moviles.');
        }

        $attachmentImages = array_keys($images);

        $notIncludedImage = includedOrNotIncludedElements($requieredImages, $attachmentImages, false);        

        $notIncludedImage = strtolower(spanishAttributesNames( array_shift($notIncludedImage).'_name' ));

        throw new Exception('No se incluyo la '.$notIncludedImage.'.', 400);

    }

    /**
     * Valida el contenido de las imágenes (formato, tamaño, dimensiones)
     * 
     * @param array $imagesInfo Información de las imágenes a validar
     * @param array $validationResult Resultados acumulados de validación (para recursión)
     * @return array Errores encontrados durante la validación
     */
    function checkImagesContent($imagesInfo, $validationResult = []) {

        if(!(count($imagesInfo) > 0)) {
            return $validationResult;
        }

        $imageInfo = array_shift($imagesInfo);

        [ 'name' => $imageName, 'type' => $imageType, 'size' => $imageSize, 'tmp_name' => $imageTmp ] = $imageInfo;

        $allowedTypes = ['jpeg', 'png', 'webp'];
        $allowedSize = 5242880;
        $allowedWidth = 1920;
        $allowedHeight = 720;

        $imageType = explode('/', $imageType)[1];

        if(!in_array($imageType, $allowedTypes)) {        
            $validationResult[$imageName] = 'El formato: '.$imageType.' no es permitido.';
            return checkImagesContent($imagesInfo, $validationResult);
        }

        if($imageSize > $allowedSize) {
            $validationResult[$imageName] = 'El tamaño de la imagen es superior a 5mb.';
            return checkImagesContent($imagesInfo, $validationResult);

        }

        [ $imageWidth, $imageHeight ] = getimagesize($imageTmp);

        if ((!($imageWidth === $allowedWidth)) || (!($imageHeight  === $allowedHeight))) {
            $validationResult[$imageName] = 'El tamaño de la imagen debe ser igual a: 1920px x 720px.';
            return checkImagesContent($imagesInfo, $validationResult);
        }

        return checkImagesContent($imagesInfo, $validationResult);

    }