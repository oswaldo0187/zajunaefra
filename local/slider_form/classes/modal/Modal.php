<?php

defined('MOODLE_INTERNAL') || die();

class Modal {

    private $id;
    private $title;
    private $footerButtons;

    public function __construct($id, $title, $footerButtons = []) {
        $this->id = $id;
        $this->title = $title;
        $this->footerButtons = $footerButtons;
    }
    
    public function render($bodyContent) {
        $footerHtml = '';

        if (count($this->footerButtons) > 0) {
            foreach ($this->footerButtons as $button) {
                $footerHtml .= '<button type="button"' . ($button['id'] ?? '') . 'class="btn ' . $button['class'] . '" ' . ($button['attributes'] ?? '') . '>' . $button['label'] . '</button>';
            }
        }
        
        return '

            <div class="modal fade" id="' . $this->id . '" tabindex="-1" role="dialog" aria-labelledby="' . $this->id . 'Label" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #04324d;">
                            <h5 class="modal-title" id="' . $this->id . 'Label" style="color: white;">' . $this->title . '</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true" style="color: white;">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">' . $bodyContent . '</div>
                        <div class="modal-footer">' . $footerHtml . '</div>
                    </div>
                </div>
            </div>

        ';
    }
}