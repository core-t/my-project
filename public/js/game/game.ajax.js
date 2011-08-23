function sendNextTurn() {
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

function getTurn() {
    $.getJSON(urlGetTurn, function(result) {
        unselectArmy();
        if(result.lost){
            lostM();
        }else{
            changeTurn(result.color, result.nr);
        }
    });
}

function startMyTurn() {
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
            unlock();
        }
    });
}

function sendMove(movesSpend) {
    if(selectedArmy.moves == 0){
        unselectArmy();
        simpleM('Not enough moves left.');
        return null;
    }
    if(!my.turn){
        return null;
    }
    if(movesSpend == 0) {
        return null;
    }
    if(!lWSC.isLoggedIn()){
        alert('Socket disconnected!');
        return null;
    }
    unselectArmy();
    if(unselectedArmy.x == newX && unselectedArmy.y == newY) {
        return null;
    }
    setlock();
    var castleId = isEnemyCastle(newX, newY)
    if(castleId) {
        var vectorLenth = getVectorLenth(unselectedArmy.x, unselectedArmy.y, newX, newY);
        if(vectorLenth >= 80) {
            unlock();
            return null;
        }
        if(unselectedArmy.moves < (movesSpend + 1)) {
            simpleM('Not enough moves left.');
            console.log(movesSpend);
            unlock();
            return null;
        }
        $.getJSON(urlFightCastle + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/cid/' + castleId, function(result) {
            var enemyArmies = getEnemyCastleGarrison(castleId);
            var neutral = true;
            if(castles[castleId].color){
                neutral = false;
            }
            if(result.victory) {
                deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
                players[my.color].armies['army'+unselectedArmy.armyId] = new army(result, my.color);
                newX = players[my.color].armies['army'+unselectedArmy.armyId].x;
                newY = players[my.color].armies['army'+unselectedArmy.armyId].y;
                wsArmyAdd(unselectedArmy.armyId);

                // delete enemy - find enemy at position?
                for(i in enemyArmies) {
                    deleteArmy('army' + enemyArmies[i].armyId, enemyArmies[i].color);
                    wsArmyAdd(enemyArmies[i].armyId);
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
                wsArmyAdd(unselectedArmy.armyId);
            }
            if(neutral){
                enemyArmies = new Array();
                enemyArmies[0] = getNeutralCastleGarrison();
            }
            wsBattle(result.battle,unselectedArmy,enemyArmies);
            battleM(result.battle, unselectedArmy, enemyArmies);
            unlock();
        });
    } else if(selectedEnemyArmy && selectedEnemyArmy.x == newX && selectedEnemyArmy.y == newY) {
        var vectorLenth = getVectorLenth(unselectedArmy.x, unselectedArmy.y, newX, newY);
        if(vectorLenth >= 80) {
            unlock();
            unselectEnemyArmy();
            return null;
        }
        if(unselectedArmy.moves < (movesSpend + 1)) {
            simpleM('Not enough moves left.');
            console.log(movesSpend);
            unlock();
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
                wsArmyAdd(selectedEnemyArmy.armyId);

//                 selectArmy(players[my.color].armies['army'+unselectedArmy.armyId]);
            } else {
                deleteArmyByPosition(unselectedArmy.x, unselectedArmy.y, my.color);
                wsArmyAdd(unselectedArmy.armyId);
                getAddArmy(selectedEnemyArmy.armyId);
                wsArmyAdd(selectedEnemyArmy.armyId);
            }
            wsBattle(result.battle,unselectedArmy,{0:selectedEnemyArmy});
            battleM(result.battle, unselectedArmy, {0:selectedEnemyArmy});
            unselectEnemyArmy();
            unlock();
        });
    } else {
        $.getJSON(urlMove + '/aid/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY, function(result) {
            if(result) {
                walk(result);
            }
        });
    }
    return true;
}

function getAddArmy(armyId) {
    $.getJSON(urlAddArmy+'/armyId/'+armyId, function(result) {
        if(typeof result.armyId != 'undefined') {
            players[result.color].armies['army' + result.armyId] = new army(result, result.color);
        }
    });
}

function setProduction(castleId) {
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

function getPlayerArmies(color){
    $.getJSON(urlGetPlayerArmies+'/color/'+color, function(result) {
        for(i in result){
            players[color].armies[i] = new army(result[i], color);
        }
    });
}

function splitArmy(armyId){
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
        setParentArmyId(armyId);
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        selectArmy(players[my.color].armies['army'+result.armyId]);
        wsArmyAdd(selectedArmy.armyId);
        removeM();
    });
}

function castleRaze(){
    var castleId = $('input[name=raze]:checked').val();
    if(!castleId) {
        return null;
    }
    $.getJSON(urlCastleRaze+'/cid/'+castleId, function(result) {
        if(result.castleId == castleId){
            castleUpdate(result);
            removeM();
            goldUpdate(result.gold);
        }
    });
}

function castleGet(castleId){
    $.getJSON(urlCastleGet+'/cid/'+castleId, function(result) {
        if(result.castleId == castleId){
            castleUpdate(result);
            castleOwner(result.castleId, result.color);
        }
    });
}

function disbandArmy(){
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
            wsArmyAdd(unselectedArmy.armyId);
        }
    });
}

function heroResurection(){
    if(!my.turn){
        return null;
    }
    unselectArmy();
    $.getJSON(urlHeroResurrection, function(result) {
        if(result){
            removeM();
            players[my.color].armies['army'+result.armyId] = new army(result, my.color);
            wsArmyAdd(result.armyId);
        }
    });
}

function searchRuins(){
    if(!my.turn){
        return null;
    }
    if(selectedArmy == null){
        return null;
    }
    unselectArmy();
    $.getJSON(urlSearchRuins+'/aid/'+unselectedArmy.armyId, function(result) {
        switch(result.find[0]){
            case 'gold':
                goldUpdate(result.find[1] + parseInt($('#gold').html()));
                simpleM('You have found '+result.find[1]+' gold.');
                break;
            case 'death':
                simpleM('You have found death.');
                players[my.color].armies['army'+result.armyId] = new army(result, my.color);
                wsArmyAdd(result.armyId);
                break
            case 'alies':
                simpleM(result.find[1]+' alies joined your army.');
                players[my.color].armies['army'+result.armyId] = new army(result, my.color);
                wsArmyAdd(result.armyId);
                break
            case 'null':
                simpleM('You have found nothing.');
                break
            case 'artefact':
                simpleM('You have found an ancient artefact.');
                players[my.color].armies['army'+result.armyId] = new army(result, my.color);
                wsArmyAdd(result.armyId);
                break

        }
    });
}
