<?php

class Coret_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract {

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        $lang = $request->getParam('lang', null);

        $translate = Zend_Registry::get('Zend_Translate');

        if (!$translate->isAvailable($lang)) {
            $lang = 'en';
        }

        $translate->setLocale($lang);

        // Set language to global param so that our language route can
        // fetch it nicely.
//        $front = Zend_Controller_Front::getInstance();
//        $router = $front->getRouter();
//        $router->setGlobalParam('lang', $lang);
        Zend_Registry::set('lang', $lang);

        $translator = new Zend_Translate('array',
                        APPLICATION_PATH . '/resources/languages/' . $lang . '/Zend_Validate.php',
                        $lang,
                        array('scan' => Zend_Translate::LOCALE_DIRECTORY));
        Zend_Validate_Abstract::setDefaultTranslator($translator);
    }

}
