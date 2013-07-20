<?php

class Coret_View_Helper_Formatuj extends Zend_View_Helper_Abstract
{

    static public function kwote($kwota)
    {
        if ($kwota) {
            $kwota = substr($kwota, 0, -2) . '.' . substr($kwota, -2);
            return number_format($kwota, 2, ',', ' ');
        } else {
            return 'brak';
        }
    }

    static public function date($data, $format = null)
    {
        if ($format)
            return date($format, strtotime($data));
        if ($data) {
            return date('Y-m-d H:i:s', strtotime($data));
        }
    }

    static public function nrb($nrb)
    {
        if ($nrb) {
            return substr($nrb, 0, 2) . '&nbsp;' . substr($nrb, 2, 4) . '&nbsp;' . substr($nrb, 6, 4) . '&nbsp;' . substr($nrb, 10, 4) . '&nbsp;' . substr($nrb, 14, 4) . '&nbsp;' . substr($nrb, 18, 4) . '&nbsp;' . substr($nrb, 22, 4);
        } else {
            return 'brak';
        }
    }

    static public function bool($bool)
    {
        if ($bool) {
            return 'TAK';
        } else {
            return 'NIE';
        }
    }
}
