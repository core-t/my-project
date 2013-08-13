<?php

class Admin_View_Helper_Tabelka extends Zend_View_Helper_Abstract
{

    private $j = 0;
    protected $_options = array();

    public function tabelka(array $columns, $controller, $primary)
    {
        return $this->create($columns, $controller, $primary);
    }

    protected function create(array $columns, $controller, $primary)
    {
        if (!is_array($columns)) {
            throw new Exception('$columns is not array type');
        }
        if (empty($controller)) {
            throw new Exception('No $controller parameter');
        }
        $table = $this->createTableHeader($columns);
        $this->initJ();
        if (count($this->view->paginator)) {
            foreach ($this->view->paginator as $row) {
                $table .= $this->createTableContent($row, $columns);
                $table .= $this->createButtons($controller, $row[$primary], $row);
                $table .= '</tr>';
            }
        } else {
            $table .= '<tr><td colspan="100%">brak danych</td></tr>';
        }

        $table = '<table id="list">' . $table . '</table>';
        if (isset($this->view->paginator)) {
            return $table . $this->view->paginationControl($this->view->paginator, 'Sliding', 'pagination_control.phtml');
        } else {
            return $table;
        }
    }

    protected function initJ()
    {
        $page = Zend_Controller_Front::getInstance()->getRequest()->getParam('page');
        if (empty($page)) {
            $page = 1;
        }
        $this->j = ($page - 1) * $this->view->paginator->getItemCountPerPage();
    }

    protected function createTableHeader(array $columns)
    {
        $th = '<tr><th><a href="' . $this->view->url(array('sort' => null)) . '">Lp</a></th>';

        $sort = Zend_Controller_Front::getInstance()->getRequest()->getParam('sort');
        $order = Zend_Controller_Front::getInstance()->getRequest()->getParam('order');

        foreach ($columns AS $key => $val) {
            if (isset($val['active']['table']) && !$val['active']['table']) {
                continue;
            }

            if ($sort == $key) {
                switch ($order) {
                    case 'desc':
                        $order = 'asc';
                        $img = '<img id="order" src="/img/admin/orderDown.png" alt="desc"/>';
                        break;
                    default:
                        $order = 'desc';
                        $img = '<img id="order" src="/img/admin/orderUp.png" alt="asc"/>';
                        break;
                }
            } else {
                $img = '';
            }
            $th .= '<th>' . $img . '<a href="' . $this->view->url(array('sort' => $key, 'order' => $order)) . '">' . $val['label'] . '</a></th>';
        }

        return $th . '<th></th></tr>';
    }

    protected function createTableContent(array $row, array $columns)
    {
        $this->j++;
        $content = '<tr><td class="right">' . $this->j . '.</td>';
        foreach (array_keys($columns) as $key) {
            if (isset($columns[$key]['active']['table']) && !$columns[$key]['active']['table']) {
                continue;
            }

            if (!array_key_exists($key, $row)) {
                continue;
            }

            if (isset($columns[$key]['class'])) {
                $cssClass = ' ' . $columns[$key]['class'];
            } else {
                $cssClass = '';
            }

            switch ($columns[$key]['type']) {
                case 'number':
                    $content .= '<td class="center' . $cssClass . '">' . Coret_View_Helper_Formatuj::number($row[$key]) . '</td>';
                    break;
                case 'checkbox':
                    $content .= '<td class="center' . $cssClass . '">' . Coret_View_Helper_Formatuj::bool($row[$key]) . '</td>';
                    break;
                case 'date':
                    $content .= '<td class="center' . $cssClass . '">' . Coret_View_Helper_Formatuj::date($row[$key]) . '</td>';
                    break;
                default:
                    $content .= '<td class="left' . $cssClass . '">' . Coret_View_Helper_Formatuj::varchar($row[$key]) . '</td>';
                    break;
            }
        }
        return $content;
    }

    protected function createButtons($controller, $id, $params = null)
    {
        return '
        <td>
            <a href="/admin/' . $controller . '/edit/id/' . $id . '" title="Zmień dane">
                <img class="pointer zmien" src="' . $this->view->baseUrl() . '/img/admin/zmien.png" alt="Zmień dane" />
            </a>
            <a href="/admin/' . $controller . '/delete/id/' . $id . '" title="Usuń">
                <img class="pointer usun" src="' . $this->view->baseUrl() . '/img/admin/usun.png" alt="Usuń" />
            </a>
        </td>';
    }

}