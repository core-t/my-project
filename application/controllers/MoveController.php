<?php

class MoveController extends Warlords_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function goAction()
    {
        // action body
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if(!$modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            throw new Exception('Nie Twoja tura.');
        }
        $armyId = $this->_request->getParam('aid');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        if (!empty($armyId) AND $x !== null AND $y !== null) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyPositionByArmyId($armyId, $this->_namespace->player['playerId']);
            $movesSpend = 0;
            $this->calculateNewArmyPosition($army, $x, $y);
            foreach($this->path as $path) {
                $movesSpend += $path['cost'];
            }
            if ($army['movesLeft'] < $movesSpend) {
                throw new Exception('Pozostało mniej ruchów niż gracz próbuje wydać!');
                return false;
            } elseif($movesSpend > 0) {
                $data = array(
                    'position' => $x . ',' . $y,
                    'movesLeft' => $army['movesLeft'] - $movesSpend
                );
                $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                $armyId = $modelArmy->joinArmiesAtPosition($data['position'], $this->_namespace->player['playerId']);
                switch ($res) {
                    case 1:
                        $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                        $result['pos'] = $army['position'];
//                         $result['m'] = $army['movesLeft'];
                        $result['path'] = $this->path;
                        $result['army'] = $army;
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
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y" lub "movesSpend"!');
        }
    }

    private function calculateNewArmyPosition($army, $newX, $newY) {
        $position = explode(',', substr($army['position'], 1 , -1));
        $position = array('x' => $position[0], 'y' => $position[1]);
        $this->movesLeft = $army['movesLeft'];
        $modelBoard = new Application_Model_Board();
        $this->fields = $modelBoard->getBoardFields();
        $castlesSchema = $modelBoard->getCastlesSchema();
        foreach($castlesSchema as $castle) {
            $y = $castle['position']['y']/40;
            $x = $castle['position']['x']/40;
            $this->fields[$y][$x] = 'r';
            $this->fields[$y + 1][$x + 1] = 'r';
        }
        
        $this->path = array();
        $vectorLenth = sqrt(pow($newX - $position['x'], 2) + pow($position['y'] - $newY, 2));
        $cosAlpha = ($newX - $position['x']) / $vectorLenth;
        $sinAlpha = ($newY - $position['y']) / $vectorLenth;

        $pfX = $position['x'] / 40;
        $pfY = $position['y'] / 40;
        if($cosAlpha>=0 && $sinAlpha>=0) {
            $movesSpend = $this->downRight($newX, $newY, $position, $pfX, $pfY);
        } elseif ($cosAlpha>=0 && $sinAlpha<=0) {
            $movesSpend = $this->topRight($newX, $newY, $position, $pfX, $pfY);
        } elseif ($cosAlpha<=0 && $sinAlpha<=0) {
            $movesSpend = $this->topLeft($newX, $newY, $position, $pfX, $pfY);
        } elseif ($cosAlpha<=0 && $sinAlpha>=0) {
            $movesSpend = $this->downLeft($newX, $newY, $position, $pfX, $pfY);
        }
    }

    private function downRight($newX, $newY, $position, $pfX, $pfY) {
        $xLenthPixels = $newX - $position['x'];
        $xLenthPoints = $xLenthPixels / 40;
        $yLenthPixels = $newY - $position['y'];
        $yLenthPoints = $yLenthPixels / 40;
        $movesSpend = 0;
        if($xLenthPixels < $yLenthPixels) {
            for($i = 1; $i <= $xLenthPoints; $i++) {
                $pfX += 1;
                $pfY += 1;
                $m = $this->addPathDiv($pfX, $pfY, 'se', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($yLenthPoints - $xLenthPoints); $i++) {
                $pfY += 1;
                $m = $this->addPathDiv($pfX, $pfY, 's', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
        } else {
            for($i = 1; $i <= $yLenthPoints; $i++) {
                $pfX += 1;
                $pfY += 1;
                $m = $this->addPathDiv($pfX, $pfY, 'se', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($xLenthPoints - $yLenthPoints); $i++) {
                $pfX += 1;
                $m = $this->addPathDiv($pfX, $pfY, 'e', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
        }
        return $movesSpend;
    }

    private function topRight($newX, $newY, $position, $pfX, $pfY) {
        $xLenthPixels = $newX - $position['x'];
        $xLenthPoints = $xLenthPixels / 40;
        $yLenthPixels = $position['y'] - $newY;
        $yLenthPoints = $yLenthPixels / 40;
        $movesSpend = 0;
        if($xLenthPixels < $yLenthPixels) {
            for($i = 1; $i <= $xLenthPoints; $i++) {
                $pfX += 1;
                $pfY -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'ne', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($yLenthPoints - $xLenthPoints); $i++) {
                $pfY -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'n', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
        } else {
            for($i = 1; $i <= $yLenthPoints; $i++) {
                $pfX += 1;
                $pfY -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'ne', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($xLenthPoints - $yLenthPoints); $i++) {
                $pfX += 1;
                $m = $this->addPathDiv($pfX, $pfY, 'e', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;        }
        }
        return $movesSpend;
    }

    private function topLeft($newX, $newY, $position, $pfX, $pfY) {
        $xLenthPixels = $position['x'] - $newX;
        $xLenthPoints = $xLenthPixels / 40;
        $yLenthPixels = $position['y'] - $newY;
        $yLenthPoints = $yLenthPixels / 40;
        $movesSpend = 0;
        if($xLenthPixels < $yLenthPixels) {
            for($i = 1; $i <= $xLenthPoints; $i++) {
                $pfX -= 1;
                $pfY -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'nw', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($yLenthPoints - $xLenthPoints); $i++) {
                $pfY -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'n', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
        } else {
            for($i = 1; $i <= $yLenthPoints; $i++) {
                $pfX -= 1;
                $pfY -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'nw', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($xLenthPoints - $yLenthPoints); $i++) {
                $pfX -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'w', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
        }
        return $movesSpend;
    }

    private function downLeft($newX, $newY, $position, $pfX, $pfY) {
        $xLenthPixels = $position['x'] - $newX;
        $xLenthPoints = $xLenthPixels / 40;
        $yLenthPixels = $newY - $position['y'];
        $yLenthPoints = $yLenthPixels / 40;
        $movesSpend = 0;
        if($xLenthPixels < $yLenthPixels) {
            for($i = 1; $i <= $xLenthPoints; $i++) {
                $pfX -= 1;
                $pfY += 1;
                $m = $this->addPathDiv($pfX, $pfY, 'sw', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($yLenthPoints - $xLenthPoints); $i++) {
                $pfY += 1;
                $m = $this->addPathDiv($pfX, $pfY, 's', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
        } else {
            for($i = 1; $i <= $yLenthPoints; $i++) {
                $pfX -= 1;
                $pfY += 1;
                $m = $this->addPathDiv($pfX, $pfY, 'sw', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
            for($i = 1; $i <= ($xLenthPoints - $yLenthPoints); $i++) {
                $pfX -= 1;
                $m = $this->addPathDiv($pfX, $pfY, 'w', $movesSpend);
                if(!$m || $m == $movesSpend) {
                    return $movesSpend;
                }
                $movesSpend = $m;
            }
        }
        return $movesSpend;
    }

    private function addPathDiv($pfX, $pfY, $direction, $movesSpend) {
        if($movesSpend >= $this->movesLeft) {
            return $movesSpend;
        }
        $terrainType = $this->fields[$pfY][$pfX];
        if($terrainType == 'M' || $terrainType == 'w') {
            return 0;
        }
        $terrain = $this->getTerrain($terrainType);
//         var_dump($terrain);
        $this->path[] = array(
            'terrain' => $terrain[0],
            'cost' => $terrain[1],
            'x' => $pfX * 40,
            'y' => $pfY * 40
            );
        if(($movesSpend + $terrain[1]) > $this->movesLeft) {
            return $movesSpend;
        }
        return $movesSpend + $terrain[1];
    }

    private function getTerrain($type) {
        switch($type) {
            case 'r':
                return array('Road', 1);
            case 'w':
                return array('Water', 10);
            case 'm':
                return array('Hills', 5);
            case 'M':
                return array('Mountains', 10);
            case 'g':
                return array('Grassland', 2);
            case 'f':
                return array('Forest', 3);
            case 's':
                return array('Swamp', 4);
        }
    }


}

