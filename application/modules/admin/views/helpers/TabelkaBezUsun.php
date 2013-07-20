<?php

class Admin_View_Helper_TabelkaBezUsun extends Admin_View_Helper_Tabelka
{

    public function tabelkaBezUsun(array $kolumny, $kontroler, $primary)
    {
        return $this->create($kolumny, $kontroler, $primary);
    }

    protected function createButtons($kontroler, $id, $params = null)
    {
        return '<td><img class="pointer zmien" onclick="document.location = \'/admin/' . $kontroler . '/edit/id/' . $id . '\'" src="' . $this->view->baseUrl() . '/img/admin/zmien.png" alt="Zmień dane" title="Zmień dane" /></td>';
    }

}