function startWebSocket(){
    ws = new WebSocket(wsURL);

    ws.onopen = function() {
        wsClosed = false;
        $("#wsStatus") . html("connected");
        wsOpen();
    };

    ws.onmessage = function(e) {
        var r=$.parseJSON( e.data );

        if(typeof r['type'] != 'undefined'){

            switch(r.type){

                case 'error':
                    simpleM(r.msg);
                    unlock();
                    break;

                case 'move':
                    //                    console.log(r);
                    removeM();
                    move(r);
                    break;

                case 'computer':
                    //                    console.log(r);
                    removeM();

                    if(typeof r.path != 'undefined' && r.path){
                        move(r, 1);
                    }else{
                        wsComputer();
                    }
                    break;

                case 'computerStart':
                    computerArmiesUpdate(r.armies, r.color);
                    break;

                case 'computerGameover':
                    console.log(r);
                    wsComputer();
                    break;

                case 'nextTurn':
                    console.log(r);
                    unselectArmy();
                    if(r.lost){
                        lostM(r.color);
                    }else if(typeof r.win != 'undefined'){
                        winM(color);
                    }else{
                        changeTurn(r.color, r.nr);
                        wsComputer();
                    }
                    break;

                case 'startTurn':
                    console.log(r);
                    if(typeof r.gameover != 'undefined'){
                        lostM(r.color);
                    }else if(r.color==my.color){
                        goldUpdate(r.gold);
                        $('#costs').html(r.costs);
                        $('#income').html(r.income);
                        unlock();
                    }

                    for(i in r.armies) {
                        players[r.color].armies[i] = new army(r.armies[i], r.color);
                    }
                    for(i in r.castles){
                        updateCastleCurrentProductionTurn(i, r.castles[i].productionTurn);
                    }
                    break;

                case 'ruin':
                    //                    console.log(r);
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

                //                case 'armies':
                //                    for(i in r.data){
                //                        players[r.color].armies[i] = new army(r.data[i], r.color);
                //                    }
                //                    break;

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
                    //                    console.log(r);
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
                    if(loading){
                        startGame();
                        loading = false;
                    }

                    webSocketOpenA(r.wssuid);
                    break;

                case 'chat':
                    if(r.msg){
                        titleBlink('Incoming chat!');
                        chat(r.color,r.msg,makeTime());
                    }
                    break;

                case 'castle':
                    castleUpdate(r);
                    castleOwner(r.castleId, r.color);
                    if(r.color==my.color){
                        removeM();
                        goldUpdate(r.gold);
                    }
                    break;

                default:
                    console.log(r);

            }
        }
    };

    ws.onclose = function() {
        wsClosed = true;
        $("#wsStatus") . html("connection closed");
        setTimeout ( 'startWebSocket()', 1000 );
    };

}

function wsCastleBuildDefense(){
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

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
        accessKey: accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsRazeCastle() {
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

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
        accessKey: accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsNextTurn() {
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var token = {
        type: 'nextTurn',
        gameId: gameId,
        playerId: my.id,
        accessKey: accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsStartMyTurn(){
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var token = {
        type: 'startTurn',
        gameId: gameId,
        playerId: my.id,
        accessKey: accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsChat() {
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var msg = $('#msg').val();
    $('#msg').val('');
    if(msg){
        chat(my.color,msg,makeTime());

        var token = {
            type: 'chat',
            data: msg,
            gameId: gameId,
            playerId: my.id,
            accessKey: accessKey
        };

        ws.send(JSON.stringify(token));
    }
}

//function wsPlayerArmies(color){
//    if(wsClosed){
//        simpleM('Sorry, server is disconnected.');
//        return;
//    }
//
//    var token = {
//        type: 'armies',
//        data:{
//            color:color
//        },
//        gameId: gameId,
//        playerId: my.id,
//        accessKey: accessKey
//    };
//
//    ws.send(JSON.stringify(token));
//}

function wsArmyMove(movesSpend) {
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

    if(selectedArmy.moves == 0){
        unselectArmy();
        simpleM('Not enough moves left.');
        return;
    }

    if(movesSpend === null){
        unselectArmy();
        return;
    }

    if(!my.turn){
        simpleM('It is not your turn.');
        return;
    }

    var x = newX/40;
    var y = newY/40;

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
        accessKey: accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsSplitArmy(armyId) {
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

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
        accessKey: accessKey,
        data: {
            armyId:armyId,
            s:s,
            h:h
        }
    };

    ws.send(JSON.stringify(token));
}

function wsDisbandArmy() {
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

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
        accessKey: accessKey,
        data: {
            armyId:unselectedArmy.armyId,
            x:unselectedArmy.x,
            y:unselectedArmy.y
        }
    };

    ws.send(JSON.stringify(token));
}

function wsHeroResurrection(castleId) {
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }


    if(!my.turn){
        return;
    }
    unselectArmy();

    var token = {
        type: 'heroResurrection',
        gameId: gameId,
        playerId: my.id,
        accessKey: accessKey,
        data: {
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsJoinArmy(armyId){
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }


    if(!my.turn){
        return;
    }

    var token = {
        type: 'joinArmy',
        gameId: gameId,
        playerId: my.id,
        accessKey: accessKey,
        data: {
            armyId:armyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsSearchRuins(){
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

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
        accessKey: accessKey,
        data: {
            armyId:unselectedArmy.armyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsComputer(){
    if(wsClosed){
        simpleM('Sorry, server is disconnected.');
        return;
    }

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
        accessKey: accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsOpen(){
    var token = {
        type: 'open'
    };

    ws.send(JSON.stringify(token));
}
