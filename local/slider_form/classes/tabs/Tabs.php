<?php

class TabManager {

    private $url;
    private $tabs = [];
    private $activeTab;

    public function __construct($url) {

        $this->url = $url;
        $this->activeTab = optional_param('t', 1, PARAM_INT);
    
    }

    public function addTab($id, $name) {
    
        $this->tabs[] = new tabobject($id, new moodle_url($this->url, ['t' => $id]), $name);
    
    }

    public function displayTabs() {
    
        global $OUTPUT;
        echo $OUTPUT->tabtree($this->tabs, $this->activeTab);
    
    }

    public function getActiveTab() {
    
        return $this->activeTab;
    
    }
    
}