function startWebSocket() {
    ws = new WebSocket(wsURL + '/game');

    ws.onopen = function () {
        wsClosed = false;
        $("#wsStatus").html("connected");
        wsOpen();
    };

    ws.onmessage = function (e) {
        var r = $.parseJSON(e.data);

        if (typeof r['type'] != 'undefined') {

            switch (r.type) {

                case 'error':
                    simpleM(r.msg);
                    unlock();
                    break;

                case 'move':
                    //                    console.log(r);
                    move(r);
                    break;

                case 'computer':
                    //                    console.log(r);
                    if (typeof r.path != 'undefined' && r.path) {
                        move(r, 1);
                    } else {
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
                    //                    console.log(r);
                    unselectArmy();
                    if (r.lost) {
                        lostM(r.color);
                    } else if (typeof r.win != 'undefined') {
                        winM(color);
                    } else {
                        changeTurn(r.color, r.nr);
                        wsComputer();
                    }
                    break;

                case 'startTurn':
                    //                    console.log(r);
                    if (typeof r.gameover != 'undefined') {
                        lostM(r.color);
                    } else if (r.color == my.color) {
                        goldUpdate(r.gold);
                        $('#costs').html(r.costs);
                        $('#income').html(r.income);
                        unlock();
                    }

                    for (i in r.armies) {
                        players[r.color].armies[i] = new army(r.armies[i], r.color);
                    }
                    for (i in r.castles) {
                        updateCastleCurrentProductionTurn(i, r.castles[i].productionTurn);
                    }
                    break;

                case 'ruin':
                    //                    console.log(r);
                    zoomer.lensSetCenter(players[r.color].armies['army' + r.army.armyId].x * 40, players[r.color].armies['army' + r.army.armyId].y * 40);
                    players[r.color].armies['army' + r.army.armyId] = new army(r.army, r.color);
                    ruinUpdate(r.ruin.ruinId, r.ruin.empty);
                    if (my.color == r.color) {
                        switch (r.find[0]) {
                            case 'gold':
                                var gold = r.find[1] + parseInt($('#gold').html());
                                goldUpdate(gold);
                                simpleM('You have found ' + r.find[1] + ' gold.');
                                break;
                            case 'death':
                                simpleM('You have found death.');
                                break
                            case 'alies':
                                simpleM(r.find[1] + ' alies joined your army.');
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

                case 'splitArmy':
                    removeM();
                    players[r.color].armies['army' + r.parentArmy.armyId] = new army(r.parentArmy, r.color);
                    setParentArmy(players[r.color].armies['army' + r.parentArmy.armyId]);
                    players[r.color].armies['army' + r.childArmy.armyId] = new army(r.childArmy, r.color);
                    if (my.color == turn.color) {
                        selectArmy(players[r.color].armies['army' + r.childArmy.armyId]);
                    } else {
                        zoomer.lensSetCenter(r.parentArmy.x * 40, r.parentArmy.y * 40);
                    }
                    break;

                case 'joinArmy':
                    //                    console.log(r);
                    removeM();
                    zoomer.lensSetCenter(r.army.x * 40, r.army.y * 40);
                    for (i in r.deletedIds) {
                        deleteArmy('army' + r.deletedIds[i].armyId, r.color);
                    }
                    players[r.color].armies['army' + r.army.armyId] = new army(r.army, r.color);
                    break;

                case 'disbandArmy':
                    if (typeof r.armyId != 'undefined' && r.color != 'undefined') {
                        removeM();
                        deleteArmy('army' + r.armyId, r.color);
                    }
                    break;

                case 'heroResurrection':
                    removeM();
                    zoomer.lensSetCenter(r.data.army.x * 40, r.data.army.y * 40);
                    players[r.color].armies['army' + r.data.army.armyId] = new army(r.data.army, r.color);
                    if (my.color == turn.color) {
                        goldUpdate(r.data.gold);
                    }
                    break;

                case 'open':
                    lock = false;
                    if (loading) {
                        startGame();
                        loading = false;
                    }
                    break;

                case 'chat':
                    if (r.msg) {
                        titleBlink('Incoming chat!');
                        chat(r.color, r.msg, makeTime());
                    }
                    break;

                case 'castle':
                    castleUpdate(r);
                    castleOwner(r.castleId, r.color);
                    if (r.color == my.color) {
                        removeM();
                        goldUpdate(r.gold);
                    }
                    break;

                default:
                    console.log(r);

            }
        }
    };

    ws.onclose = function () {
        wsClosed = true;
        $("#wsStatus").html("connection closed");
        setTimeout('startWebSocket()', 1000);
    };

}

function wsNextTurn() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var token = {
        type:'nextTurn'
    };

    ws.send(JSON.stringify(token));
}

function wsStartMyTurn() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var token = {
        type:'startTurn'
    };

    ws.send(JSON.stringify(token));
}

function wsCastleBuildDefense() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var castleId = $('input[name=defense]:checked').val();
    if (!castleId) {
        return;
    }
    var token = {
        type:'castleBuildDefense',
        castleId:castleId
    };

    ws.send(JSON.stringify(token));
}

function wsRazeCastle() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var castleId = $('input[name=raze]:checked').val();
    if (!castleId) {
        return;
    }
    var token = {
        type:'razeCastle',
        castleId:castleId
    };

    ws.send(JSON.stringify(token));
}

function wsHeroResurrection(castleId) {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }


    if (!my.turn) {
        return;
    }
    unselectArmy();

    var token = {
        type:'heroResurrection',
        data:{
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsArmyMove(movesSpend) {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

//    if (selectedArmy.moves == 0) {
//        unselectArmy();
//        simpleM('Not enough moves left.');
//        return;
//    }
//
////    if (movesSpend === null) {
////        unselectArmy();
////        return;
////    }

    if (!my.turn) {
        simpleM('It is not your turn.');
        return;
    }

    var x = newX / 40;
    var y = newY / 40;

    tmpUnselectArmy();

    if (unselectedArmy.x == x && unselectedArmy.y == y) {
        return;
    }

    setlock();

    var token = {
        type:'move',
        x:x,
        y:y,
        armyId:unselectedArmy.armyId
    };

    ws.send(JSON.stringify(token));
}

function wsSplitArmy(armyId) {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    if (!my.turn) {
        return;
    }
    var h = '';
    var s = '';

    $('.message input[type="checkbox"]:checked').each(function () {
        if ($(this).attr('name') == 'heroId') {
            if (h) {
                h += ',';
            }
            h += $(this).val();
        } else {
            if (s) {
                s += ',';
            }
            s += $(this).val();
        }
    });

    var token = {
        type:'splitArmy',
        data:{
            armyId:armyId,
            s:s,
            h:h
        }
    };

    ws.send(JSON.stringify(token));
}

function wsDisbandArmy() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    if (!my.turn) {
        return;
    }
    if (selectedArmy == null) {
        return;
    }
    unselectArmy(1);

    var token = {
        type:'disbandArmy',
        data:{
            armyId:unselectedArmy.armyId,
            x:unselectedArmy.x,
            y:unselectedArmy.y
        }
    };

    ws.send(JSON.stringify(token));
}

function wsJoinArmy(armyId) {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }


    if (!my.turn) {
        return;
    }

    var token = {
        type:'joinArmy',
        data:{
            armyId:armyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFortifyArmy(armyId) {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var token = {
        type:'fortifyArmy',
        armyId:armyId
    };

    ws.send(JSON.stringify(token));
}

function wsSearchRuins() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    if (!my.turn) {
        return;
    }
    if (selectedArmy == null) {
        return;
    }
    unselectArmy();
    var token = {
        type:'ruin',
        data:{
            armyId:unselectedArmy.armyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsComputer() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    if (!my.game) {
        return
    }
    if (!players[turn.color].computer) {
        return;
    }

    var token = {
        type:'computer'
    };

    ws.send(JSON.stringify(token));
}

function wsChat() {
    if (wsClosed) {
        simpleM('Sorry, server is disconnected.');
        return;
    }

    var msg = $('#msg').val();

    if (msg) {
        $('#msg').val('');

        var token = {
            type:'chat',
            msg:msg
        };

        ws.send(JSON.stringify(token));
    }
}

function wsOpen() {
    var token = {
        type:'open',
        gameId:gameId,
        playerId:my.id,
        accessKey:accessKey
    };

    ws.send(JSON.stringify(token));
}