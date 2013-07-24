<?php

/**
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_WofHandler extends WebSocket_UriHandler {

    /**
     * @param $db
     * @param $token
     * @param $gameId
     * @param null $debug
     */
    public function sendToChannel($db, $token, $gameId, $debug = null) {

        $users = Cli_Model_Database::getInGameWSSUIds($gameId, $db);

        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }
        foreach ($users AS $row)
        {
            foreach ($this->users AS $u)
            {
                if ($u->getId() == $row['webSocketServerUserId']) {
                    $this->send($u, Zend_Json::encode($token));
                }
            }
        }
    }

    /**
     * @param $user
     * @param $msg
     * @param null $debug
     */
    public function sendError($user, $msg, $debug = null) {
        $token = array(
            'type' => 'error',
            'msg' => $msg
        );
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ (ERROR)');
            print_r($token);
        }
        $this->send($user, Zend_Json::encode($token));
    }

}
