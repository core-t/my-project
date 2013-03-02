<?php

class Zend_View_Helper_Tabelka extends Zend_View_Helper_Abstract
{

    private $j = 0;

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
        foreach ($kolumny AS $val) {
            $th .= '<th>' . $val['nazwa'] . '</th>';
        }
        return '<tr><th>Lp</th>' . $th . '<th></th></tr>';
    }

    protected function createTableContent(array $v, array $kolumny)
    {
        $this->j++;
        $content = '<tr><td>' . $this->j . '</td>';
        foreach ($v AS $key => $val) {
            if (!isset($kolumny[$key])) {
                continue;
            }
            if (!isset($kolumny[$key]['class'])) {
                $klasa = '';
            } else {
                $klasa = ' class="' . $kolumny[$key]['class'] . '"';
            }
            switch ($kolumny[$key]['typ']) {
                case 'nrb':
                    $val = Coret_View_Helper_Formatuj::nrb($val);
                    break;
                case 'data':
                    $content .= '<td' . $klasa . '>' . Coret_View_Helper_Formatuj::date($val) . '</td>';
                    break;
                default:
                    $content .= '<td' . $klasa . '>' . $val . '</td>';
                    break;
            }
        }
        return $content;
    }

    protected function createButtons($kontroler, $id, $params = null)
    {
        return '<td>
        <img class="pointer zmien" onclick="document.location = \'/admin/' . $kontroler . '/edit/id/' . $id . '\'" src="' . $this->view->baseUrl() . '/img/admin/zmien.png" alt="Zmień dane" title="Zmień dane" />
        <img class="pointer usun" onclick="document.location = \'/admin/' . $kontroler . '/delete/id/' . $id . '\'" src="' . $this->view->baseUrl() . '/img/admin/usun.png" alt="Usuń" title="Usuń" />
    </td>';
    }

}