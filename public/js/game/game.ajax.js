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
//    if(selectedArmy.moves == 0){
//        unselectArmy();
//        simpleM('Not enough moves left.');
//        return;
//    }

//    if(movesSpend === null){
//        unselectArmy();
//        return;
//    }

    if(!my.turn){
        simpleM('It is not your turn.');
        return;
    }
    tmpUnselectArmy();
    if(unselectedArmy.x == x && unselectedArmy.y == y) {
        return;
    }
    setlock();
    wsArmyMove(x, y, unselectedArmy.armyId);
//    var castleId = isEnemyCastle(x, y);
//    if(castleId || (selectedEnemyArmy && selectedEnemyArmy.x == x && selectedEnemyArmy.y == y)){
//        wsFight(unselectedArmy.armyId, x, y);
//    } else {
//        wsArmyMove(x, y, unselectedArmy.armyId);
//    }
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

function webSocketOpenA(wssuid){
    $.getJSON(urlWebSocketOpen+'/wssuid/'+wssuid);
}