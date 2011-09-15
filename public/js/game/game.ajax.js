function computerA(){
    if(!players[turn.color].computer){
        return null;
    }
    $.getJSON(urlComputer, function(result) {
        console.log(result);
    });
}

function nextTurnA() {
    if(my.turn){
        setlock();
        if(!lWSC.isLoggedIn()){
            alert('Socket disconnected!');
            return null;
        }
        $.getJSON(urlNextTurn, function(result) {
            unselectArmy();
            if(typeof result.win != 'undefined' && result.win){
                turnOff();
                winM();
            }else{
                changeTurn(result.color, result.nr);
            }
            wsTurn();
        });

    }
}

function getTurnA() {
    $.getJSON(urlGetTurn, function(result) {
        unselectArmy();
        if(result.lost){
            lostM();
        }else{
            changeTurn(result.color, result.nr);
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
    if(selectedArmy.moves == 0){
        unselectArmy();
        simpleM('Not enough moves left.');
        return null;
    }
    if(!my.turn){
        return null;
    }
    if(!lWSC.isLoggedIn()){
        alert('Socket disconnected!');
        return null;
    }
    tmpUnselectArmy();
    if(unselectedArmy.x == newX && unselectedArmy.y == newY) {
        return null;
    }
    setlock();
    var neutralCastleId = null;
    var enemyCastleId = null;
    var castleId = isEnemyCastle(newX, newY);
    if(castleId || (selectedEnemyArmy && selectedEnemyArmy.x == newX && selectedEnemyArmy.y == newY)){
        var vectorLength  = getVectorLength(unselectedArmy.x, unselectedArmy.y, newX, newY);
        if(vectorLength >= 80) {
            unlock();
            unselectEnemyArmy();
            return null;
        }
        if(castleId){
            if(unselectedArmy.moves < (movesSpend + 2)) {
                simpleM('Not enough moves left.');
                unlock();
                unselectEnemyArmy();
                return null;
            }
            //            var newArmyPosition = checkCastleVectorLength(castleId);
//            if(!newArmyPosition){
//                unlock();
//                return null;
//            }
            if(castles[castleId].color){
                enemyCastleId = castleId;
            }else{
                neutralCastleId = castleId;
            }
            if(neutralCastleId){
                $.getJSON(urlFightNeutralCastle + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/cid/' + castleId, function(result) {
                    var enemyArmies = new Array();
                    enemyArmies[0] = getNeutralCastleGarrison();
                    if(result.victory) {
                        myArmyWin(result);
                        wsCastle(castleId);
                        castleOwner(castleId, my.color);
                    } else {
                        deleteArmy('army' + unselectedArmy.armyId, my.color);
                        wsArmy(unselectedArmy.armyId);
                    }
                    handleParentArmy();
                    wsBattle(result.battle,unselectedArmy, enemyArmies);
                    battleM(result.battle, unselectedArmy, enemyArmies);
                    unlock();
                });
            } else if(enemyCastleId){
                $.getJSON(urlFightEnemyCastle + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/cid/' + castleId, function(result) {
                    var enemyArmies = getEnemyCastleGarrison(castleId);
                    if(result.victory) {
                        myArmyWin(result);
                        for(i in enemyArmies) {
                            deleteArmy('army' + enemyArmies[i].armyId, enemyArmies[i].color);
                            wsArmy(enemyArmies[i].armyId);
                        }
                        wsCastle(castleId);
                        castleOwner(castleId, my.color);
                    } else {
                        for(i in enemyArmies) {
                            wsArmy(enemyArmies[i].armyId);
                            getArmyA(enemyArmies[i].armyId);
                        }
                        deleteArmy('army' + unselectedArmy.armyId, my.color);
                        wsArmy(unselectedArmy.armyId);
                    }
                    handleParentArmy();
                    wsBattle(result.battle,unselectedArmy,enemyArmies);
                    battleM(result.battle, unselectedArmy, enemyArmies);
                    unlock();
                });
            }
        } else if(selectedEnemyArmy) {
            if(unselectedArmy.moves < (getTerrain(selectedEnemyArmy.fieldType, selectedEnemyArmy)[1] + 1)) {
                simpleM('Not enough moves left.');
                unlock();
                unselectEnemyArmy();
                return null;
            }
            $.getJSON(urlFightArmy + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/eid/' + selectedEnemyArmy.armyId, function(result) {
                if(result.victory == true) {
                    myArmyWin(result);
                    deleteArmyByPosition(newX, newY, selectedEnemyArmy.color);
                    wsArmy(selectedEnemyArmy.armyId);
                } else {
                    deleteArmy('army' + unselectedArmy.armyId, my.color, 1);
                    wsArmy(unselectedArmy.armyId);
                    getArmyA(selectedEnemyArmy.armyId);
                    wsArmy(selectedEnemyArmy.armyId);
                }
                handleParentArmy();
                wsBattle(result.battle,unselectedArmy,{0:selectedEnemyArmy});
                battleM(result.battle, unselectedArmy, {0:selectedEnemyArmy});
                unselectEnemyArmy();
                unlock();
            });
        }
    } else {
        if(movesSpend === null) {
            unlock();
            return null;
        }
        $.getJSON(urlMove + '/aid/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY, function(result) {
            if(result) {
                var res = result;
//                 console.log(result.path);
                walk(res);
            }
        });
    }
    return true;
}

function getArmyA(armyId, center) {
    $.getJSON(urlAddArmy+'/armyId/'+armyId, function(result) {
        if(typeof result.armyId != 'undefined') {
            if(typeof players[result.color].armies['army' + result.armyId] != 'undefined'){
                armyFields(players[result.color].armies['army' + result.armyId]);
            }
            players[result.color].armies['army' + result.armyId] = new army(result, result.color);
            if(center == 1){
                removeM();
                zoomer.lensSetCenter(players[result.color].armies['army' + result.armyId].x, players[result.color].armies['army' + result.armyId].y);
            }
//            clearPlayerArmiesTrash();
        }
    });
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
        return null;
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

function getPlayerArmiesA(color){
    $.getJSON(urlGetPlayerArmies+'/color/'+color, function(result) {
//        clearPlayerArmiesTrash();
        for(i in result){
            players[color].armies[i] = new army(result[i], color);
        }
    });
}

function joinArmyA(armyId1, armyId2){
    $.getJSON(urlJoinArmy+'/aid1/'+armyId1+'/aid2/'+armyId2, function(result) {
        unsetParentArmy();
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        if(armyId1 != result.armyId){
            wsArmy(armyId1, false);
        }
        if(armyId2 != result.armyId){
            wsArmy(armyId2, false);
        }
        wsArmy(result.armyId, true);
        removeM();
    });
}

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
    $.getJSON(urlSplitArmy+'/aid/'+armyId+'/s/'+s+'/h/'+h, function(result) {
        setParentArmy(players[my.color].armies['army'+armyId]);
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        selectArmy(players[my.color].armies['army'+result.armyId]);
        wsArmy(selectedArmy.armyId, true);
        removeM();
    });
}

function castleBuildDefenseA(){
    var castleId = $('input[name=defense]:checked').val();
    if(!castleId) {
        return null;
    }
    $.getJSON(urlCastleBuild+'/cid/'+castleId, function(result) {
        if(result.castleId == castleId){
            wsCastle(castleId);
            castleUpdate(result);
            castleOwner(result.castleId, result.color);
            removeM();
            goldUpdate(result.gold);
        }
    });
}

function razeCastleA(){
    var castleId = $('input[name=raze]:checked').val();
    if(!castleId) {
        return null;
    }
    $.getJSON(urlCastleRaze+'/cid/'+castleId, function(result) {
        if(result.castleId == castleId){
            wsCastle(castleId);
            castleUpdate(result);
            castleOwner(result.castleId, result.color);
            removeM();
            goldUpdate(result.gold);
        }
    });
}

function getCastleA(castleId){
    $.getJSON(urlCastleGet+'/cid/'+castleId, function(result) {
        if(result.castleId == castleId){
            castleUpdate(result);
            castleOwner(result.castleId, result.color);
        }
    });
}

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

function searchRuinsA(){
    if(!my.turn){
        return null;
    }
    if(selectedArmy == null){
        return null;
    }
    unselectArmy();
    $.getJSON(urlSearchRuins+'/aid/'+unselectedArmy.armyId, function(result) {
        wsGetRuin(getRuinId(unselectedArmy));
        ruinUpdate(result.ruinId, 1)
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        switch(result.find[0]){
            case 'gold':
                var gold = result.find[1] + parseInt($('#gold').html());
                goldUpdate(gold);
                simpleM('You have found '+result.find[1]+' gold.');
                break;
            case 'death':
                simpleM('You have found death.');
                wsArmy(result.armyId);
                break
            case 'alies':
                simpleM(result.find[1]+' alies joined your army.');
                wsArmy(result.armyId);
                break
            case 'null':
                simpleM('You have found nothing.');
                break
            case 'artefact':
                simpleM('You have found an ancient artefact.');
                wsArmy(result.armyId, true);
                break
            case 'empty':
                simpleM('Ruins are empty.');
                break;

        }
    });
}

function getRuinA(ruinId){
    $.getJSON(urlGetRuins+'/rid/'+ruinId, function(result) {
        ruinUpdate(ruinId, result.empty);
    });
}

function addTowerA(towerId){
    if(!my.turn){
        return null;
    }
    $.getJSON(urlTowerAdd+'/tid/'+towerId, function() {
        wsAddTower(towerId);
    });
}

function getTowerA(towerId){
    $.getJSON(urlTowerGet+'/tid/'+towerId, function(result) {
        changeEnemyTower(result.towerId, result.color);
    });
}