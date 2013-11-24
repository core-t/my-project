Websocket = {
    close: true,
    init: function () {
        ws = new WebSocket(wsURL + '/game');

        ws.onopen = function () {
            this.closed = false;
            Websocket.open();
        };

        ws.onmessage = function (e) {
            var r = $.parseJSON(e.data);

            if (isSet(r['type'])) {

                switch (r.type) {

                    case 'error':
                        Sound.play('error');
                        Message.simple(r.msg);
                        unlock();
                        break;

                    case 'move':
                        Move.start(r);
                        break;

                    case 'computer':
                        if (isTruthful(r.path)) {
                            Move.start(r, 1);
                        } else {
                            Websocket.computer();
                        }
                        break;

                    case 'computerStart':
                        computerArmiesUpdate(r.armies, r.color);
                        break;

                    case 'nextTurn':
                        Army.deselect();
                        Turn.change(r.color, r.nr);
                        Websocket.computer();
                        break;

                    case 'startTurn':
                        Army.quitedArmies = {};

                        if (r.color == my.color) {
                            Sound.play('startturn');
                            goldUpdate(r.gold);
                            $('#costs').html(r.costs);
                            $('#income').html(r.income);
                            unlock();
                        }

                        for (i in r.armies) {
                            Army.init(r.armies[i], r.color);
                        }
                        for (i in r.castles) {
                            Castle.updateCurrentProductionTurn(i, r.castles[i].productionTurn);
                        }
                        break;

                    case 'ruin':
                        zoomer.lensSetCenter(r.army.x * 40, r.army.y * 40);
                        Army.init(r.army, r.color);
                        Ruin.update(r.ruin.ruinId, r.ruin.empty);
                        if (my.color == r.color) {
                            switch (r.find[0]) {
                                case 'gold':
                                    Sound.play('gold1');
                                    var gold = r.find[1] + parseInt($('#gold').html());
                                    goldUpdate(gold);
                                    Message.simple('You have found ' + r.find[1] + ' gold.');
                                    break;
                                case 'death':
                                    Sound.play('death');
                                    Message.simple('You have found death.');
                                    break
                                case 'allies':
                                    Sound.play('allies');
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
                        $('.ruinSearch').animate({'display': 'none'}, 1000, function () {
                            $('.ruinSearch').remove();
                        });
                        break;

                    case 'split':
                        Message.remove();
                        Army.init(r.parentArmy, r.color);
                        Army.init(r.childArmy, r.color);

                        Army.parent = players[r.color].armies[r.parentArmy.armyId];

                        if (my.color == Turn.color) {
                            Army.select(players[r.color].armies[r.childArmy.armyId], 0);
                        } else {
                            zoomer.lensSetCenter(r.parentArmy.x * 40, r.parentArmy.y * 40);
                        }
                        break;

                    case 'join':
                        Message.remove();
                        zoomer.lensSetCenter(r.army.x * 40, r.army.y * 40);
                        for (i in r.deletedIds) {
                            Army.delete(r.deletedIds[i].armyId, r.color);
                        }
                        Army.init(r.army, r.color);
                        break;

                    case 'disband':
                        if (isSet(r.armyId) && isSet(r.color)) {
                            Message.remove();
                            Army.delete(r.armyId, r.color);
                        }
                        break;

                    case 'resurrection':
                        Sound.play('resurrection');
                        Message.remove();
                        zoomer.lensSetCenter(r.data.army.x * 40, r.data.army.y * 40);
                        Army.init(r.data.army, r.color);
                        if (my.color == Turn.color) {
                            goldUpdate(r.data.gold);
                        }
                        break;

                    case 'open':
                        Gui.lock = false;
                        if (loading) {
                            startGame();
                            loading = false;
                        } else if (my.game && players[Turn.color].computer) {
                            setTimeout('Websocket.computer()', 1000);
                        }
                        break;

                    case 'chat':
                        chat(r.color, r.msg, makeTime());
                        break;

                    case 'raze':
                        $('#razeCastle').addClass('buttonOff');
                        Castle.raze(r.castleId);
                        if (r.color == my.color) {
                            Sound.play('gold1');
                            Message.remove();
                            goldUpdate(r.gold);
                        } else {
                            Sound.play('raze');
                        }
                        break;

                    case 'defense':
                        Castle.updateDefense(r.castleId, r.defenseMod);
                        if (r.color == my.color) {
                            Message.remove();
                            goldUpdate(r.gold);
                        }
                        break;

                    case 'production':
                        Castle.updateMyProduction(r.unitId, r.castleId, r.relocationCastleId);
                        break;

                    case 'surrender':
                        Army.deselect();
                        for (i in players[r.color].armies) {
                            Army.delete(i, r.color, 1);
                        }
                        for (i in players[r.color].castles) {
                            Castle.raze(i);
                        }
                        Websocket.nextTurn();
                        break;

                    case 'inventoryAdd':
                        $('#inventory').append(
                            $('<div>')
                                .html(artifacts[r.artifactId].name)
                                .click(function () {
                                    Websocket.inventoryDel(Army.selected.heroes[0].heroId);
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

                    case 'statistics':
                        castlesConquered = r.castlesConquered;
                        castlesDestroyed = r.castlesDestroyed;
                        heroesKilled = r.heroesKilled;
                        soldiersCreated = r.soldiersCreated;
                        soldiersKilled = r.soldiersKilled;
                        Message.statistics();
                        break;

                    case 'dead':
                        Message.simple(mapPlayersColors[r.color].longName + ' have been defeated');
                        break;

                    case 'end':
                        Turn.off();
                        Message.end();
                        break;

                    default:
                        console.log(r);

                }
            }
        };

        ws.onclose = function () {
            this.closed = true;
            setTimeout('Websocket.init()', 1000);
        };

    },
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
    production: function (castleId, unitId, relocationCastleId) {
        if (unitId == 'stop') {
            unitId = -1;
        }

        if (!unitId) {
            Sound.play('error');
            Message.simple('Error');
            return;
        }

        var token = {
            type: 'production',
            castleId: castleId,
            unitId: unitId,
            relocationCastleId: relocationCastleId
        };

        ws.send(JSON.stringify(token));
    },
    tower: function (towerId) {
        if (this.closed) {
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
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'surrender'
        };

        ws.send(JSON.stringify(token));
    },
    chat: function () {
        if (this.closed) {
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
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        if (!my.game) {
            return
        }

        if (!players[Turn.color].computer) {
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
    ruin: function () {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        if (!my.turn) {
            return;
        }
        if (Army.selected == null) {
            return;
        }

        Army.deselect();

        board.append($('<div>').addClass('ruinSearch').css({'top': 40 * Army.deselected.y + 'px', 'left': 40 * Army.deselected.x + 'px'}));

        var token = {
            type: 'ruin',
            armyId: Army.deselected.armyId
        };

        ws.send(JSON.stringify(token));
    },
    fortify: function (armyId) {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        if (!my.turn) {
            return;
        }

        var token = {
            type: 'fortify',
            armyId: armyId,
            fortify: 1
        };

        ws.send(JSON.stringify(token));
    },
    unfortify: function (armyId) {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        if (!my.turn) {
            return;
        }

        var token = {
            type: 'fortify',
            armyId: armyId,
            fortify: 0
        };

        ws.send(JSON.stringify(token));
    },
    join: function (armyId) {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }


        if (!my.turn) {
            return;
        }

        var token = {
            type: 'join',
            armyId: armyId
        };

        ws.send(JSON.stringify(token));
    },
    disband: function () {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        if (!my.turn) {
            return;
        }
        if (Army.selected == null) {
            return;
        }
        Army.deselect(1);

        var token = {
            type: 'disband',
            armyId: Army.deselected.armyId
        };

        ws.send(JSON.stringify(token));
    },
    move: function (x, y, movesSpend) {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

//    if (Army.selected.moves == 0) {
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

        Army.halfDeselect();

        setLock();

        var token = {
            type: 'move',
            x: x,
            y: y,
            armyId: Army.deselected.armyId
        };

        ws.send(JSON.stringify(token));
    },
    split: function () {
        if (this.closed) {
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
            type: 'split',
            armyId: Army.selected.armyId,
            s: s,
            h: h
        };

        ws.send(JSON.stringify(token));
    },
    resurrection: function (castleId) {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }


        if (!my.turn) {
            return;
        }
        Army.deselect();

        var token = {
            type: 'resurrection',
            castleId: castleId
        };

        ws.send(JSON.stringify(token));
    },
    raze: function () {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var castleId = Castle.getMy(Army.selected.x, Army.selected.y);

        if (!castleId) {
            Message.simple('No castle to destroy.');
            return;
        }

        var token = {
            type: 'raze',
            armyId: Army.selected.armyId
        };

        ws.send(JSON.stringify(token));
    },
    defense: function () {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var castleId = Castle.getMy(Army.selected.x, Army.selected.y);

        if (!castleId) {
            Message.simple('No castle to build defense.');
            return;
        }

        var token = {
            type: 'defense',
            castleId: castleId
        };

        ws.send(JSON.stringify(token));
    },
    startMyTurn: function () {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'startTurn'
        };

        ws.send(JSON.stringify(token));
    },
    nextTurn: function () {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'nextTurn'
        };

        ws.send(JSON.stringify(token));
    },
    statistics: function () {
        if (this.closed) {
            Message.simple('Sorry, server is disconnected.');
            return;
        }

        var token = {
            type: 'statistics'
        };

        ws.send(JSON.stringify(token));
    }
}
