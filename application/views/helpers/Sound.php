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
                    $this->view->placeholder('sound')->append('<audio preload id="' . $file_parts['filename'] . '"><source src="/sound/' . $file_parts['filename'] . '.mp3" type="audio/mpeg"><source src="/sound/' . $file_parts['filename'] . '.ogg" type="audio/ogg"></audio>');
                }

            }
            closedir($handle);
        }
    }

}
