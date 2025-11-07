<?php

require_once(__DIR__ . '../../../lib/utilsFunctions.php');

defined('MOODLE_INTERNAL') || die();

class TableManager {

    private $columns;
    private $values_to_delete;

    public function __construct($columns) {

        $this->columns = $columns;
        $this->values_to_delete = ['image', 'userid', 'orderdisplay', 'createdat', 'updatedat'];
    
    }

    public function generateEditDeleteButtons($record) {

        $editDataAditionalValues = json_encode($record);
        $deleteDataAditionalValues = json_encode(deleteValuesFromArray($record, ['name', 'imagename', 'description', 'url']));

        return '
            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#editModal" data-additional="' . htmlspecialchars($editDataAditionalValues) . '" onclick="editData(this)">Editar</a>
            <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#alertModal" data-additional="' . htmlspecialchars($deleteDataAditionalValues) . '" onclick="selectedRecordToDelete(this)">Eliminar</a>
        ';

    }

    public function generateDragAndDropButtons() {

        return '
            <a href="#" class="btn btn-secondary" onclick="moveRowUp(this)">Subir</a>
            <a href="#" class="btn btn-secondary" onclick="moveRowDown(this)">Bajar</a>
        ';

    }

    public function renderTable($records, $rowsIndex, $order = false) {
        
        $html = '
        
            <div class="" role="alert" id="table-alert" style="display: none;">
                <span id="alert-message"></span>
                <button type="button" class="close" onclick="closeAlert(this)">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Descartar esta notificación</span>
                </button>
            </div>
        
        ';

        $html .= '<table class="generaltable boxaligncenter" id="display-records">';
        $html .= '<thead><tr>';

        foreach ($this->columns as $column) {
            $html .= '<th>' . htmlspecialchars($column) . '</th>';
        }

        $html .= '</tr></thead>';
        $html .= '<tbody>';
        
        $index = $rowsIndex;

        foreach ($records as $record) {

            $record = (array) $record;

                   
            // Determinar el texto de visualización según el estado
            $stateText = ($state == 1) ? 'Activo/Mostrado en Banner Plataforma' : 'Oculto/Inactivo';
        
            $stateClass = ($state == 1) ? 'text-success' : 'text-danger';

            $record = deleteValuesFromArray($record, $this->values_to_delete);
            $buttons = ($order == false) ? $this->generateEditDeleteButtons($record) : $this->generateDragAndDropButtons();

            $html .= '<tr data-id="' . htmlspecialchars($id) . '">';
            $html .= '<td>' . htmlspecialchars($index) . '</td>';
            $html .= '<td><img class="d-block" style="width: 200px; height: 70px;" src="data:image/jpeg;base64,' . htmlspecialchars($image) . '" alt=""></td>';
            $html .= '<td>' . htmlspecialchars($name) . '</td>';
            // Agregar la columna de visualización con color según el estado
            $html .= '<td><span class="' . $stateClass . '">' . htmlspecialchars($stateText) . '</span></td>';
            
            // Mostrar columna Acción solo en modo orden
            if (in_array('Accion', $this->columns) || in_array('Acción', $this->columns)) {
                $html .= '<td><div class="d-flex flex-row" style="column-gap: .6rem">' . $buttons . '</div></td>';
            }
            $html .= '</tr>';

        }
        
        $html .= '</tbody></table>';
        return $html;

    }
}