function sendNextTurn() {
    lock = true;
    if(typeof socket == 'undefined') {
        alert('Socket disconnected!');
        return null;
    }
    $.getJSON(urlNextTurn, function(result) {
        unselectArmy();
        changeTurn(result.playerId, result.color);
        wsTurn(result.playerId, result.color);
    });
}

function sendMove(movesSpend) {
    if(movesSpend == 0) {
        return null;
    }
    if(typeof socket == 'undefined') {
        alert('Socket disconnected!');
        return null;
    }
    unselectArmy();
    if(unselectedArmy.x == newX && unselectedArmy.y == newY) {
        return null;
    }
    lock = true;
    var castleId = isEnemyCastle(newX, newY)
    if(castleId) {
        var vectorLenth = getVectorLenth(unselectedArmy.x, unselectedArmy.y, newX, newY);
        if(vectorLenth >= 80) {
            lock = false;
            return null;
        }
        if(unselectedArmy.moves < (movesSpend + 1)) {
            console.log(movesSpend);
            lock = false;
            return null;
        }
        $.getJSON(urlFightCastle + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/cid/' + castleId, function(result) {
            var enemyArmies = getEnemyCastleGarrison(castleId);
            if(result.victory) {
                deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
                players[my.color].armies['army'+unselectedArmy.armyId] = new army(result, my.color);
                newX = players[my.color].armies['army'+unselectedArmy.armyId].x;
                newY = players[my.color].armies['army'+unselectedArmy.armyId].y;
                wsArmyAdd(unselectedArmy.armyId);

                // delete enemy - find enemy at position?
                for(i in enemyArmies) {
                    deleteArmy('army' + enemyArmies[i].armyId, enemyArmies[i].color);
                    wsArmyDelete(enemyArmies[i].armyId, enemyArmies[i].color);
                }

                wsCastleOwner(castleId, my.color);
                castleOwner(castleId, my.color);
            } else {
                for(i in enemyArmies) {
                    wsArmyAdd(enemyArmies[i].armyId);
                    getAddArmy(enemyArmies[i].armyId);
                    console.log(enemyArmies[i]);
                }
                deleteArmy('army' + unselectedArmy.armyId, my.color);
                wsArmyDelete(unselectedArmy.armyId, my.color);
            }
            battleM(result.battle, unselectedArmy, enemyArmies);
            lock = false;
        });
    } else if(selectedEnemyArmy && selectedEnemyArmy.x == newX && selectedEnemyArmy.y == newY) {
        var vectorLenth = getVectorLenth(unselectedArmy.x, unselectedArmy.y, newX, newY);
        if(vectorLenth >= 80) {
            lock = false;
            unselectEnemyArmy();
            return null;
        }
        if(unselectedArmy.moves < (movesSpend + 1)) {
            console.log(movesSpend);
            lock = false;
            unselectEnemyArmy();
            return null;
        }
        $.getJSON(urlFightArmy + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/eid/' + selectedEnemyArmy.armyId, function(result) {
            if(result.victory == true) {
                deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
                players[my.color].armies['army'+unselectedArmy.armyId] = new army(result, my.color);
                newX = players[my.color].armies['army'+unselectedArmy.armyId].x;
                newY = players[my.color].armies['army'+unselectedArmy.armyId].y;
                wsArmyAdd(unselectedArmy.armyId);

                deleteArmyByPosition(newX, newY, selectedEnemyArmy.color);
                wsArmyDelete(selectedEnemyArmy.armyId, selectedEnemyArmy.color);

//                 selectArmy(players[my.color].armies['army'+unselectedArmy.armyId]);
            } else {
                deleteArmyByPosition(unselectedArmy.x, unselectedArmy.y, my.color);
                wsArmyDelete(unselectedArmy.armyId, my.color);
                getAddArmy(selectedEnemyArmy.armyId);
                wsArmyAdd(selectedEnemyArmy.armyId);
            }

            battleM(result.battle, unselectedArmy, {0:selectedEnemyArmy});
            unselectEnemyArmy();
            lock = false;
        });
    } else {
        $.getJSON(urlMove + '/aid/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY, function(result) {
            if(result) {
                walkM(result);
            }
        });
    }
    return true;
}

function startMyTurn() {
    $.getJSON(urlStartMyTurn, function(result) {
        wsPlayerArmies(my.color);
        $('#gold').html('Gold: '+result['gold']);
        $('#costs').html('Costs: '+result['costs']);
        $('#income').html('Income: '+result['income']);
        for(i in result['armies']) {
            players[my.color].armies[i] = new army(result['armies'][i], my.color);
        }
        lock = false;
    });
}

function getAddArmy(armyId) {
    $.getJSON(urlAddArmy+'/armyId/'+armyId, function(result) {
        if(typeof result.armyId != 'undefined') {
            players[result.color].armies['army' + result.armyId] = new army(result, result.color);
        }
    });
}

function setProduction(castleId) {
    var unitId = getUnitId($('input:radio[name=production]:checked').val());
    if(!unitId) {
        return null;
    }
    $.getJSON(urlSetProduction+'/castleId/'+castleId+'/unitId/'+unitId, function(result) {
        if(result.set) {
            $('.message').remove();
            castles[castleId].currentProduction = unitId;
        }
    });
}

function getPlayerArmies(color){
    $.getJSON(urlGetPlayerArmies+'/color/'+color, function(result) {
        for(i in result){
            players[color].armies[i] = new army(result[i], color);
        }
    });
}

function splitArmy(armyId){
    var s = '';
    $('.message input[type="checkbox"]:checked').each(function() {
        if(s){
            s += ',';
        }
        s += $(this).val();
    });
    $.getJSON(urlSplitArmy+'/aid/'+armyId+'/s/'+s, function(result) {
        setParentArmyId(armyId);
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        selectArmy(players[my.color].armies['army'+result.armyId]);
        wsArmyAdd(selectedArmy.armyId);
        removeM();
    });
}

