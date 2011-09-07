<?php

class FightController extends Game_Controller_Action
{
    private $_result = array();
    private $defenseModifier = 0;
    private $attackModifier = 0;
    
    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function armyAction()
    {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $enemyId = $this->_request->getParam('eid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $enemyId !== null) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            $army = $this->getCombatModifiers($army);
            if($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
            }
            if(($movesSpend = $this->movesSpend($x, $y, $army, 1)) > $army['movesLeft']) {
                throw new Exception('Armia ma za mało ruchów do wykonania akcji ('.$movesSpend.'>'.$army['movesLeft'].').');
            }
            $enemy = $modelArmy->getAllUnitsFromPosition(array('x' => $x, 'y' => $y));
            $enemy = $this->getCombatModifiers($enemy);
            if(Application_Model_Board::isTowerAtPosition($x, $y)){
                $this->defenseModifier += 1;
            }
            $this->battle($army, $enemy);
            foreach($this->_result AS $r) {
                if(isset($r['heroId'])) {
                    $modelArmy->armyRemoveHero($r['heroId']);
                } else {
                    $modelArmy->destroySoldier($r['soldierId']);
                }
            }
            $enemy = $modelArmy->updateAllArmiesFromPosition(array('x' => $x, 'y' => $y));
            if (empty($enemy)) {
                $data = array(
                    'position' => $x . ',' . $y,
                    'movesSpend' => $movesSpend
                );
                $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                switch ($res) {
                    case 1:
                        $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                        $result['victory'] = true;
                        $result['battle'] = $this->_result;
                        $this->view->response = Zend_Json::encode($result);
                        break;
                    case 0:
                        throw new Exception('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                        break;
                    case null:
                        throw new Exception('Zapytanie zwróciło błąd');
                        break;
                    default:
                        throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                        break;
                }
            } else {
                $modelArmy->destroyArmy($army['armyId'], $this->_namespace->player['playerId']);
                $enemy['battle'] = $this->_result;
                $enemy['victory'] = false;
                $this->view->response = Zend_Json::encode($enemy);
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y" lub "$enemyId"!');
        }
    }

    public function ecastleAction()
    {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $castleId = $this->_request->getParam('cid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $castleId !== null) {
            $modelBoard = new Application_Model_Board();
            $castle = $modelBoard->getCastle($castleId);
            if (empty($castle)) {
                throw new Exception('Brak zamku o podanym ID!');
                return false;
            }
            if (($x >= $castle['position']['x']) AND ($x < ($castle['position']['x'] + 80)) AND ($y >= $castle['position']['y']) AND ($y < ($castle['position']['y'] + 80))) {
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                $army = $this->getCombatModifiers($army);
                if (empty($army)) {
                    throw new Exception('Brak armii o podanym ID!');
                    return false;
                }
                if($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                    throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
                }
                if(($movesSpend = 2) > $army['movesLeft']) {
                    throw new Exception('Armia ma za mało ruchów do wykonania akcji('.$movesSpend.'>'.$army['movesLeft'].').');
                }
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                if ($modelCastle->isEnemyCastle($castleId, $this->_namespace->player['playerId'])) {
                    $enemy = $modelArmy->getAllUnitsFromCastlePosition($castle['position']);
                    $enemy = $this->getCombatModifiers($enemy);
                    $this->defenseModifier += $castle['defensePoints'];
                    $this->defenseModifier += $modelCastle->getCastleDefenseModifier($castleId);
                    $this->battle($army, $enemy);
                    foreach($this->_result AS $r) {
                        if(isset($r['heroId'])) {
                            $modelArmy->armyRemoveHero($r['heroId']);
                        } else {
                            if(strpos($r['soldierId'],'s') === false){
                                $modelArmy->destroySoldier($r['soldierId']);
                            }
                        }
                    }
                    $enemy = $modelArmy->updateAllArmiesFromCastlePosition($castle['position']);
                } else {
                    throw new Exception('To nie jest zamek wroga.');
                }
                if (empty($enemy)) {
                    $res = $modelCastle->changeOwner($castleId, $this->_namespace->player['playerId']);
                    if($res == 1){
                        $data = array(
                            'position' => $x . ',' . $y,
                            'movesSpend' => $movesSpend
                        );
                        $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                        switch ($res) {
                            case 1:
                                $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                                $result['victory'] = true;
                                $result['battle'] = $this->_result;
                                $this->view->response = Zend_Json::encode($result);
                                break;
                            case 0:
                                throw new Exception('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                                break;
                            case null:
                                throw new Exception('Zapytanie zwróciło błąd');
                                break;
                            default:
                                throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                                break;
                        }
                    }else{
                        throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.'.$res);
                    }
                } else {
                    $modelArmy->destroyArmy($army['armyId'], $this->_namespace->player['playerId']);
                    $enemy['battle'] = $this->_result;
                    $enemy['victory'] = false;
                    $this->view->response = Zend_Json::encode($enemy);
                }
            } else {
                throw new Exception('Na podanej pozycji nie ma zamku!');
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y"!');
        }
    }

    public function ncastleAction()
    {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $castleId = $this->_request->getParam('cid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $castleId !== null) {
            $modelBoard = new Application_Model_Board();
            $castle = $modelBoard->getCastle($castleId);
            if (empty($castle)) {
                throw new Exception('Brak zamku o podanym ID!');
                return false;
            }
            if (($x >= $castle['position']['x']) AND ($x < ($castle['position']['x'] + 80)) AND ($y >= $castle['position']['y']) AND ($y < ($castle['position']['y'] + 80))) {
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                $army = $this->getCombatModifiers($army);
                if (empty($army)) {
                    throw new Exception('Brak armii o podanym ID!');
                    return false;
                }
                if($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                    throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
                }
                $movesSpend = 2;
                if($movesSpend > $army['movesLeft']) {
                    throw new Exception('Armia ma za mało ruchów do wykonania akcji('.$movesSpend.'>'.$army['movesLeft'].').');
                }
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                $enemy = array(
                    'soldiers' => array(
                        array(
                            'defensePoints' => 3,
                            'soldierId' => 's1'
                            ),
                        array(
                            'defensePoints' => 3,
                            'soldierId' => 's2'
                            ),
                        array(
                            'defensePoints' => 3,
                            'soldierId' => 's3'
                            )
                    ),
                    'heroes' => array()
                );
                $battle = $this->battle($army, $enemy);
                foreach($this->_result AS $r) {
                    if(isset($r['heroId'])) {
                        $modelArmy->armyRemoveHero($r['heroId']);
                    } else {
                        if(strpos($r['soldierId'],'s') === false){
                            $modelArmy->destroySoldier($r['soldierId']);
                        }
                    }
                }
                $enemy = $battle['defender']['soldiers'];
                if (empty($enemy)) {
                    $res = $modelCastle->addCastle($castleId, $this->_namespace->player['playerId']);
                    if($res == 1){
                        $data = array(
                            'position' => $x . ',' . $y,
                            'movesSpend' => $movesSpend
                        );
                        $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                        switch ($res) {
                            case 1:
                                $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                                $result['victory'] = true;
                                $result['battle'] = $this->_result;
                                $this->view->response = Zend_Json::encode($result);
                                break;
                            case 0:
                                throw new Exception('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                                break;
                            case null:
                                throw new Exception('Zapytanie zwróciło błąd');
                                break;
                            default:
                                throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                                break;
                        }
                    }else{
                        throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.'.$res);
                    }
                } else {
                    $modelArmy->destroyArmy($army['armyId'], $this->_namespace->player['playerId']);
                    $enemy['battle'] = $this->_result;
                    $enemy['victory'] = false;
                    $this->view->response = Zend_Json::encode($enemy);
                }
            } else {
                throw new Exception('Na podanej pozycji nie ma zamku!');
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y"!');
        }
    }

    private function battle($attacker, $defender) {
//        Zend_Debug::dump($defender);
        $hits = array('attack' => 2, 'defense' => 2);
        foreach ($attacker['soldiers'] as $a => $unitAttaking) {
            foreach ($defender['soldiers'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['soldiers'][$d]);
                } else {
                    unset($attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($attacker['soldiers'] as $a => $unitAttaking) {
            foreach ($defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['heroes'][$d]);
                } else {
                    unset($attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($attacker['heroes'] as $a => $unitAttaking) {
            foreach ($defender['soldiers'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['soldiers'][$d]);
                } else {
                    unset($attacker['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($attacker['heroes'] as $a => $unitAttaking) {
            foreach ($defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['heroes'][$d]);
                } else {
                    unset($attacker['heroes'][$a]);
                    break;
                }
            }
        }
        return array('attacker' => $attacker, 'defender' => $defender);
    }

    private function combat($unitAttaking, $unitDefending, $hits) {
        $attackHits = $hits['attack'];
        $defenseHits = $hits['defense'];
        if (!$attackHits) {
            $attackHits = 2;
        }
        if (!$defenseHits) {
            $defenseHits = 2;
        }
        $unitAttaking['attackPoints'] += $this->attackModifier;
        $unitDefending['defensePoints'] += $this->defenseModifier;
        while ($attackHits AND $defenseHits) {
            $maxDie = $unitAttaking['attackPoints'] + $unitDefending['defensePoints'];
            $dieAttacking = $this->rollDie($maxDie);
            $dieDefending = $this->rollDie($maxDie);
            if(isset($unitAttaking['heroId'])) {
                $id = array('heroId', $unitAttaking['heroId']);
            } else {
                $id = array('soldierId', $unitAttaking['soldierId']);
            }
            if ($unitAttaking['attackPoints'] > $dieDefending AND $unitDefending['defensePoints'] <= $dieAttacking) {
                $defenseHits--;
            } elseif ($unitAttaking['attackPoints'] <= $dieDefending AND $unitDefending['defensePoints'] > $dieAttacking) {
                $attackHits--;
            }
            if(isset($unitAttaking['heroId'])) {
                $idA = array('heroId' => $unitAttaking['heroId']);
            } else {
                $idA = array('soldierId' => $unitAttaking['soldierId']);
            }
            if(isset($unitDefending['heroId'])) {
                $idD = array('heroId' => $unitDefending['heroId']);
            } else {
                $idD = array('soldierId' => $unitDefending['soldierId']);
            }
        }
        if($attackHits){
            $this->_result[] = $idD;
        } else {
            $this->_result[] = $idA;
        }
        return array('attack' => $attackHits, 'defense' => $defenseHits);
    }

    private function rollDie($maxDie) {
        return rand(1, $maxDie);
    }

    private function calculateArmiesDistance($x, $y, $position) {
        $position = explode(',', substr($position, 1 , -1));
        return sqrt(pow($x - $position[0], 2) + pow($position[1] - $y, 2));
    }

    private function movesSpend($x, $y, $army) {
        $canFly = 1;
        $canSwim = 0;
        $movesRequiredToAttack = 1;
        foreach($army['heroes'] as $hero) {
            $canFly--;
        }
        foreach($army['soldiers'] as $soldier) {
            if($soldier['canFly']){
                $canFly++;
            }else{
                $canFly -= 200;
            }
            if($soldier['canSwim']){
                $canSwim++;
            }
        }
        $fields = Application_Model_Board::getBoardFields();
        $terrainType = $fields[$y/40][$x/40];
        $terrain = Application_Model_Board::getTerrain($terrainType, $canFly, $canSwim);
        return $terrain[1] + $movesRequiredToAttack;
    }
    
    private function getCombatModifiers($army){
        $heroExists = false;
        $canFly = false;
        if(count($army['heroes']) > 0){
            $heroExists = true;
        }
        foreach($army['soldiers'] as $soldier){
            if($soldier['canFly']){
                $canFly = true;
                break;
            }
        }
        if($canFly){
            foreach($army['soldiers'] as $k=>$soldier){
                if($soldier['canFly']){
                    continue;
                }
                $army['soldiers'][$k]['attackPoints']++;
                $army['soldiers'][$k]['defensePoints']++;
            }
        }
        if($heroExists){
            foreach($army['soldiers'] as $k=>$soldier){
                $army['soldiers'][$k]['attackPoints']++;
                $army['soldiers'][$k]['defensePoints']++;
            }
        }
        return $army;
    }
}

