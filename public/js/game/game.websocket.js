$(document).ready(function() {
    ws.onmessage = function(e) {
        var edata=$.parseJSON( e.data );

        if(typeof edata['type'] != 'undefined'){

            switch(edata.type){

                case 'move':
                    walk(edata.data, edata.color);
                    break;

                case 'computer':
                    console.log(edata);
                    removeM();

                    if(typeof edata.data.oldArmyId == 'undefined'){
                        //                        wsPlayerArmies(turn.color);
                        //                        $.when(getPlayerArmiesA(turn.color)).then(computerA());
                        $.when(wsPlayerArmies(turn.color)).then(wsComputer());
                    }else if(typeof edata.data.path != 'undefined'){
                        //                        waitOn();
                        enemyWalk(edata.data);
                    }else{
                        wsComputer();
                    }
                    break;

                case 'computerEnd':
                    changeTurn(edata.data.color, edata.data.nr);
                    if(players[edata.data.color].computer){
                        wsComputer();
                    }
                    break;

                case 'computerGameover':
                    wsComputer();
                    break;

                case 'army':
                    if(typeof edata.data.armyId != 'undefined') {
                        if(typeof players[edata.data.color].armies['army' + edata.data.armyId] != 'undefined'){
                            armyFields(players[edata.data.color].armies['army' + edata.data.armyId]);
                        }
                        players[edata.data.color].armies['army' + edata.data.armyId] = new army(edata.data, edata.data.color);
                        if(edata.data.center == 1){
                            removeM();
                            zoomer.lensSetCenter(players[edata.data.color].armies['army' + edata.data.armyId].x*40, players[edata.data.color].armies['army' + edata.data.armyId].y*40);
                        }
                    }
                    break;

                case 'ruin':
                    zoomer.lensSetCenter(players[edata.color].armies['army' + edata.data.army.armyId].x*40, players[edata.color].armies['army' + edata.data.army.armyId].y*40);
                    players[edata.color].armies['army' + edata.data.army.armyId] = new army(edata.data.army, edata.color);
                    ruinUpdate(edata.data.ruin.ruinId, edata.data.ruin.empty);
                    if(my.color==edata.color){
                        switch(edata.data.find[0]){
                            case 'gold':
                                var gold = edata.data.find[1] + parseInt($('#gold').html());
                                goldUpdate(gold);
                                simpleM('You have found '+edata.data.find[1]+' gold.');
                                break;
                            case 'death':
                                simpleM('You have found death.');
                                break
                            case 'alies':
                                simpleM(edata.data.find[1]+' alies joined your army.');
                                break
                            case 'null':
                                simpleM('You have found nothing.');
                                break
                            case 'artefact':
                                simpleM('You have found an ancient artefact.');
                                break
                            case 'empty':
                                simpleM('Ruins are empty.');
                                break;

                        }
                    }
                    break;

                case 'armies':
                    for(i in edata.data){
                        players[edata.color].armies[i] = new army(edata.data[i], edata.color);
                    }
                    break;

                case 'splitArmy':
                    removeM();
                    setParentArmy(players[edata.color].armies['army'+edata.data.parentArmyId]);
                    players[edata.color].armies['army'+edata.data.childArmy.armyId] = new army(edata.data.childArmy, edata.color);
                    if(my.color==turn.color){
                        selectArmy(players[edata.color].armies['army'+edata.data.childArmy.armyId]);
                    }
                    break;

                case 'joinArmy':
                    unsetParentArmy();
                    zoomer.lensSetCenter(edata.data.army.x*40, edata.data.army.y*40);
                    players[edata.color].armies['army'+edata.data.army.armyId] = new army(edata.data.army, edata.color);
                    removeM();
                    break;

                case 'disbandArmy':
                    if(typeof edata.data.armyId != 'undefined'){
                        removeM();
                        zoomer.lensSetCenter(edata.data.x*40, edata.data.y*40);
                        deleteArmy('army' + edata.data.armyId, edata.color);
                    }
                    break;

                case 'heroResurrection':
                    removeM();
                    zoomer.lensSetCenter(edata.data.army.x*40, edata.data.army.y*40);
                    players[edata.color].armies['army'+edata.data.army.armyId] = new army(edata.data.army, edata.color);
                    if(my.color==turn.color){
                        goldUpdate(edata.data.gold);
                    }
                    break;

                case 'open':
                    webSocketOpen(edata.wssuid);
                    break;

                case 'chat':
                    if(edata.msg){
                        titleBlink('Incoming chat!');
                        chat(edata.color,edata.msg,makeTime());
                    }
                    break;

                case 'turn':
                    unselectArmy();
                    if(edata.data['lost']){
                        lostM();
                    }else{
                        //                        if(!data[3]){
                        //                            unset(data[3]);
                        //                        }
                        changeTurn(edata.data['color'], edata.data['nr']);
                        wsComputer();
                    }
                    break;

                case 'fightNeutralCastle':
                    var enemyArmies = {
                        0: getNeutralCastleGarrison()
                    };

                    var x = players[edata.color].armies['army'+edata.data.armyId].x;
                    var y = players[edata.color].armies['army'+edata.data.armyId].y;

                    zoomer.lensSetCenter(x*40, y*40);

                    if(edata.data.victory) {
                        players[edata.color].armies['army'+edata.data.armyId] = new army(edata.data, edata.color);
                        if(edata.color==my.color){
                            newX = x;
                            newY = y;
                        }
                        castleOwner(edata.data.castleId, edata.color);
                    } else {
                        deleteArmy('army' + edata.data.armyId, edata.color);
                    }
                    battleM(edata.data.battle, players[edata.color].armies['army'+edata.data.armyId], enemyArmies);
                    if(edata.color==my.color){
                        unlock();
                    }
                    break;

                case 'fightEnemyCastle':
                    var enemyArmies = getEnemyCastleGarrison(edata.data.castleId);
                    if(edata.data.victory) {
                        players[edata.color].armies['army'+edata.data.armyId] = new army(edata.data, edata.color);
                        if(edata.color==my.color){
                            newX = players[edata.color].armies['army'+edata.data.armyId].x;
                            newY = players[edata.color].armies['army'+edata.data.armyId].y;
                        }
                        for(i in enemyArmies) {
                            deleteArmy('army' + enemyArmies[i].armyId, enemyArmies[i].color);
                        }
                        castleOwner(edata.data.castleId, edata.color);
                    } else {
                        deleteArmy('army' + edata.data.armyId, edata.color);
                    }
                    battleM(edata.data.battle, players[edata.color].armies['army'+edata.data.armyId], enemyArmies);
                    if(edata.color==my.color){
                        unlock();
                    }
                    break;

                case 'fightEnemy':
                    console.log(edata);
                    if(edata.data.victory) {
                        players[edata.color].armies['army'+edata.data.armyId] = new army(edata.data, edata.color);
                        if(edata.color==my.color){
                            newX = players[edata.color].armies['army'+edata.data.armyId].x;
                            newY = players[edata.color].armies['army'+edata.data.armyId].y;
                        }
                        deleteArmyByPosition(edata.data.x, edata.data.y, edata.data.enemyColor);
                    } else {
                        players[edata.data.enemyColor].armies['army'+edata.data.enemyArmyId] = new army(edata.data, edata.color);
                        deleteArmy('army' + edata.data.armyId, edata.color, 1);
                    }
                    battleM(edata.data.battle, players[edata.color].armies['army'+edata.data.armyId], {
                        0:edata.data.enemyArmy
                    });
                    if(edata.color==my.color){
                        unselectEnemyArmy();
                        unlock();
                    }
                    break;

                case 'castle':
                    castleUpdate(edata.data);
                    castleOwner(edata.data.castleId, edata.data.color);
                    if(edata.data.color==my.color){
                        removeM();
                        goldUpdate(edata.data.gold);
                    }
                    break;

                default:
                    console.log(edata);

            }
        }
    };

});

function wsCastleBuildDefense(){
    var castleId = $('input[name=defense]:checked').val();
    if(!castleId) {
        return;
    }
    var token = {
        type: 'castleBuildDefense',
        data: {
            castleId:castleId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsRazeCastle() {
    var castleId = $('input[name=raze]:checked').val();
    if(!castleId) {
        return;
    }
    var token = {
        type: 'razeCastle',
        data: {
            castleId:castleId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsNextTurn() {
    var token = {
        type: 'turn',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsChat() {
    var msg = $('#msg').val();
    $('#msg').val('');
    if(msg){
        chat(my.color,msg,makeTime());

        var token = {
            type: 'chat',
            data: msg,
            gameId: gameId,
            playerId: my.id,
            color: my.color,
            accessKey: lAccessKey
        };

        ws.send(JSON.stringify(token));
    }
}

function wsPlayerArmies(color){
    var token = {
        type: 'armies',
        data:{
            color:color
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsArmyMove(x, y, armyId) {
    var token = {
        type: 'move',
        data:{
            x: x,
            y: y,
            armyId: armyId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsArmy(armyId, center) {
    var token = {
        type: 'army',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };
    if(center){
        token['data']={
            armyId: armyId,
            center: 1
        };
    }else{
        token['data']={
            armyId: armyId,
            center: 0
        };
    }
    ws.send(JSON.stringify(token));
}

function wsSplitArmy(armyId) {
    if(!my.turn){
        return;
    }
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
    var token = {
        type: 'splitArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            s:s,
            h:h
        }
    };

    ws.send(JSON.stringify(token));
}

function wsDisbandArmy() {
    if(!my.turn){
        return;
    }
    if(selectedArmy == null){
        return;
    }
    unselectArmy();

    var token = {
        type: 'disbandArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:unselectArmy.armyId,
            x:unselectArmy.x,
            y:unselectArmy.y
        }
    };

    ws.send(JSON.stringify(token));
}

function wsHeroResurrection(castleId) {
    if(!my.turn){
        return;
    }
    unselectArmy();

    var token = {
        type: 'heroResurrection',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsJoinArmy(armyId1, armyId2){
    var token = {
        type: 'joinArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId1:armyId1,
            armyId2:armyId2
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightNeutralCastle(armyId, x, y, castleId){
    var token = {
        type: 'fightNeutralCastle',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightEnemyCastle(armyId, x, y, castleId){
    var token = {
        type: 'fightEnemyCastle',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightEnemy(armyId, x, y, enemyArmyId){
    var token = {
        type: 'fightEnemy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            enemyArmyId:enemyArmyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsSearchRuins(){
    if(!my.turn){
        return;
    }
    if(selectedArmy == null){
        return;
    }
    unselectArmy();
    var token = {
        type: 'ruin',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:unselectedArmy.armyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsComputer(){
    if(!my.game){
        return
    }
    if(!players[turn.color].computer){
        return;
    }

    var token = {
        type: 'computer',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}
//function wsBattle(battle,army,armies){
//    var data = my.color+'.b.';
//    var tmp = '';
//    for(i in battle){
//        if(typeof battle[i].soldierId != 'undefined'){
//            if(tmp){
//                tmp += ',';
//            }
//            tmp += 's'+battle[i].soldierId;
//        }
//        if(typeof battle[i].heroId != 'undefined'){
//            if(tmp){
//                tmp += ',';
//            }
//            tmp += 'h'+battle[i].heroId;
//        }
//    }
//    data += tmp+'.'+army.color+','+army.armyId;
//    tmp = '';
//    if(typeof armies != 'undefined'){
//        if(typeof armies[0] != 'undefined' && typeof armies[0].color != 'undefined' && armies[0].color == 'neutral'){
//            tmp = 'n';
//        }else{
//            for(i in armies){
//                if(tmp){
//                    tmp += '|';
//                }
//                tmp += armies[i].armyId;
//            }
//            if(tmp){
//                tmp += ','+armies[i].color;
//            }
//        }
//    }
//    if(tmp){
//        data += '.'+tmp;
//    }
//    lWSC.channelPublish(channel,data);
//}