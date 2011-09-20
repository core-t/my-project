<?php
abstract class Game_Controller_Game extends Zend_Controller_Action
{
    public final function init()
    {
        parent::init();

        Zend_Session::start();
        $this->_namespace = new Zend_Session_Namespace(); // default namespace

        if(empty($this->_namespace->player['playerId'])) {
            $this->_helper->redirector('index', 'login');
        }
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
        // Wywołujemy funkcję _init w klasie kontrolera
        if(method_exists($this, '_init'))
        {
            $this->_init();
        }
//        $request = Zend_Controller_Front::getInstance()->getRequest();
//        $this->view->headTitle($request->getActionName());
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        // Konstruktor klasy nadrzędnej
        parent::__construct($request, $response, $invokeArgs);

    }

}
?>