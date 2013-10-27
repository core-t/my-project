function startWebSocket() {
    ws = new WebSocket(wsURL + '/game');

    ws.onopen = function () {
        wsClosed = false;
        $("#wsStatus").html("connected");
        Websocket.open();
    };

    ws.onmessage = function (e) {
        var r = $.parseJSON(e.data);

        if (typeof r['type'] != 'undefined') {

            switch (r.type) {

                case 'error':
                    Message.simple(r.msg);
                    unlock();
                    break;

                case 'move':
                    console.log(r);
                    move(r);
                    break;

                case 'computer':
                    //                    console.log(r);
                    if (typeof r.path != 'undefined' && r.path) {
                        move(r, 1);
                    } else {
                        Websocket.computer();
                    }
                    break;

                case 'computerStart':
                    computerArmiesUpdate(r.armies, r.color);
                    break;

                case 'computerGameover':
                    console.log(r);
                    Websocket.computer();
                    break;

                case 'nextTurn':
                    console.log(r);
                    unselectArmy();
                    if (r.lost) {
                        Message.lost(r.color);
                    } else if (typeof r.win != 'undefined') {
                        winM(r.color);
                    } else {
                        changeTurn(r.color, r.nr);
                        Websocket.computer();
                    }
                    break;

                case 'startTurn':
                    //                    console.log(r);
                    if (typeof r.gameover != 'undefined') {
                        Message.lost(r.color);
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
                                Message.simple('You have found ' + r.find[1] + ' gold.');
                                break;
                            case 'death':
                                Message.simple('You have found death.');
                                break
                            case 'alies':
                                Message.simple(r.find[1] + ' alies joined your army.');
                                break
                            case 'null':
                                Message.simple('You have found nothing.');
                                break
                            case 'artifact':
                                Message.simple('You have found an ancient artifact - "' + artifacts[r.find[1]].name + '".');
                                Chest.update(r.color, r.find[1]);
                                break
                            case 'empty':
                                Message.simple('Ruins are empty.');
                                break;

                        }
                    }
                    break;

                case 'splitArmy':
                    Message.remove();
                    players[r.color].armies['army' + r.parentArmy.armyId] = new army(r.parentArmy, r.color);
                    setParentArmy(players[r.color].armies['army' + r.parentArmy.armyId]);
                    players[r.color].armies['army' + r.childArmy.armyId] = new army(r.childArmy, r.color);
                    if (my.color == turn.color) {
                        selectArmy(players[r.color].armies['army' + r.childArmy.armyId], 0);
                    } else {
                        zoomer.lensSetCenter(r.parentArmy.x * 40, r.parentArmy.y * 40);
                    }
                    break;

                case 'joinArmy':
                    //                    console.log(r);
                    Message.remove();
                    zoomer.lensSetCenter(r.army.x * 40, r.army.y * 40);
                    for (i in r.deletedIds) {
                        deleteArmy('army' + r.deletedIds[i].armyId, r.color);
                    }
                    players[r.color].armies['army' + r.army.armyId] = new army(r.army, r.color);
                    break;

                case 'disbandArmy':
                    if (typeof r.armyId != 'undefined' && r.color != 'undefined') {
                        Message.remove();
                        deleteArmy('army' + r.armyId, r.color);
                    }
                    break;

                case 'heroResurrection':
                    Message.remove();
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
                    chat(r.color, r.msg, makeTime());
                    break;

                case 'raze':
                    razeCastle(r.castleId);
                    if (r.color == my.color) {
                        Message.remove();
                        goldUpdate(r.gold);
                    }
                    break;

                case 'defense':
                    updateCastleDefense(r.castleId, r.defenseMod);
                    if (r.color == my.color) {
                        Message.remove();
                        goldUpdate(r.gold);
                    }
                    break;

                case 'production':
                    updateProduction(r.unitId, r.castleId);
                    break;

                case 'surrender':
                    unselectArmy();
                    for (i in players[r.color].armies) {
                        deleteArmy(i, r.color, 1);
                    }
                    for (i in players[r.color].castles) {
                        razeCastle(i);
                    }
                    Websocket.nextTurn();
                    break;

                case 'inventoryAdd':
                    $('#inventory').append(
                        $('<div>')
                            .html(artifacts[r.artifactId].name)
                            .click(function () {
                                Websocket.inventoryDel(selectedArmy.heroes[0].heroId);
                            })
                    );
                    for (a in players[my.color].armies) {
                        for (h in players[my.color].armies[a].heroes) {
                            if (players[my.color].armies[a].heroes[h].heroId == r.heroId) {
                                players[my.color].armies[a].heroes[h].artifacts.push({artifactId: r.artifactId});
                            }
                        }
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

Websocket = {
    open: function () {
        var token = {
            type: 'open',
            gameId: gameId,
            playerId: my.id,
            langId: langId,
            accessKey: accessKey
        };

        ws.send(JSON.stringify(token));
    },
    inventoryAdd: function (heroId, artifactId) {
        var token = {
            type: 'inventoryAdd',
            heroId: heroId,
            artifactId: artifactId
        };

        ws.send(JSON.stringify(token));
    },
    inventoryDel: function (heroId) {
        var token = {
            type: 'inventoryDel',
            heroId: heroId
        };

        ws.send(JSON.stringify(token));
    },
    production: function (castleId, name) {
        var unitId

        if (name == 'stop') {
            unitId = -1;
        } else {
            unitId = Unit.getId(name);
        }

        if (!unitId) {
            console.log('Brak unitId!');
            return;
        }

        var token = {
            type: 'production',
            castleId: castleId,
            unitId: unitId
        };

        ws.send(JSON.stringify(token));
    },
    addTower: function (towerId) {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'tower',
            towerId: towerId
        };

        ws.send(JSON.stringify(token));
    },
    surrender: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'surrender'
        };

        ws.send(JSON.stringify(token));
    },
    chat: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var msg = $('#msg').val();

        if (msg) {
            $('#msg').val('');

            var token = {
                type: 'chat',
                msg: msg
            };

            ws.send(JSON.stringify(token));
        }
    },
    computer: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        if (!my.game) {
            return
        }

        if (!players[turn.color].computer) {
            return;
        }

        if (stop) {
            return;
        }

        var token = {
            type: 'computer'
        };

        ws.send(JSON.stringify(token));
    },
    searchRuins: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
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
            type: 'ruin',
            armyId: unselectedArmy.armyId
        };

        ws.send(JSON.stringify(token));
    },
    fortifyArmy: function (armyId, fortify) {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        if (!my.turn) {
            return;
        }

        if (typeof fortify == 'undefined' || fortify) {
            fortify = 1;
        } else {
            fortify = 0;
        }

        var token = {
            type: 'fortifyArmy',
            armyId: armyId,
            fortify: fortify
        };

        ws.send(JSON.stringify(token));
    },
    joinArmy: function (armyId) {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }


        if (!my.turn) {
            return;
        }

        var token = {
            type: 'joinArmy',
            armyId: armyId
        };

        ws.send(JSON.stringify(token));
    },
    disbandArmy: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
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
            type: 'disbandArmy',
            armyId: unselectedArmy.armyId
        };

        ws.send(JSON.stringify(token));
    },
    armyMove: function (movesSpend) {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

//    if (selectedArmy.moves == 0) {
//        unselectArmy();
//        Message.simple('Not enough moves left.');
//        return;
//    }
//
////    if (movesSpend === null) {
////        unselectArmy();
////        return;
////    }

        if (!my.turn) {
            Message.simple('It is not your turn.');
            return;
        }

        var x = newX / 40;
        var y = newY / 40;

        tmpUnselectArmy();

        if (unselectedArmy.x == x && unselectedArmy.y == y) {
            return;
        }

        setLock();

        var token = {
            type: 'move',
            x: x,
            y: y,
            armyId: unselectedArmy.armyId
        };

        ws.send(JSON.stringify(token));
    },
    splitArmy: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
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
            type: 'splitArmy',
            armyId: selectedArmy.armyId,
            s: s,
            h: h
        };

        ws.send(JSON.stringify(token));
    },
    heroResurrection: function (castleId) {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }


        if (!my.turn) {
            return;
        }
        unselectArmy();

        var token = {
            type: 'heroResurrection',
            castleId: castleId
        };

        ws.send(JSON.stringify(token));
    },
    razeCastle: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var castleId = isMyCastle(selectedArmy.x, selectedArmy.y);

        if (!castleId) {
            Message.simple('No castle to destroy.');
            return;
        }

        var token = {
            type: 'razeCastle',
            armyId: selectedArmy.armyId
        };

        ws.send(JSON.stringify(token));
    },
    castleBuildDefense: function (castleId) {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'castleBuildDefense',
            castleId: castleId
        };

        ws.send(JSON.stringify(token));
    },
    startMyTurn: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'startTurn'
        };

        ws.send(JSON.stringify(token));
    },
    nextTurn: function () {
        if (wsClosed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'nextTurn'
        };

        ws.send(JSON.stringify(token));
    }

}