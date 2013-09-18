<?php

class HeroController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $modelHero = new Application_Model_Hero($this->_namespace->player['playerId']);
        $this->view->heroes = $modelHero->getHeroes();

        $this->view->form = new Application_Form_Hero ();
        $this->view->form->setDefault('name', $this->view->heroes[0]['name']);
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {

                $modelHero->changeHeroName($this->view->heroes[0]['heroId'], $this->_request->getParam('name'));
                $this->_redirect('/hero');
            }
        }

        $mChest = new Application_Model_Chest($this->_namespace->player['playerId']);
        $this->view->chest = $mChest->getAll();

        $mArtifact = new Application_Model_Artifact();
        $this->view->artifacts = $mArtifact->getArtifacts();
    }

}

