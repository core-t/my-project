<?php

class Application_View_Helper_Websocket extends Zend_View_Helper_Abstract {

    public function __construct($view, $params) {
        $view->placeholder('webSocket')->append('
<script type="text/javascript">
if( jws.browserSupportsWebSockets() ) {
    lWSC = new jws.jWebSocketJSONClient();
    var res = login();
//    console.log(res);
}else{
    top.location = '.$view->url(array('controller'=>'index', 'action'=>'unsupported')).'
}
</script>
        ');
    }

}