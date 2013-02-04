<?php

class Zend_View_Helper_ImageType {

    public function imageType($image) {
        $type = strtolower(substr($image, -3));
        switch ($type)
        {
            case 'jpg':
            case 'png':
            case 'gif':
                return $type;
            default:
                return 'jpg';
        }
    }

}

