<?php

class Admin_View_Helper_AddButton extends Zend_View_Helper_Abstract {

    public function addButton() {
        return '
<div class="pointer dodaj" onclick="document.location = \'' . $this->view->url(array('action' => 'add')) . '\'">
    <a class="button" href="' . $this->view->url(array('action' => 'add')) . '" title="Dodaj">Dodaj</a>
    <img src="' . $this->view->baseUrl() . '/img/admin/plus.png" alt="Dodaj" title="Dodaj"/>
</div>';

    }

}
