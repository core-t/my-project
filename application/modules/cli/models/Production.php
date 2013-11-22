<?php

class Cli_Model_Production
{
    public function __construct($dataIn, $user, $db, $gameHandler)
    {
        $castleId = $dataIn['castleId'];
        $unitId = $dataIn['unitId'];
        if (isset($dataIn['relocationCastleId'])) {
            $relocationCastleId = $dataIn['relocationCastleId'];
        } else {
            $relocationCastleId = null;
        }

        if ($castleId === null) {
            $gameHandler->sendError($user, 'No "castleId"!');
            return;
        }

        if (empty($unitId)) {
            $gameHandler->sendError($user, 'No "unitId"!');
            return;
        }

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);

        if (!$mCastlesInGame->isPlayerCastle($castleId, $user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        if ($relocationCastleId && !$mCastlesInGame->isPlayerCastle($relocationCastleId, $user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        if ($unitId != -1) {
            $mMapCastlesProduction = new Application_Model_MapCastlesProduction($db);
            $production = $mMapCastlesProduction->getCastleProduction($castleId);

            if (!isset($production[$unitId])) {
                $this->sendError($user, 'Can\'t produce this unit here!');
                return;
            }
        } else {
            $unitId = null;
        }


        if ($mCastlesInGame->setProduction($user->parameters['playerId'], $castleId, $unitId, $relocationCastleId)) {
            $token = array(
                'type' => $dataIn['type'],
                'unitId' => $unitId,
                'castleId' => $castleId,
                'relocationCastleId' => $relocationCastleId
            );

            $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
        }

    }
}