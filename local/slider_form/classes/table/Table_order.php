<?php

// Incluye funciones auxiliares para manipulación de arrays y utilidades generales del slider
require_once(__DIR__ . '../../../lib/utilsFunctions.php');

// Asegura que el archivo solo se ejecute dentro del contexto de Moodle
defined('MOODLE_INTERNAL') || die();

/**
 * Clase TableManager para la vista de Orden de Despliegue
 *
 * Esta clase se encarga de renderizar la tabla de imágenes para el orden de despliegue,
 * mostrando únicamente los botones de "Subir" y "Bajar" en modo orden.
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
     * Genera los botones de arrastrar y soltar (Subir/Bajar) para el orden
     * @return string HTML de los botones
     */
    public function generateDragAndDropButtons() {
        return '
            <a href="#" class="btn btn-secondary" onclick="moveRowUp(this)">Subir</a>
            <a href="#" class="btn btn-secondary" onclick="moveRowDown(this)">Bajar</a>
        ';
    }

    /**
     * Renderiza la tabla de imágenes para el orden de despliegue
     * @param array $records Registros a mostrar en la tabla
     * @param int $rowsIndex Índice inicial para la numeración de la columna Orden
     * @param bool $order Indica si se deben mostrar los botones de orden (modo orden)
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
        // Renderiza los encabezados de columna, ocultando "Acción" si no es modo orden
        foreach ($this->columns as $column) {
            // Ocultar columna Acción si no está en modo orden
            if ($column === 'Acción' && !$order) continue;
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
            // Genera los botones de orden
            $buttons = $this->generateDragAndDropButtons();
            // Renderiza la fila
            $html .= '<tr data-id="' . htmlspecialchars($id) . '">';
            $html .= '<td>' . htmlspecialchars($index) . '</td>';
            $html .= '<td><img class="d-block" style="width: 200px; height: 70px;" src="data:image/jpeg;base64,' . htmlspecialchars($image) . '" alt=""></td>';
            $html .= '<td>' . htmlspecialchars($name) . '</td>';
            $html .= '<td><span class="' . $stateClass . '">' . htmlspecialchars($stateText) . '</span></td>';
            // Mostrar columna Acción solo en modo orden
            if ($order) {
                $html .= '<td><div class="d-flex flex-row" style="column-gap: .6rem">' . $buttons . '</div></td>';
            }
            $html .= '</tr>';
            $index++;
        }
        $html .= '</tbody></table>';
        return $html;
    }
} 