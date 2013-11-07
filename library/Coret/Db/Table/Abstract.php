<?php

abstract class Coret_Db_Table_Abstract extends Zend_Db_Table_Abstract
{

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    protected $_quiet = true;
    protected $_cli = true;

    public function __construct($config = null)
    {
        parent::__construct($config);

        $this->_db = $this->getDefaultAdapter();
        $this->_cli = false;

    }

    protected function setQuiet($quiet)
    {
        $this->_quiet = $quiet;
    }

    protected function setCli($cli)
    {
        $this->_cli = $cli;
    }

    public function update(array $data, $where, $name = null)
    {
        if (!$name) {
            $name = $this->_name;
        }
        try {
            $updateResult = $this->_db->update($name, $data, $where);
        } catch (Exception $e) {
            if ($this->_cli) {
                $l = new Coret_Model_Logger();
                $l->log($e);

                echo($e);
            } else {
                $l = new Coret_Model_Logger('www');
                $l->log($e);

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

    public function insert(array $data, $name = null)
    {
        if (!$name) {
            $name = $this->_name;
        }
        try {
            return $this->_db->insert($name, $data);
        } catch (Exception $e) {
            if ($this->_cli) {
                $l = new Coret_Model_Logger();
                $l->log($e);
                $l->log($data);
                echo($e);
            } else {
                $l = new Coret_Model_Logger('www');
                $l->log($e);
                $l->log($data);
                throw $e;
            }
        }
    }

    public function selectAll($select)
    {
        try {
            return $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            if ($this->_cli) {
                $l = new Coret_Model_Logger();
                $l->log($e);
                $l->log($select->__toString());

                echo($e);
                echo($select->__toString());
            } else {
                $l = new Coret_Model_Logger('www');
                $l->log($e);
                $l->log($select->__toString());

                throw $e;
            }
        }
    }

    public function selectRow($select)
    {
        try {
            return $this->_db->fetchRow($select);
        } catch (Exception $e) {
            if ($this->_cli) {
                $l = new Coret_Model_Logger();
                $l->log($e);
                $l->log($select->__toString());

                echo($e);
                echo($select->__toString());
            } else {
                $l = new Coret_Model_Logger('www');
                $l->log($e);
                $l->log($select->__toString());

                throw $e;
            }
        }
    }

    public function selectOne($select)
    {
        try {
            return $this->_db->fetchOne($select);
        } catch (Exception $e) {
            if ($this->_cli) {
                $l = new Coret_Model_Logger();
                $l->log($e);
                $l->log($select->__toString());

                echo($e);
                echo($select->__toString());
            } else {
                $l = new Coret_Model_Logger('www');
                $l->log($e);
                $l->log($select->__toString());

                throw $e;
            }
        }
    }

    public function delete($where)
    {
        try {
            $this->_db->delete($this->_name, $where);
        } catch (Exception $e) {
            if ($this->_cli) {
                $l = new Coret_Model_Logger();
                $l->log($e);
                echo($e);
            } else {
                $l = new Coret_Model_Logger('www');
                $l->log($e);
                throw $e;
            }
        }
    }
}
