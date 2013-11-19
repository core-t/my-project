<?php

class Zend_View_Helper_Sound extends Zend_View_Helper_Abstract
{

    public function sound()
    {
        if ($handle = opendir(APPLICATION_PATH . '/../public/sound/')) {
            while (false !== ($filename = readdir($handle))) {
                if ($filename == '.' || $filename == '..') {
                    continue;
                }
                $file_parts = pathinfo($filename);

                if (!isset($file_parts['extension'])) {
                    continue;
                }

                if ($file_parts['extension'] == 'mp3') {
                    $this->view->placeholder('sound')->append('<audio preload id="' . $file_parts['filename'] . '"><source src="/sound/' . $file_parts['filename'] . '.mp3?v=' . Zend_Registry::get('config')->version . '" type="audio/mpeg"><source src="/sound/' . $file_parts['filename'] . '.ogg?v=' . Zend_Registry::get('config')->version . '" type="audio/ogg"></audio>');
                }

            }
            closedir($handle);
        }
    }

}
