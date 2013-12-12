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

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $position = $mArmy2->getArmyPositionByArmyIdPlayerId($armyId, $user->parameters['playerId']);
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

        $found = self::search($user->parameters['gameId'], $ruinId, $heroId, $armyId, $user->parameters['playerId'], $db);

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

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'ruin',
            'army' => Cli_Model_Army::getArmyByArmyId($armyId, $user->parameters['gameId'], $db),
            'ruin' => $ruin,
            'find' => $found,
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

    static public function search($gameId, $ruinId, $heroId, $armyId, $playerId, $db)
    {
        $mGame = new Application_Model_Game($gameId, $db);
        $turnNumber = $mGame->getTurnNumber();

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        if (!$mHeroesInGame->isThisCorrectHero($playerId, $heroId)) {
            echo('HeroId jest inny');

            return;
        }

        $random = rand(0, 100);

        if ($random < 10) { //10%
//śmierć
            if ($turnNumber <= 7) {
                $found = array('null', 1);
                $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
                $mRuinsInGame->add($ruinId);
                $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            } else {
                $found = array('death', 1);
                $mHeroesInGame->armyRemoveHero($heroId);
                $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
                $mHeroesKilled->add($heroId, 0, $playerId);
            }
        } elseif ($random < 55) { //45%
//kasa
            $gold = rand(50, 150);
            $found = array('gold', $gold);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $inGameGold = $mPlayersInGame->getPlayerGold($playerId);

            $mPlayersInGame->updatePlayerGold($playerId, $gold + $inGameGold);

            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $mRuinsInGame->add($ruinId);
        } elseif ($random < 85) { //30%
//jednostki
            if ($turnNumber <= 7) {
                $max1 = 0;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 9) {
                $max1 = 1;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 11) {
                $max1 = 2;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 12) {
                $max1 = 3;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 14) {
                $max1 = 4;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 17) {
                $max1 = 4;
                $min2 = 1;
                $max2 = 2;
            } elseif ($turnNumber <= 20) {
                $max1 = 4;
                $min2 = 1;
                $max2 = 3;
            } elseif ($turnNumber <= 25) {
                $max1 = 4;
                $min2 = 2;
                $max2 = 3;
            } else {
                $max1 = 4;
                $min2 = 3;
                $max2 = 3;
            }

            $specialUnits = Zend_Registry::get('specialUnits');

            $unitId = $specialUnits[rand(1, $max1)]['unitId'];

            $numberOfUnits = rand($min2, $max2);

            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            for ($i = 0; $i < $numberOfUnits; $i++) {
                $mSoldier->add($armyId, $unitId);
            }

            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $mRuinsInGame->add($ruinId);

            $found = array('allies', $numberOfUnits);
//        } elseif ($random < 95) { //10%
        } else {
//nic
            $found = array('null', 1);
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
//            $found = array('artifact', $artifactId);
//
//            Cli_Model_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
//
//            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
//            $mRuinsInGame->add($ruinId);
//
        }

        return $found;
    }

}