<?php

class Cli_SearchRuin {

    public function __construct($armyId, $user, $db, $gameHandler) {
        if (!Zend_Validate::is($armyId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $heroId = Cli_Database::getHeroIdByArmyIdPlayerId($user->parameters['gameId'], $armyId, $user->parameters['playerId'], $db);

        if (empty($heroId)) {
            $gameHandler->sendError($user, 'Tylko Hero może przeszukiwać ruiny!');
            return;
        }

        $position = Cli_Database::getArmyPositionByArmyId($user->parameters['gameId'], $armyId, $user->parameters['playerId'], $db);
        $ruinId = Application_Model_Board::confirmRuinPosition($position);

        if (!Zend_Validate::is($ruinId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak ruinId na pozycji');
            return;
        }

        if (Cli_Database::ruinExists($user->parameters['gameId'], $ruinId, $db)) {
            $gameHandler->sendError($user, 'Ruiny są już przeszukane.');
            return;
        }

        $find = self::search($user->parameters['gameId'], $ruinId, $heroId, $armyId, $user->parameters['playerId'], $db);

        if (Cli_Database::ruinExists($user->parameters['gameId'], $ruinId, $db)) {
            $ruin = array(
                'ruinId' => $ruinId,
                'empty' => 1
            );
        } else {
            $ruin = array(
                'ruinId' => $ruinId,
                'empty' => 0
            );
        }

        $token = array(
            'type' => 'ruin',
            'army' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $armyId, $db),
            'ruin' => $ruin,
            'find' => $find,
            'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
        );

        $gameHandler->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
    }

    static public function search($gameId, $ruinId, $heroId, $armyId, $playerId, $db) {

        $turn = Cli_Database::getTurn($gameId, $db);

        $random = rand(0, 100);
        if ($random < 10) {//10%
//śmierć
            if ($turn['nr'] <= 7) {
                $find = array('null', 1);
                Cli_Database::addRuin($gameId, $ruinId, $db);
                Cli_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            } else {
                $find = array('death', 1);
                Cli_Database::armyRemoveHero($gameId, $heroId, $db);
            }
        } elseif ($random < 55) {//45%
//kasa
            $gold = rand(50, 150);
            $find = array('gold', $gold);
            $inGameGold = Cli_Database::getPlayerInGameGold($gameId, $playerId, $db);
            Cli_Database::updatePlayerInGameGold($gameId, $playerId, $gold + $inGameGold, $db);
            Cli_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            Cli_Database::addRuin($gameId, $ruinId, $db);
        } elseif ($random < 85) {//30%
//jednostki
            if ($turn['nr'] <= 7) {
                $max1 = 11;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 12) {
                $max1 = 13;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 16) {
                $max1 = 14;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 19) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 21) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 2;
            } elseif ($turn['nr'] <= 23) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 3;
            } elseif ($turn['nr'] <= 25) {
                $max1 = 15;
                $min2 = 2;
                $max2 = 3;
            } else {
                $max1 = 15;
                $min2 = 3;
                $max2 = 3;
            }
            $unitId = rand(11, $max1);
            $numerOfUnits = rand($min2, $max2);
            $find = array('alies', $numerOfUnits);
            for ($i = 0; $i < $numerOfUnits; $i++)
            {
                Cli_Database::addSoldierToArmy($gameId, $armyId, $unitId, $db);
            }
            Cli_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            Cli_Database::addRuin($gameId, $ruinId, $db);
        } elseif ($random < 95) {//10%
//nic
            $find = array('null', 1);
            Cli_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            Cli_Database::addRuin($gameId, $ruinId, $db);
        } else {//5%
//artefakt
            $artefactId = rand(5, 34);

            if (Cli_Inventory::itemExists($gameId, $artefactId, $heroId, $db)) {
                Cli_Inventory::increaseItemQuantity($gameId, $artefactId, $heroId, $db);
            } else {
                Cli_Inventory::addArtefact($gameId, $artefactId, $heroId, $db);
            }
            $find = array('artefact', $artefactId);
            Cli_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            Cli_Database::addRuin($gameId, $ruinId, $db);
        }

        return $find;
    }

}