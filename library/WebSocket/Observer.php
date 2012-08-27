<?php

interface WebSocket_Observer {

    public function onDisconnect(WebSocket_Socket $s);

    public function onConnectionEstablished(WebSocket_Socket $s);

    public function onMessage(IWebSocketConnection $s, IWebSocketMessage $msg);

    public function onFlashXMLRequest(WebSocket_ConnectionFlash $connection);
}
