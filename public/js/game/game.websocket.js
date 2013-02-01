$(document).ready(function() {
    ws.onmessage = function(e) {
        var r=$.parseJSON( e.data );
        function clb(){};

        if(typeof r['type'] != 'undefined'){

            switch(r.type){

                case 'error':
                    simpleM(r.msg);
                    unlock();
                    break;

                case 'move':
                    //                    console.log(r);
                    removeM();
                    walk(r, r.attackerColor, r.deletedIds);
                    break;

                case 'computer':
                    console.log(r);
                    removeM();

                    if(typeof r.data.path != 'undefined' && r.data.path){
                        walk(r.data, r.data.attackerColor, r.data.deletedIds, 1);
                    }else{
                        wsComputer();
                    }
                    break;

                case 'computerStart':
                    computerArmiesUpdate(r.data.armies, r.data.color);
                    break;

                case 'computerEnd':
                    changeTurn(r.data.color, r.data.nr);
                    if(players[r.data.color].computer){
                        wsComputer();
                    }
                    break;

                case 'computerGameover':
                    console.log(r);
                    wsComputer();
                    break;

                case 'ruin':
                    console.log(r);
                    zoomer.lensSetCenter(players[r.color].armies['army' + r.data.army.armyId].x*40, players[r.color].armies['army' + r.data.army.armyId].y*40);
                    players[r.color].armies['army' + r.data.army.armyId] = new army(r.data.army, r.color);
                    ruinUpdate(r.data.ruin.ruinId, r.data.ruin.empty);
                    if(my.color==r.color){
                        switch(r.data.find[0]){
                            case 'gold':
                                var gold = r.data.find[1] + parseInt($('#gold').html());
                                goldUpdate(gold);
                                simpleM('You have found '+r.data.find[1]+' gold.');
                                break;
                            case 'death':
                                simpleM('You have found death.');
                                break
                            case 'alies':
                                simpleM(r.data.find[1]+' alies joined your army.');
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
                    for(i in r.data){
                        players[r.color].armies[i] = new army(r.data[i], r.color);
                    }
                    break;

                case 'splitArmy':
                    removeM();
                    players[r.color].armies['army'+r.data.parentArmy.armyId] = new army(r.data.parentArmy, r.color);
                    setParentArmy(players[r.color].armies['army'+r.data.parentArmy.armyId]);
                    players[r.color].armies['army'+r.data.childArmy.armyId] = new army(r.data.childArmy, r.color);
                    if(my.color==turn.color){
                        selectArmy(players[r.color].armies['army'+r.data.childArmy.armyId]);
                    }
                    else{
                        zoomer.lensSetCenter(r.data.parentArmy.x*40, r.data.parentArmy.y*40);
                    }
                    break;

                case 'joinArmy':
                    console.log(r);
                    removeM();
                    zoomer.lensSetCenter(r.army.x*40, r.army.y*40);
                    for(i in r.deletedIds){
                        deleteArmy('army' + r.deletedIds[i].armyId, r.color);
                    }
                    players[r.color].armies['army'+r.army.armyId] = new army(r.army, r.color);
                    break;

                case 'disbandArmy':
                    if(typeof r.data.armyId != 'undefined'){
                        removeM();
                        deleteArmy('army' + r.data.armyId, r.color);
                    }
                    break;

                case 'heroResurrection':
                    removeM();
                    zoomer.lensSetCenter(r.data.army.x*40, r.data.army.y*40);
                    players[r.color].armies['army'+r.data.army.armyId] = new army(r.data.army, r.color);
                    if(my.color==turn.color){
                        goldUpdate(r.data.gold);
                    }
                    break;

                case 'open':
                    lock = false;
                    startGame();

                    webSocketOpenA(r.wssuid);
                    break;

                case 'chat':
                    if(r.msg){
                        titleBlink('Incoming chat!');
                        chat(r.color,r.msg,makeTime());
                    }
                    break;

                case 'turn':
                    unselectArmy();
                    if(r.data['lost']){
                        lostM();
                    }else{
                        //                        if(!data[3]){
                        //                            unset(data[3]);
                        //                        }
                        changeTurn(r.data['color'], r.data['nr']);
                        wsComputer();
                    }
                    break;

                case 'castle':
                    castleUpdate(r.data);
                    castleOwner(r.data.castleId, r.data.color);
                    if(r.data.color==my.color){
                        removeM();
                        goldUpdate(r.data.gold);
                    }
                    break;

                default:
                    console.log(r);

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

function wsArmyMove(movesSpend) {

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

    var token = {
        type: 'move',
        data:{
            x: x,
            y: y,
            armyId: unselectedArmy.armyId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

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
    unselectArmy(1);

    var token = {
        type: 'disbandArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:unselectedArmy.armyId,
            x:unselectedArmy.x,
            y:unselectedArmy.y
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

function wsJoinArmy(armyId){
    if(!my.turn){
        return;
    }

    var token = {
        type: 'joinArmy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFight(armyId, x, y){
    var token = {
        type: 'fight',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y
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

function wsOpen(){
    var token = {
        type: "open",
        gameId: gameId,
        playerId: my.id,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}