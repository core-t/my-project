<?php

class Admin_Model_Portfolio extends Coret_Model_ParentDb {

    protected $_width = 209;
    protected $_height = 140;
    protected $_name = 'Portfolio';
    protected $_primary = 'id';
    protected $_columns = array(
        'image' => array('nazwa' => 'Logo', 'typ' => 'image'),
        'identyfikator' => array('nazwa' => 'Dodane przez', 'typ' => 'tekst'),
        'data' => array('nazwa' => 'Data utworzenia', 'typ' => 'data')
    );

    /**
     *
     * @param type $select
     * @return type
     */
    public function addJoin($select) {
        $mPortfolioLang = new Admin_Model_PortfolioLang($this->_params);
        $columns = $mPortfolioLang->getColumns();
        $array = array();
        foreach ($columns as $k => $v)
        {
            $array[] = $k;
        }
        $select->join('Portfolio_Lang', 'Portfolio.id = Portfolio_Lang.id_parent', $array);
        return $select;
    }

    /**
     *
     * @return \Zend_Paginator_Adapter_DbSelect
     */
    public function getPagination() {
        $select = $this->getSelect();
        $select = $this->addJoin($select);
        return new Zend_Paginator_Adapter_DbSelect($select);
    }

    public function handleElement(Array $post) {
        $dataPortfolio = $this->prepareData($post);
        $mPortfolioLang = new Admin_Model_PortfolioLang($this->_params);
        $dataPortfolioLang = $mPortfolioLang->prepareData($post);

        $dataPortfolio['identyfikator'] = Zend_Auth::getInstance()->getIdentity();
        $dataPortfolioLang['identyfikator'] = $dataPortfolio['identyfikator'];

        if ($post['id']) {
            $id = $post['id'];
            if (isset($dataPortfolio['image'])) {
                $this->createThumbnail($id, $dataPortfolio['image']);
            }
            try {
                $dataPortfolioLang['id_parent'] = $id;
                if ($mPortfolioLang->chechIfExist($dataPortfolioLang)) {
                    $mPortfolioLang->updateElement($dataPortfolioLang);
                } else {
                    $mPortfolioLang->insertElement($dataPortfolioLang);
                }
                $this->updateElement($dataPortfolio);
                return 1;
            } catch (Exception $e) {
                throw new Exception($e);
            }
        } else {
            $dataPortfolio = $this->addDataForInsert($dataPortfolio);
            $dataPortfolioLang = $this->addDataForInsert($dataPortfolioLang);

            $id = $this->insertElement($dataPortfolio);
            if ($id) {
                $dataPortfolioLang['id_parent'] = $id;
                $mPortfolioLang->insertElement($dataPortfolioLang);
                if (isset($dataPortfolio['image'])) {
                    $this->createThumbnail($id, $dataPortfolio['image']);
                }
            }
        }
    }

    private function createThumbnail($id, $image) {
        $cT = new Coret_Model_Thumbnail($this->_params, $id, $image);
        $cT->createThumbnail($this->_width, $this->_height);
    }

}

