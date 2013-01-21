function computerA(){
    if(!my.game){
        return null;
    }
    if(!players[turn.color].computer){
        return null;
    }
    $.getJSON(urlComputer, function(result) {
        console.log(result);
        removeM();
        if(typeof result.action != 'undefined'){
            switch(result.action){
                case 'continue':
                    if(typeof result.oldArmyId == 'undefined'){
                        //                        wsPlayerArmies(turn.color);
                        //                        $.when(getPlayerArmiesA(turn.color)).then(computerA());
                        $.when(wsPlayerArmiesA(turn.color)).then(computerA());
                    }else if(typeof result.path != 'undefined'){
                        //                        waitOn();
                        enemyWalk(result);
                    }else{
                        computerA();
                    }
                    break;
                case 'end':
                    changeTurn(result.color, result.nr);
                    if(players[result.color].computer){
                        computerA();
                    }
                    break;
                case 'gameover':
                    computerA();
                    break;
            }
        }
    //        console.log(result);
    });
}

//function sleep(){
//    console.log('s');
//    if(!wait){
//        setTimeout('computerA()', 200);
//    }else{
//        setTimeout('sleep()', 500);
//    }
//}

function nextTurnA() {
    if(my.turn){
        setlock();
        $.getJSON(urlNextTurn, function(result) {
            unselectArmy();
            if((typeof result.win != 'undefined') && result.win){
                turnOff();
                winM();
            }else{
                changeTurn(result.color, result.nr);
                computerA();
            }
        //            wsTurn();
        });

    }
    return null;
}

function getTurnA() {
    $.getJSON(urlGetTurn, function(result) {
        unselectArmy();
        if(result.lost){
            lostM();
        }else{
            changeTurn(result.color, result.nr);
            computerA();
        }
    });
}

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
        var vectorLength  = getVectorLength(unselectedArmy.x, unselectedArmy.y, x, y);
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
                wsFightNeutralCastle(unselectedArmy.armyId, x, y, castleId);
            }else{
                wsFightEnemyCastle(unselectedArmy.armyId, x, y, castleId);
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

//function getArmyA(armyId, center) {
//    $.getJSON(urlAddArmy+'/armyId/'+armyId, function(result) {
//        if(typeof result.armyId != 'undefined') {
//            if(typeof players[result.color].armies['army' + result.armyId] != 'undefined'){
//                armyFields(players[result.color].armies['army' + result.armyId]);
//            }
//            players[result.color].armies['army' + result.armyId] = new army(result, result.color);
//            if(center == 1){
//                removeM();
//                zoomer.lensSetCenter(players[result.color].armies['army' + result.armyId].x*40, players[result.color].armies['army' + result.armyId].y*40);
//            }
//        //            clearPlayerArmiesTrash();
//        }
//    });
//}

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

//function getPlayerArmiesA(color){
//    $.getJSON(urlGetPlayerArmies+'/color/'+color, function(result) {
//        //        clearPlayerArmiesTrash();
//        for(i in result){
//            players[color].armies[i] = new army(result[i], color);
//        }
//    });
//}

//function joinArmyA(armyId1, armyId2){
//    $.getJSON(urlJoinArmy+'/aid1/'+armyId1+'/aid2/'+armyId2, function(result) {
//        unsetParentArmy();
//        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
//        if(armyId1 != result.armyId){
//            wsArmy(armyId1, false);
//        }
//        if(armyId2 != result.armyId){
//            wsArmy(armyId2, false);
//        }
//        wsArmy(result.armyId, true);
//        removeM();
//    });
//}

function splitArmyA(armyId){
    var h = '';
    var s = '';
    $('.message input[type="checkbox"]:checked').each(function() {
        if($(this).attr('name') == 'heroId'){
            if(h){
                h += ',';
            }
            h += $(this).val();
        }else{
            if(s){
                s += ',';
            }
            s += $(this).val();
        }
    });
    wsSplitArmy(armyId, s, h);
}

//function castleBuildDefenseA(){
//    var castleId = $('input[name=defense]:checked').val();
//    if(!castleId) {
//        return;
//    }
//    $.getJSON(urlCastleBuild+'/cid/'+castleId, function(result) {
//        if(result.castleId == castleId){
//            wsCastle(castleId);
//            castleUpdate(result);
//            castleOwner(result.castleId, result.color);
//            removeM();
//            goldUpdate(result.gold);
//        }
//    });
//}

//function razeCastleA(){
//    var castleId = $('input[name=raze]:checked').val();
//    if(!castleId) {
//        return null;
//    }
//    $.getJSON(urlCastleRaze+'/cid/'+castleId, function(result) {
//        if(result.castleId == castleId){
//            wsCastle(castleId);
//            castleUpdate(result);
//            castleOwner(result.castleId, result.color);
//            removeM();
//            goldUpdate(result.gold);
//        }
//    });
//}

//function getCastleA(castleId){
//    $.getJSON(urlCastleGet+'/cid/'+castleId, function(result) {
//        if(result.castleId == castleId){
//            castleUpdate(result);
//            castleOwner(result.castleId, result.color);
//        }
//    });
//}

function disbandArmyA(){
    if(!my.turn){
        return null;
    }
    if(selectedArmy == null){
        return null;
    }
    unselectArmy();
    $.getJSON(urlDisbandArmy+'/aid/'+unselectedArmy.armyId, function(result) {
        if(result == 1){
            removeM();
            deleteArmy('army' + unselectedArmy.armyId, my.color);
            wsArmy(unselectedArmy.armyId, true);
        }
    });
}

function heroResurrectionA(castleId){
    if(!my.turn){
        return null;
    }
    unselectArmy();
    $.getJSON(urlHeroResurrection+'/cid/'+castleId, function(result) {
        if(result){
            removeM();
            players[my.color].armies['army'+result.armyId] = new army(result, my.color);
            wsArmy(result.armyId, true);
            goldUpdate(result.gold);
        }
    });
}

//function getRuinA(ruinId){
//    $.getJSON(urlGetRuins+'/rid/'+ruinId, function(result) {
//        ruinUpdate(ruinId, result.empty);
//    });
//}

function addTowerA(towerId){
    $.getJSON(urlTowerAdd+'/tid/'+towerId+'/c/'+turn.color)
}

function webSocketOpen(wssuid){
    $.getJSON(urlWebSocketOpen+'/wssuid/'+wssuid);
}