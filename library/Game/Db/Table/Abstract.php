<?php

abstract class Game_Db_Table_Abstract extends Zend_Db_Table_Abstract
{

    protected $_db;
    protected $_quiet = false;
    protected $_cli = true;

    public function __construct($config = null)
    {
        parent::__construct($config);

        $this->_db = $this->getDefaultAdapter();
        $this->_cli = false;

        $this->logActivity();
    }

    protected function logActivity()
    {
        $activity = new Game_Player_Activity();
        $activity->logActivity();
    }

    protected function logRequest()
    {
        $activity = new Game_Player_Request();
//        $activity->logRequest();
    }

    protected function setQuiet($quiet)
    {
        $this->_quiet = $quiet;
    }

    protected function setCli($cli)
    {
        $this->_cli = $cli;
    }

    public function update(array $data, $where)
    {
        try {
            $updateResult = $this->_db->update($this->_name, $data, $where);
        } catch (Exception $e) {
            new Coret_Model_Logger($e);
            if ($this->_cli) {
                echo($e);
            } else {
                throw $e;
            }
            return;
        }
        switch ($updateResult) {
            case 1:
                return $updateResult;
                break;

            case 0:
                if ($this->_quiet) {
                    return;
                }
                echo('
Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            case null:
                echo('
Zapytanie zwróciło błąd
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            default:
                if ($this->_quiet) {
                    return;
                }
                echo('
Został zaktualizowany więcej niż jeden rekord (' . $updateResult . ').
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
//                print_r($updateResult);
                break;
        }
    }

    public function insert(array $data)
    {
        try {
            return $this->_db->insert($this->_name, $data);
        } catch (Exception $e) {
            new Coret_Model_Logger($e);
            if ($this->_cli) {
                echo($e);
            } else {
                throw $e;
            }
        }
    }

    public function fetchAll($select)
    {
        try {
            return $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            new Coret_Model_Logger($e);
            if ($this->_cli) {
                echo($e);
                echo($select->__toString());
            } else {
                throw $e;
            }
        }
    }

    public function delete($where)
    {
        try {
            $this->_db->delete($this->_name, $where);
        } catch (Exception $e) {
            new Coret_Model_Logger($e);
            if ($this->_cli) {
                echo($e);
            } else {
                throw $e;
            }
        }
    }
}
