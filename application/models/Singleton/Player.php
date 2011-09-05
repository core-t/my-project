<?php

/**
 * Description of Player
 *
 * @author brzoza
 */
class Application_Model_Singleton_Player extends Game_Model_Singleton {

    private static $_oInstance = false;
    //private $_connection = null;
    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_sequence = 'player_playerId_seq';
    protected $_id;
    protected $_fbId;
    protected $_playerId;
    protected $_activity;

    public static function getInstance() {
        //parent::init();
        if (self::$_oInstance == false) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    protected function __construct() {
        $this->setNamespace();
        $this->setConnection();
    }

    private function __clone() {
        
    }

    public function test() {

        exit;
    }

    public function getPlayer($playerId = null) {
        if (NULL === $playerId) {
            $playerId = $this->_namespace->player['playerId'];
        }
        $select = $this->_db->select()
                ->from($this->_name)
                ->where('"playerId" = ?', $playerId);
        $result = $this->_db->query($select)->fetch();
        if (isset($result)) {
            $this->setParams($result);
            return $result;
        }
    }

    public function updateActivity() {
        $data = array('activity' => 'now()');
        $primary = $this->_db->quoteIdentifier($this->_primary);
        $where = $this->_db->quoteInto("$primary = ?", $this->_playerId);
        $this->_db->update($this->_name, $data, $where);
        return $this;
    }

    public function insertRequest() {
        $data = array(
            $this->_primary => $this->_playerId,
            'request' => $req
        );
        $this->_db->insert($this->_name, $data);
        return $this;
    }

}

?>
