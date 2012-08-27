<?php

class Application_View_Helper_Websocket extends Zend_View_Helper_Abstract {

    public function __construct() {
        $script = '
var lWSC = null;
var channelAuthorized = null;
var channelCreated = null;
var channelSubscribed = null;

var aSchema = "' . Zend_Registry::get('config')->websockets->aSchema . '";
var aHost = "' . Zend_Registry::get('config')->websockets->aHost . '";
var aPort = ' . Zend_Registry::get('config')->websockets->aPort . ';
var aContext = "/jWebSocket";
var aServlet = "/jWebSocket";

var channel = "Public";
var lAccessKey = "access";
var lSecretKey = "secret";
$(document).ready(function() {
    if( jws.browserSupportsWebSockets() ) {
        lWSC = new jws.jWebSocketJSONClient();
        var res = login();
    }
});
';
        $view = new Zend_View();
        $view->headScript()->appendScript($script);
    }

}