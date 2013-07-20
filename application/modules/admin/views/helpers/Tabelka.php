<?php

class Admin_View_Helper_Tabelka extends Zend_View_Helper_Abstract
{

    private $j = 0;
    protected $_options = array();

    public function tabelka(array $kolumny, $kontroler, $primary)
    {
        return $this->create($kolumny, $kontroler, $primary);
    }

    protected function create(array $kolumny, $kontroler, $primary)
    {
        if (!is_array($kolumny)) {
            throw new Exception('$kolumny nie jest typu array');
        }
        if (empty($kontroler)) {
            throw new Exception('Brak parametru $kontroler');
        }
        $tabelka = $this->createTableHeader($kolumny);
        if (count($this->view->paginator)) {
            foreach ($this->view->paginator as $row) {
                $tabelka .= $this->createTableContent($row, $kolumny);
                $tabelka .= $this->createButtons($kontroler, $row[$primary], $row);
                $tabelka .= '</tr>';
            }
        } else {
            $tabelka .= '<tr><td colspan="100%">brak danych</td></tr>';
        }

        $tabelka = '<table id="list">' . $tabelka . '</table>';
        if (isset($this->view->paginator)) {
            return $tabelka . $this->view->paginationControl($this->view->paginator, 'Sliding', 'pagination_control.phtml');
        } else {
            return $tabelka;
        }
    }

    protected function createTableHeader(array $kolumny)
    {
        $th = '';
        foreach ($kolumny AS $key => $val) {
            if (isset($val['active']['table']) && !$val['active']['table']) {
                continue;
            }

            $th .= '<th><a href="' . $this->view->url(array('order' => $key)) . '">' . $val['nazwa'] . '</a></th>';
        }
        return '<tr><th><a href="' . $this->view->url(array('order' => null)) . '">Lp</a></th>' . $th . '<th></th></tr>';
    }

    protected function createTableContent(array $v, array $kolumny)
    {
        $this->j++;
        $content = '<tr><td>' . $this->j . '</td>';
        foreach ($v AS $key => $val) {
            if (!isset($kolumny[$key])) {
                continue;
            }
            if (isset($kolumny[$key]['active']['table']) && !$kolumny[$key]['active']['table']) {
                continue;
            }
            if (!isset($kolumny[$key]['class'])) {
                $klasa = '';
            } else {
                $klasa = ' class="' . $kolumny[$key]['class'] . '"';
            }
            switch ($kolumny[$key]['typ']) {
                case 'checkbox':
                    $content .= '<td' . $klasa . '>' . Coret_View_Helper_Formatuj::bool($val) . '</td>';
                    break;
                case 'date':
                    $content .= '<td' . $klasa . '>' . Coret_View_Helper_Formatuj::date($val) . '</td>';
                    break;
                default:
                    $content .= '<td' . $klasa . '>' . substr(strip_tags($val), 0, 100) . '</td>';
                    break;
            }
        }
        return $content;
    }

    protected function createButtons($kontroler, $id, $params = null)
    {
        return '
        <td>
            <a href="/admin/' . $kontroler . '/edit/id/' . $id . '" title="Zmień dane">
                <img class="pointer zmien" src="' . $this->view->baseUrl() . '/img/admin/zmien.png" alt="Zmień dane" />
            </a>
            <a href="/admin/' . $kontroler . '/delete/id/' . $id . '" title="Usuń">
                <img class="pointer usun" src="' . $this->view->baseUrl() . '/img/admin/usun.png" alt="Usuń" />
            </a>
        </td>';
    }

}