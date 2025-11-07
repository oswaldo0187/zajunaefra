<?php

require_once($CFG->libdir . '/formslib.php');

class UpdateForm extends moodleform {

    public function definition() {

        $mform = $this->_form;
        $formId = 'update-record';

        $mform->setAttributes(array(
            'id' => $formId
        ));

        $mform->addElement('text', 'name', 'Actualizar nombre: <span class="bg-danger text-white rounded-circle" style="display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; font-size: 12px;" title="Obligatorio">!</span> ');
        $mform->setType('name', PARAM_TEXT);
        $mform->addHelpButton('name', 'name', 'local_slider_form');

        $mform->addElement('text', 'url', 'Actualizar url:');
        $mform->setType('url', PARAM_TEXT);
        $mform->addHelpButton('url', 'url', 'local_slider_form');
        
        $desktop_input_file = '
                
            <div id="fitem_id_category" class="form-group row  fitem">
                <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                    
                    <label id="id_category_label" class="d-inline word-break " for="id_category">
                        Imagen de escritorio: <br><span class="text-muted">Tamaño Aceptado: 1920 px por 720 px</span><br>
                    </label>
                    
                    <div class="form-label-addon d-flex align-items-center align-self-start">
                    <span class="bg-danger text-white rounded-circle" style="display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; font-size: 12px;" title="Obligatorio">!</span>    
                    <a class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;><p>Imagen que se mostrará en dispositivos de escritorio. Recuerde que la resolución de la imagen debe ser de 1920px por 720px. </p></div>" data-html="true" tabindex="0" data-trigger="focus" aria-label="Ayuda sobre Imagen de escritorio">
                            <i class="icon fa fa-question-circle text-info fa-fw " title="Ayuda sobre Imagen de escritorio" role="img" aria-label="Ayuda sobre Imagen de escritorio"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-9 form-inline align-items-start felement" data-fieldtype="select">
                    <label for="desktop_image" id="label_desktop_image" class="btn btn-secondary fp-btn-choose">Seleccionar imagen...</label>
                    <input type="file" name="desktop_image" id="desktop_image" accept="image/*" style="display: none;">
                </div>
            </div>

        ';

        $mform->addElement('html', $desktop_input_file);

        $desktop_image_name_label = '
                
            <div id="fitem_id_category" class="form-group row  fitem">
                <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                    <label id="id_category_label" class="d-inline word-break " for="id_category">
                        Imagen en uso:
                    </label>
                    <div class="form-label-addon d-flex align-items-center align-self-start">
                        <a class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;><p>Nombre de la imagen actualmente en uso para dispositivos de escritorio.</p></div>" data-html="true" tabindex="0" data-trigger="focus" aria-label="Ayuda sobre Imagen en uso">
                            <i class="icon fa fa-question-circle text-info fa-fw " title="Ayuda sobre Imagen en uso" role="img" aria-label="Ayuda sobre Imagen en uso"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-9 form-inline align-items-start felement" data-fieldtype="select">
                    <label class="align-self-center" name="desktop_image_name" id="id_desktop_image_name"></label>
                </div>
            </div>

        ';

        $mform->addElement('html', $desktop_image_name_label);

        $mobile_input_file = '
                
            <div id="fitem_id_category" class="form-group row  fitem">
                <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                    
                    <label id="id_category_label" class="d-inline word-break " for="id_category">
                        Imagen dispositivos móviles: <br><span class="text-muted">Tamaño Aceptado: 1920 px por 720 px</span><br>

                    </label>
                    
                    <div class="form-label-addon d-flex align-items-center align-self-start">
                    <span class="bg-danger text-white rounded-circle" style="display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; font-size: 12px;" title="Obligatorio">!</span>       
                    <a class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;><p>Imagen que se mostrará en dispositivos móviles. Recuerde que la resolución de la imagen debe ser de 1920px por 1080px.</p></div>" data-html="true" tabindex="0" data-trigger="focus" aria-label="Ayuda sobre Imagen de dispositivos móviles">
                            <i class="icon fa fa-question-circle text-info fa-fw " title="Ayuda sobre Imagen de dispositivos móviles" role="img" aria-label="Ayuda sobre Imagen de dispositivos móviles"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-9 form-inline align-items-start felement" data-fieldtype="select">
                    <label for="mobile_image" id="label_mobile_image" class="btn btn-secondary fp-btn-choose">Seleccionar imagen...</label>
                    <input type="file" name="mobile_image" id="mobile_image" accept="image/*" style="display: none;">
                </div>
            </div>

        ';

        $mform->addElement('html', $mobile_input_file);


        $mobile_image_name_label = '
                
            <div id="fitem_id_category" class="form-group row  fitem">
                <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
                    <label id="id_category_label" class="d-inline word-break " for="id_category">
                        Imagen en uso:
                    </label>
                    <div class="form-label-addon d-flex align-items-center align-self-start">
                        <a class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="<div class=&quot;no-overflow&quot;><p>Nombre de la imagen actualmente en uso para dispositivos móviles.</p></div>" data-html="true" tabindex="0" data-trigger="focus" aria-label="Ayuda sobre Imagen en uso">
                            <i class="icon fa fa-question-circle text-info fa-fw " title="Ayuda sobre Imagen en uso" role="img" aria-label="Ayuda sobre Imagen en uso"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-9 form-inline align-items-start felement" data-fieldtype="select">
                    <label class="align-self-center" name="mobile_image_name" id="id_mobile_image_name"></label>
                </div>
            </div>

        ';

        $mform->addElement('html', $mobile_image_name_label);

        $mform->addElement('textarea', 'description', 'Descripción:', array('cols' => 50, 'rows' => 5));
        $mform->setType('textarea_field', PARAM_TEXT);
        $mform->addHelpButton('description', 'description', 'local_slider_form');

    $mform->addElement('advcheckbox', 'state', 'Estado:', 'Activar las imágenes en el Banner de plataforma.');
    $mform->addHelpButton('state', 'state', 'local_slider_form');

    // Checkbox adicional para activar también en banner de curso (colocado junto a 'state')
    $mform->addElement('advcheckbox', 'course_state', 'Activar imagen en Banner de curso');
    $mform->addHelpButton('course_state', 'course_state', 'local_slider_form');

        $mform->addElement('html', '<div class="form-group row">
            <div class="col-md-12 text-left">
                <span class="bg-danger text-white rounded-circle" style="display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; font-size: 12px;" title="Campo obligatorio">!</span>
                <span class="text ml-2">Requerido</span>
            </div>
        </div>');
        

    }

}