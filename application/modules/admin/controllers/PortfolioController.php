<?php

class Admin_PortfolioController extends Coret_Controller_Backend {

    public function init() {
        $this->view->title = 'Portfolio';
        parent::init();
        $this->params = array(
            'controller' => $this->view->controllerName
        );
    }

    public function indexAction() {
        parent::indexAction();
        $mPortfolioLang = new Admin_Model_PortfolioLang(array());
        $columns = $mPortfolioLang->getColumns();
        foreach ($columns as $k => $v)
        {
            $this->view->kolumny[$k] = $v;
        }
    }

    protected function editHandleElse($id) {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $model = new $className($this->params, $id);

        $element = $model->getElement();

        $this->view->form->populate($element);
        $this->view->form->addElement('submit', 'submit', array('label' => 'Zmień'));
        $this->view->form->setDefault('id', $id);

        $this->view->id = $id;

        if (isset($element['image'])) {
            $this->view->img = $this->view->controllerName . '_' . $id . '.' . $this->view->imageType($element['image']);
        }

        $form = new Admin_Form_Galeria();

        $mGaleria = new Admin_Model_Galeria($this->params);
        $list = $mGaleria->getList($id);

        if (count($list)) {
            foreach ($list as $row)
            {
                $image = new Admin_Form_Image(array('numer' => $row['id'], 'required' => true, 'class' => $this->view->imageType($row['image'])));
                $form->addElements($image->getElements());
            }
        } else {
            $image = new Admin_Form_Image(array('numer' => 1, 'required' => true));
            $form->addElements($image->getElements());
        }

        $this->view->formGaleria = $form;
    }

}

