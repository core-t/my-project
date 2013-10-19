<?php

class Coret_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract
{

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $id_lang = 0;
        $countryCode = $request->getParam('lang', null);

        $translate = new Zend_Translate('gettext',
            APPLICATION_PATH . "/resources/languages/",
            null,
            array('scan' => Zend_Translate::LOCALE_DIRECTORY));

        if (!$translate->isAvailable($countryCode)) {
            $countryCode = Zend_Registry::get('config')->lang;
            $id_lang = Zend_Registry::get('config')->id_lang;
        }

        if(!$id_lang){
            $mLanguage = new Admin_Model_Language();
            $id_lang = $mLanguage->getLanguageIdByCountryCode($countryCode);
        }

        $translate->setLocale($countryCode);

        Zend_Registry::set('Zend_Translate', $translate);

        Zend_Registry::set('lang', $countryCode);
        Zend_Registry::set('id_lang', $id_lang);

        Zend_Validate_Abstract::setDefaultTranslator(new Zend_Translate('array',
            APPLICATION_PATH . '/resources/languages/' . $countryCode . '/Zend_Validate.php',
            $countryCode,
            array('scan' => Zend_Translate::LOCALE_DIRECTORY)));
    }
}