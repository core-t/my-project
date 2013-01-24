function startMyTurnA() {
    $.getJSON(urlStartMyTurn, function(result) {
        if(result['gameover']){
            lostM();
        }else{
            wsPlayerArmies(my.color);
            goldUpdate(result['gold']);
            $('#costs').html(result['costs']);
            $('#income').html(result['income']);
            for(i in result['armies']) {
                players[my.color].armies[i] = new army(result['armies'][i], my.color);
            }
            for(i in result.castles){
                updateCastleCurrentProductionTurn(i, result.castles[i].productionTurn);
            }
            unlock();
        }
    });
}

function moveA(movesSpend) {
    var x = newX/40;
    var y = newY/40;
    if(selectedArmy.moves == 0){
        unselectArmy();
        simpleM('Not enough moves left.');
        return;
    }
    if(!my.turn){
        return;
    }
    tmpUnselectArmy();
    if(unselectedArmy.x == x && unselectedArmy.y == y) {
        return;
    }
    setlock();
    var castleId = isEnemyCastle(x, y);
    if(castleId || (selectedEnemyArmy && selectedEnemyArmy.x == x && selectedEnemyArmy.y == y)){
        var vectorLength = getVectorLength(unselectedArmy.x, unselectedArmy.y, x, y);
        if(vectorLength >= 80) {
            unlock();
            unselectEnemyArmy();
            return;
        }
        if(castleId){
            if(unselectedArmy.moves < (movesSpend + 2)) {
                simpleM('Not enough moves left.');
                unlock();
                unselectEnemyArmy();
                return;
            }
            if(castles[castleId].color){
                wsFightEnemyCastle(unselectedArmy.armyId, x, y, castleId);
            }else{
                wsFightNeutralCastle(unselectedArmy.armyId, x, y, castleId);
            }
        } else if(selectedEnemyArmy) {
            if(unselectedArmy.moves < (getTerrain(selectedEnemyArmy.fieldType, selectedEnemyArmy)[1] + 1)) {
                simpleM('Not enough moves left.');
                unlock();
                unselectEnemyArmy();
                return;
            }
            wsFightEnemy(unselectedArmy.armyId, x, y, selectedEnemyArmy.armyId);
        }
    } else {
        if(movesSpend === null) {
            unlock();
            return;
        }
        wsArmyMove(x, y, unselectedArmy.armyId);
    }
}

function setProductionA(castleId) {
    var unitId
    var production = $('input:radio[name=production]:checked').val();
    if(production == 'stop'){
        unitId = -1;
    }else{
        unitId = getUnitId(production);
    }

    if(!unitId) {
        return;
    }
    if(castles[castleId].currentProduction == unitId){
        return;
    }
    $.getJSON(urlSetProduction+'/castleId/'+castleId+'/unitId/'+unitId, function(result) {
        if(result.set) {
            if(unitId == -1){
                $('#castle'+castleId).html('');
            }else{
                $('#castle'+castleId).html($('<img>').attr('src','../img/game/castle_production.png').css('float','right'));
            }
            $('.message').remove();
            castles[castleId].currentProduction = unitId;
            castles[castleId].currentProductionTurn = 0;
        }
    });
}

function addTowerA(towerId){
    $.getJSON(urlTowerAdd+'/tid/'+towerId+'/c/'+turn.color)
}

function webSocketOpen(wssuid){
    $.getJSON(urlWebSocketOpen+'/wssuid/'+wssuid);
}