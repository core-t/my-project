function sendNextTurn() {
    lock = true;
    if(typeof socket == 'undefined') {
        alert('Socket disconnected!');
        return null;
    }
    $.getJSON(urlTurn, function(result) {
        unselectArmy();
        changeTurn(result.playerId, result.color);
        wsTurn(result.playerId, result.color);
    });
}

function sendMove(movesSpend) {
    if(typeof socket == 'undefined') {
        alert('Socket disconnected!');
        return null;
    }
    if(selectedArmy.x == newX && selectedArmy.y == newY) {
        return null;
    } else {
        lock = true;
        var castleId = isEnemyCastle(newX, newY)
        if(castleId) {
            $.getJSON(urlFightCastle + '/armyId/' + selectedArmy.armyId + '/x/' + newX + '/y/' + newY + '/m/' + movesSpend + '/cid/' + castleId, function(result) {
                if(result.victory) {
                    deleteArmyByPosition(players[my.color].armies['army'+selectedArmy.armyId].x, players[my.color].armies['army'+selectedArmy.armyId].y, my.color);
                    players[my.color].armies['army'+selectedArmy.armyId] = new army(result, my.color);
                    newX = players[my.color].armies['army'+selectedArmy.armyId].x;
                    newY = players[my.color].armies['army'+selectedArmy.armyId].y;
                    wsArmyAdd(selectedArmy.armyId);

                    // delete enemy - find enemy at position?
                    var armiesToDelete = getEnemyCastleGarrison(castleId);
                    for(i in armiesToDelete) {
                        deleteArmy('army' + armiesToDelete[i].armyId, armiesToDelete[i].color);
                        wsArmyDelete(armiesToDelete[i].armyId, armiesToDelete[i].color);
                    }

                    wsCastleOwner(castleId, my.color);
                    castleOwner(castleId, my.color);
                } else {
                    var armiesToCheck = getEnemyCastleGarrison(castleId);
                    for(i in armiesToCheck) {
                        console.log(armiesToCheck[i]);
                    }
                    deleteArmy('army' + selectedArmy.armyId, my.color);
                    wsArmyDelete(selectedArmy.armyId, my.color);
                    unselectArmy();
                }
                lock = false;
            });
        } else if(selectedEnemyArmy && selectedEnemyArmy.x == newX && selectedEnemyArmy.y == newY) {
            $.getJSON(urlFightArmy + '/armyId/' + selectedArmy.armyId + '/x/' + newX + '/y/' + newY + '/m/' + movesSpend + '/eid/' + selectedEnemyArmy.armyId, function(result) {
                if(result.victory == true) {
                    deleteArmyByPosition(players[my.color].armies['army'+selectedArmy.armyId].x, players[my.color].armies['army'+selectedArmy.armyId].y, my.color);
                    players[my.color].armies['army'+selectedArmy.armyId] = new army(result, my.color);
                    newX = players[my.color].armies['army'+selectedArmy.armyId].x;
                    newY = players[my.color].armies['army'+selectedArmy.armyId].y;
                    wsArmyAdd(selectedArmy.armyId);

                    deleteArmyByPosition(newX, newY, selectedEnemyArmy.color);
                    wsArmyDelete(selectedEnemyArmy.armyId, selectedEnemyArmy.color);

                    selectArmy(players[my.color].armies['army'+selectedArmy.armyId]);
                } else if(result.victory == false) {
                    deleteArmyByPosition(selectedArmy.x, selectedArmy.y, my.color);
                    wsArmyDelete(selectedArmy.armyId, my.color);
                    getAddArmy(selectedEnemyArmy.armyId);
                    wsArmyAdd(selectedEnemyArmy.armyId);
                    unselectArmy();
                } else {
                    console.log('Victory?')
                }
                lock = false;
            });
        } else {
            $.getJSON(urlMove + '/aid/' + selectedArmy.armyId + '/x/' + newX + '/y/' + newY, function(result) {
                if(result) {
                    var a = players[my.color].armies['army'+selectedArmy.armyId];
                    unselectArmy();
                    walk(result, a.element);
                }
            });
        }
        return true;
    }
}

function walk(result, el) {
    for(i in result.path) {
        break;
    }
    if(typeof result.path[i] == 'undefined') {
        deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
        players[my.color].armies['army'+result.army.armyId] = new army(result.army, my.color);
        newX = players[my.color].armies['army'+result.army.armyId].x;
        newY = players[my.color].armies['army'+result.army.armyId].y;
        wsArmyAdd(result.army.armyId);
        lock = false;
        return null;
    } else {
        wsArmyMove(result.path[i].x, result.path[i].y, unselectedArmy.armyId);
        el.css({
            display:'none',
            left: result.path[i].x + 'px',
            top: result.path[i].y + 'px'
        });
        zoomer.lensSetCenter(result.path[i].x, result.path[i].y);
        el.fadeIn(1, function() {
            delete result.path[i];
            walk(result, el);
        });
    }
}

function startMyTurn() {
    $.getJSON(urlStartMyTurn, function(result) {
        for(i in result) {
            players[my.color].armies[i] = new army(result[i], my.color);
            wsArmyAdd(result[i].armyId);
        }
        lock = false;
    });
}

function getAddArmy(armyId) {
    $.getJSON(urlAddArmy+'/armyId/'+armyId, function(result) {
        if(typeof result.armyId != 'undefined') {
            players[result.color].armies['army' + result.armyId] = new army(result, result.color);
            console.log('dodana ' + armyId + ' - ' + result.color);
        }
    });
}
