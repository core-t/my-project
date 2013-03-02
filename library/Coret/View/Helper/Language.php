<?php

class Zend_View_Helper_Language extends Zend_View_Helper_Abstract {

    private $_l;

    public function Language($language) {
        $this->_l = '/' . $language;

        $this->view->placeholder('language')
                ->setPrefix($this->prefix($language))
                ->setPostfix('</div></div></div>');

        if ($language != 'de') {
            $this->createLink('de', $this->view->translate('niemiecki'));
        }
        if ($language != 'en') {
            $this->createLink('en', $this->view->translate('angielski'));
        }
        if ($language != 'pl') {
            $this->createLink('pl', $this->view->translate('polski'));
        }
    }

    private function prefix($language) {
        switch ($language) {
            case 'de':
                $name = $this->view->translate('niemiecki');
                break;
            case 'pl':
                $name = $this->view->translate('polski');
                break;
            case 'en':
//            default:
                $name = $this->view->translate('angielski');
                $language = 'en';
        }
        return '
                    <div id="language">
                        <div id="select">
                            <img id="roll" src="/img/core-t/rolldown_white.png" alt="" />
                            <div id="text_1">' . $this->view->translate('Wybierz język') . '</div>
                            <img id="flag" src="/img/core-t/flag_' . $language . '.png" alt="" />
                            <div id="text_2">' . $name . '</div>
                        </div>
                        <div id="bar">
                            <div id="list">';
    }

    private function createLink($language, $name) {
        if ($language == $this->_l) {
            $active = ' class="active"';
        } else {
            $active = '';
        }
        $url = $this->view->url(array('lang' => $language));
        $this->view->placeholder('language')
                ->append('<div class="el" id="' . $language . '" onclick="changeLanguage(\'' . $url . '\')"><img src="/img/core-t/flag_' . $language . '.png" /> ' . $name . '</div>');
    }

}

