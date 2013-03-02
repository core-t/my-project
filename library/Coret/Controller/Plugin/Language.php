<?php

class Coret_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract
{

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $lang = $request->getParam('lang', null);

        $translate = new Zend_Translate('gettext',
            APPLICATION_PATH . "/resources/languages/",
            null,
            array('scan' => Zend_Translate::LOCALE_DIRECTORY));

        if (!$translate->isAvailable($lang)) {
            $lang = 'en';
        }

        $translate->setLocale($lang);

        Zend_Registry::set('Zend_Translate', $translate);

        Zend_Registry::set('lang', $lang);

        Zend_Validate_Abstract::setDefaultTranslator(new Zend_Translate('array',
            APPLICATION_PATH . '/resources/languages/' . $lang . '/Zend_Validate.php',
            $lang,
            array('scan' => Zend_Translate::LOCALE_DIRECTORY)));
    }
}