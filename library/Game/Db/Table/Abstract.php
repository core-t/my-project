<?php

abstract class Game_Db_Table_Abstract extends Zend_Db_Table_Abstract
{

    protected $_db;

    public function __construct($config = null)
    {

        parent::__construct($config);
        $this->_db = $this->getDefaultAdapter();

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

    protected function update($data, $where, $quiet = false)
    {
        try {
            $updateResult = $this->_db->update($this->_name, $data, $where);
        } catch (Exception $e) {
            echo($e);

            return;
        }
        switch ($updateResult) {
            case 1:
                return $updateResult;
                break;

            case 0:
                if ($quiet) {
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
                if ($quiet) {
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
}
