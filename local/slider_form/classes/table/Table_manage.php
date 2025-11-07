<?php

// Incluye funciones auxiliares para manipulación de arrays y utilidades generales del slider
require_once(__DIR__ . '../../../lib/utilsFunctions.php');

// Asegura que el archivo solo se ejecute dentro del contexto de Moodle
defined('MOODLE_INTERNAL') || die();

/**
 * Clase TableManager para la vista de Gestión de Imágenes
 *
 * Esta clase se encarga de renderizar la tabla de imágenes para la gestión,
 * mostrando siempre los botones de "Editar" y "Eliminar".
 */
class TableManager {

    private $columns; // Columnas a mostrar en la tabla
    private $values_to_delete; // Valores a eliminar del registro antes de mostrar

    /**
     * Constructor de la clase
     * @param array $columns Columnas a mostrar en la tabla
     */
    public function __construct($columns) {
        $this->columns = $columns;
        $this->values_to_delete = ['image', 'userid', 'orderdisplay', 'createdat', 'updatedat'];
    }

    /**
     * Genera los botones de Editar y Eliminar para cada registro
     * @param array $record Registro de la imagen
     * @return string HTML de los botones
     */
    public function generateEditDeleteButtons($record) {
        $editDataAditionalValues = json_encode($record);
        $deleteDataAditionalValues = json_encode(deleteValuesFromArray($record, ['name', 'imagename', 'description', 'url']));
        return '
            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#editModal" data-additional="' . htmlspecialchars($editDataAditionalValues) . '" onclick="editData(this)">Editar</a>
            <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#alertModal" data-additional="' . htmlspecialchars($deleteDataAditionalValues) . '" onclick="selectedRecordToDelete(this)">Eliminar</a>
        ';
    }

    /**
     * Renderiza la tabla de imágenes para la gestión
     * @param array $records Registros a mostrar en la tabla
     * @param int $rowsIndex Índice inicial para la numeración de la columna Id
     * @param bool $order (no se usa en esta vista, siempre false)
     * @return string HTML de la tabla renderizada
     */
    public function renderTable($records, $rowsIndex, $order = false) {
        // Alerta para mostrar mensajes en la tabla
        $html = '
        <div class="" role="alert" id="table-alert" style="display: none;">
            <span id="alert-message"></span>
            <button type="button" class="close" onclick="closeAlert(this)">
                <span aria-hidden="true">×</span>
                <span class="sr-only">Descartar esta notificación</span>
            </button>
        </div>
        ';
        // Inicio de la tabla
        $html .= '<table class="generaltable boxaligncenter" id="display-records">';
        $html .= '<thead><tr>';
        // Renderiza los encabezados de columna
        foreach ($this->columns as $column) {
            $html .= '<th>' . htmlspecialchars($column) . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        $index = $rowsIndex;



        // Renderiza cada fila de la tabla
        foreach ($records as $record) {
            $record = (array) $record;
            // Extrae los valores principales del registro
            ['desktop_image' => $image, 'name' => $name, 'id' => $id, 'state' => $state] = $record;
            // Determina el texto y color de visualización según el estado
            $stateText = ($state == 1) ? 'Activo/Mostrado en Banner Plataforma' : 'Oculto/Inactivo';
            $stateClass = ($state == 1) ? 'text-success' : 'text-danger';

 

            // Elimina valores no deseados del registro
            $record = deleteValuesFromArray($record, $this->values_to_delete);
            // Genera los botones de Editar y Eliminar
            $buttons = $this->generateEditDeleteButtons($record);
            // Renderiza la fila
            $html .= '<tr data-id="' . htmlspecialchars($id) . '">';
            $html .= '<td>' . htmlspecialchars($index) . '</td>';
            $html .= '<td><img class="d-block" style="width: 200px; height: 70px;" src="data:image/jpeg;base64,' . htmlspecialchars($image) . '" alt=""></td>';
            $html .= '<td>' . htmlspecialchars($name) . '</td>';
            $html .= '<td><span class="' . $stateClass . '">' . htmlspecialchars($stateText) . '</span></td>';
            


            // Columna de acción (editar/eliminar)
            $html .= '<td><div class="d-flex flex-row" style="column-gap: .6rem">' . $buttons . '</div></td>';
            $html .= '</tr>';
            $index++;
        }

        $html .= '</tbody></table>';
        return $html;
    }
} 