<?php

class Cli_Model_SearchRuin
{

    public function __construct($armyId, $user, $db, $gameHandler)
    {
        if (!Zend_Validate::is($armyId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
        $heroId = $mHeroesInGame->getHeroIdByArmyIdPlayerId($armyId, $user->parameters['playerId']);

        if (empty($heroId)) {
            $gameHandler->sendError($user, 'Tylko Hero może przeszukiwać ruiny!');
            return;
        }

        $position = Cli_Model_Database::getArmyPositionByArmyId($user->parameters['gameId'], $armyId, $user->parameters['playerId'], $db);
        $ruinId = Application_Model_Board::confirmRuinPosition($position);

        if (!Zend_Validate::is($ruinId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak ruinId na pozycji');
            return;
        }

        $mRuinsInGame = new Application_Model_RuinsInGame($user->parameters['gameId'], $db);

        if ($mRuinsInGame->ruinExists($ruinId)) {
            $gameHandler->sendError($user, 'Ruiny są już przeszukane.');
            return;
        }

        $find = self::search($user->parameters['gameId'], $ruinId, $heroId, $armyId, $user->parameters['playerId'], $db);

        if ($mRuinsInGame->ruinExists($ruinId)) {
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

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'ruin',
            'army' => $mArmy2->getArmyByArmyId($armyId),
            'ruin' => $ruin,
            'find' => $find,
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

    static public function search($gameId, $ruinId, $heroId, $armyId, $playerId, $db)
    {
        $mGame = new Application_Model_Game($gameId, $db);
        $turn = $mGame->getTurn();

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        if (!$mHeroesInGame->isThisCorrectHero($playerId, $heroId)) {
            echo('HeroId jest inny');

            return;
        }

        $random = rand(0, 100);

        if ($random < 10) { //10%
//śmierć
            if ($turn['nr'] <= 7) {
                $find = array('null', 1);
                $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
                $mRuinsInGame->add($ruinId);
                $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            } else {
                $find = array('death', 1);

                $mHeroesInGame->armyRemoveHero($heroId);
            }
        } elseif ($random < 55) { //45%
//kasa
            $gold = rand(50, 150);
            $find = array('gold', $gold);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $inGameGold = $mPlayersInGame->getPlayerInGameGold($playerId);

            $mPlayersInGame->updatePlayerInGameGold($playerId, $gold + $inGameGold);

            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $mRuinsInGame->add($ruinId);
        } elseif ($random < 85) { //30%
//jednostki
            $fistUnitId = Zend_Registry::get('fistUnitId');

            if ($turn['nr'] <= 7) {
                $max1 = $fistUnitId + 10;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 12) {
                $max1 = $fistUnitId + 12;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 16) {
                $max1 = $fistUnitId + 13;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 19) {
                $max1 = $fistUnitId + 14;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 21) {
                $max1 = $fistUnitId + 14;
                $min2 = 1;
                $max2 = 2;
            } elseif ($turn['nr'] <= 23) {
                $max1 = $fistUnitId + 14;
                $min2 = 1;
                $max2 = 3;
            } elseif ($turn['nr'] <= 25) {
                $max1 = $fistUnitId + 14;
                $min2 = 2;
                $max2 = 3;
            } else {
                $max1 = $fistUnitId + 14;
                $min2 = 3;
                $max2 = 3;
            }
            $unitId = rand($fistUnitId + 10, $max1);
            $numberOfUnits = rand($min2, $max2);
            $find = array('alies', $numberOfUnits);
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            for ($i = 0; $i < $numberOfUnits; $i++) {
                $mSoldier->add($armyId, $unitId);
            }
            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $mRuinsInGame->add($ruinId);

//        } elseif ($random < 95) { //10%
        } else {
//nic
            $find = array('null', 1);
            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $mRuinsInGame->add($ruinId);

//        } else { //5%
////artefakt
//            $artifactId = rand(5, 34);
//
//            $mChest = new Application_Model_Chest($playerId, $db);
//
//            if ($mChest->artifactExists($artifactId)) {
//                $mChest->increaseArtifactQuantity($artifactId);
//            } else {
//                $mChest->add($artifactId);
//            }
//
//            $find = array('artifact', $artifactId);
//
//            Cli_Model_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
//
//            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
//            $mRuinsInGame->add($ruinId);
//
        }

        return $find;
    }

}