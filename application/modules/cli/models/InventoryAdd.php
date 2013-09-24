<?php

class Cli_Model_InventoryAdd
{

    public function __construct($heroId, $artifactId, $user, $db, $gameHandler)
    {
        if ($heroId == null) {
            $gameHandler->sendError($user, 'Brak "heroId"!');
            return;
        }

        $mHero = new Application_Model_Hero($user->parameters['playerId'], $db);
        if (!$mHero->isMyHero($heroId)) {
            $gameHandler->sendError($user, 'To nie jest Twój hero.');
            return;
        }

        $mChest = new Application_Model_Chest($user->parameters['playerId'], $db);
        if (!$mChest->artifactExists($artifactId)) {
            $gameHandler->sendError($user, 'Tego artefaktu nie ma w skrzyni.');
            return;
        }

        $mInventory = new Application_Model_Inventory($heroId, $user->parameters['gameId'], $db);
        if ($mInventory->itemExists($artifactId)) {
            $gameHandler->sendError($user, 'Ten artefakt już jest w Twoim ekwipunku.');
            return;
        }

        $mInventory->addArtifact($artifactId);

        $token = array(
            'type' => 'inventoryAdd',
            'heroId' => $heroId,
            'artifactId' => $artifactId
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}