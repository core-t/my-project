var Message = {
    element: function () {
        return $('#goldBox');
    },
    remove: function () {
        if (typeof $('.message') != 'undefined') {
            $('.message').remove();
        }
    },
    show: function (txt) {
        this.remove();
        this.element().after(
            $('<div>')
                .addClass('message center')
                .append(txt)
        );
        var left = documentWidth / 2 - $('.message').outerWidth() / 2;
        var maxHeight = documentHeight - 120;
        $('.message').css({
            'max-height': maxHeight + 'px',
            'left': left + 'px',
            'display': 'block'
        })
    },
    ok: function (func) {
        $('.message').append(
            $('<div>')
                .addClass('button go')
                .html('Ok')
                .click(function () {
                    if (typeof func != 'undefined') {
                        func();
                    } else {
                        Message.remove();
                    }
                })
        );
//        $('<body>').keypress(function (event) {
//            console.log(event);
//            var key = event.keyCode || event.charCode;
//            if (key == 13) {
//                if (typeof func != 'undefined') {
//                    func();
//                } else {
//                    Message.remove();
//                }
//            }
//        });
    },
    cancel: function (func) {
        $('.message').append(
            $('<div>')
                .addClass('button cancel')
                .html('Cancel')
                .click(function () {
                    if (typeof func != 'undefined') {
                        func();
                    } else {
                        Message.remove();
                    }
                })
        )
    },
    surrender: function () {
        this.show($('<div>').html('Surrender. Are you sure?'));
        this.ok('surrender');
        this.cancel();
    },
    lost: function (color) {
//        $('.nr.' + color).html('<img src="/img/game/skull_and_crossbones.png" />');
        var msg;

        if (color == my.color) {
            msg = '<br/>GAME OVER<br/><br/>You lose!';
        } else {
            msg = color.charAt(0).toUpperCase() + color.slice(1) + ' no longer fights!';
        }

        this.show($('<div>').html(msg));
        this.ok();
    },
    showArtifacts: function () {
        Message.remove();

        var htmlChest = $('<div>').attr('id', 'chest');
        for (i in players[my.color].chest) {
            htmlChest.append(
                $('<div>')
                    .attr('id', players[my.color].chest[i].artifactId)
                    .html(artifacts[players[my.color].chest[i].artifactId].name + ' ' + players[my.color].chest[i].quantity)
                    .click(function () {
                        Websocket.inventoryAdd(selectedArmy.heroes[0].heroId, $(this).attr('id'));
                    })
                    .mousemove(function (e) {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                        $('.zoomWindow').append(
                            $('<div>')
                                .attr('id', 'des' + $(this).attr('id'))
                                .addClass('artifactDescription')
                                .css({
                                    top: e.pageY + 'px',
                                    left: e.pageX + 'px'

                                })
                                .append(
                                    '<h3>' + artifacts[$(this).attr('id')].name + '</h3><div>' + artifacts[$(this).attr('id')].description + '</div>'
                                )
                        );
                    })
                    .mouseleave(function () {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                    })
            );
        }

        var htmlInventory = $('<div>').attr('id', 'inventory');

        for (i in selectedArmy.heroes[0].artifacts) {
            htmlInventory.append(
                $('<div>')
                    .attr('id', selectedArmy.heroes[0].artifacts[i].artifactId)
                    .html(artifacts[selectedArmy.heroes[0].artifacts[i].artifactId].name)
                    .click(function () {
                        Websocket.inventoryDel(selectedArmy.heroes[0].heroId, $(this).attr('id'));
                    })
                    .mousemove(function (e) {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                        $('.zoomWindow').append(
                            $('<div>')
                                .attr('id', 'des' + $(this).attr('id'))
                                .addClass('artifactDescription')
                                .css({
                                    top: e.pageY + 'px',
                                    left: e.pageX + 'px'

                                })
                        );
                    })
                    .mouseleave(function () {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                    })
            );
        }

        this.element().after(
            $('<div>')
                .addClass('message')
                .addClass('center')
                .append($('<h3>').html('Chest'))
                .append(htmlChest)
                .append($('<h3>').html('Inventory'))
                .append(htmlInventory)
                .append($('<div>')
                    .addClass('button go')
                    .html('Ok')
                    .click(function () {
                        Message.remove();
                    })
                )
                .css({
                    'left': this.left + 'px'
                })
        );

    },
    turn: function () {
        this.remove();
        if (my.turn && turn.nr == 1) {
            Message.castle(firstCastleId);
        } else {
            this.show($('<div>').html('Your turn.'));
            this.ok();
        }
    },
    castle: function (castleId) {
        if (lock) {
            return;
        }
        if (!my.turn) {
            return;
        }
        if (selectedArmy) {
            return;
        }

        if (typeof castles[castleId] == 'undefined') {
            return;
        }

        var time = '';
        var attr;
        var capital = '';

        if (castles[castleId].capital) {
            capital = ' - Capital city';
        }
        var table = $('<table>');
        var j = 0;
        var td = new Array();

        for (unitId in castles[castleId].production) {
            var img = units[unitId].name.replace(' ', '_').toLowerCase();
            var travelBy = '';
            if (unitId == castles[castleId].currentProduction) {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: units[unitId].name,
                    checked: 'checked'
                }
                time = castles[castleId].currentProductionTurn + '/';
            } else {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: units[unitId].name
                }
                time = '';
            }

            if (units[unitId].canFly) {
                travelBy = 'ground / air';
            } else if (units[unitId].canSwim) {
                travelBy = 'water';
            } else {
                travelBy = 'ground';
            }
            if (units[unitId].name_lang) {
                var name = units[unitId].name_lang;
            } else {
                var name = units[unitId].name;
            }
            td[j] = $('<td>')
                .addClass('unit')
                .append(
                    $('<p>')
                        .append($('<input>').attr(attr))
                        .append(' ' + name + ' (' + travelBy + ')')
                )
                .append($('<div>').append($('<img>').attr('src', Unit.getImageByName(img, my.color))))
                .append(
                    $('<div>')
                        .addClass('attributes')
                        .append($('<p>').html('Time:&nbsp;' + time + castles[castleId].production[unitId].time + 't'))
                        .append($('<p>').html('Cost:&nbsp;' + castles[castleId].production[unitId].cost + 'g'))
                        .append($('<p>').html('M ' + units[unitId].numberOfMoves + ' . A ' + units[unitId].attackPoints + ' . D ' + units[unitId].defensePoints))
                );
            j++;
        }
        var k = Math.ceil(j / 2);
        for (l = 0; l < k; l++) {
            var tr = $('<tr>');
            var m = l * 2;
            tr.append(td[m]);
            if (typeof td[m + 1] == 'undefined') {
                tr.append($('<td>').addClass('empty').html('&nbsp;'));
            } else {
                tr.append(td[m + 1]);
            }
            table.append(tr);
        }
        table.append(
            $('<tr>')
                .append(
                    $('<td>')
                        .addClass('unit')
                        .append(
                            $('<input>').attr({
                                type: 'radio',
                                name: 'production',
                                value: 'stop'
                            })
                        )
                        .append(' Stop production')
                )
        );
        var resurrectionElement;
        var resurrection = true;
        for (armyId in players[my.color].armies) {
            for (j in players[my.color].armies[armyId].heroes) {
                resurrection = false;
            }
        }
        if (resurrection) {
            var resurrectionElement = $('<fieldset>')
                .append($('<label>').html('Hero resurrection'))
                .append(
                    $('<div>')
                        .append(
                            $('<input>').attr({
                                type: 'checkbox',
                                name: 'resurrection',
                                value: castleId
                            })
                        )
                        .append(' cost 100g')
                )
        }
        var buttonBuildDefense;
        var costBuildDefense = 0;
        for (i = 1; i <= castles[castleId].defense; i++) {
            costBuildDefense += i * 100;
        }

        var div = $('<div>')
            .append($('<h3>').append(castles[castleId].name).append(capital))
            .append($('<h5>').append('Castle defense: ' + castles[castleId].defense))
            .append($('<h5>').append('Income: ' + castles[castleId].income + ' gold/turn'))
            .append(
                $('<fieldset>')
                    .append($('<label>').html('Build castle defense'))
                    .append(
                        $('<div>')
                            .append(
                                $('<input>').attr({
                                    type: 'checkbox',
                                    name: 'defense',
                                    value: castleId
                                })
                            )
                            .append(' cost ' + costBuildDefense + 'g')
                    )
            )
            .append($('<br>'))
            .append($('<fieldset>').addClass('production').append($('<label>').html('Production')).append(table))
            .append($('<br>'))
            .append(resurrectionElement);

        Message.show(div.html());
        this.ok(Castle.handle);
        this.cancel();
    },
    nextTurn: function () {
        this.show($('<div>').html('Next turn. Are you sure?'));
        this.ok(Websocket.nextTurn);
        this.cancel();
    },
    win: function (color) {
        setLock();

        var msg;

        if (color == my.color) {
            msg = '<br/>GAME OVER<br/><br/>You won!';

        } else {
            msg = '<br/>GAME OVER<br/><br/>' + color.charAt(0).toUpperCase() + color.slice(1) + ' won!';
        }

        this.show($('<div>').html(msg));
        this.ok(Message.remove);
    },
    simple: function (message) {
        this.show($('<div>').html(message));
        this.ok(Message.remove);
    },
    disbandArmy: function () {
        if (typeof selectedArmy == 'undefined') {
            return;
        }

        if (!my.turn) {
            return;
        }

        if (!selectedArmy) {
            return;
        }

        this.show($('<div>').html('Are you sure?'));
        this.ok(Websocket.disbandArmy);
        this.cancel();
    },
    splitArmy: function (a) {
        if (typeof selectedArmy == 'undefined') {
            return;
        }
        Message.remove();
        var army = $('<div>').addClass('split').css('max-height', documentHeight - 200 + 'px');
        var numberOfUnits = 0;

        for (i in selectedArmy.soldiers) {
            var img = units[selectedArmy.soldiers[i].unitId].name.replace(' ', '_').toLowerCase();
            numberOfUnits++;
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImageByName(img, selectedArmy.color),
                            'id': 'unit' + selectedArmy.soldiers[i].soldierId
                        })
                    ))
                    .append($('<span>').html(' Moves left: ' + selectedArmy.soldiers[i].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: selectedArmy.soldiers[i].soldierId
                    })))
            );
        }
        for (i in selectedArmy.heroes) {
            numberOfUnits++;
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(selectedArmy.color),
                            'id': 'hero' + selectedArmy.heroes[i].heroId
                        })
                    ))
                    .append($('<span>').html(' Moves left: ' + selectedArmy.heroes[i].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'heroId',
                        value: selectedArmy.heroes[i].heroId
                    })))
            );
        }

        this.show(army);
        this.ok(Websocket.splitArmy);
        this.cancel();

    },
    armyStatus: function () {
        if (typeof selectedArmy == 'undefined') {
            return;
        }

        var army = $('<div>').addClass('status').css('max-height', documentHeight - 200 + 'px');
        var numberOfUnits = 0;
        var bonusTower = 0;
        var castleDefense = getMyCastleDefenseFromPosition(selectedArmy.x, selectedArmy.y);
        var attackPoints;
        var defensePoints;

        if (isTowerAtPosition(selectedArmy.x, selectedArmy.y)) {
            bonusTower = 1;
        }
        for (i in selectedArmy.soldiers) {
            numberOfUnits++;
            var img = units[selectedArmy.soldiers[i].unitId].name.replace(' ', '_').toLowerCase();
            attackPoints = $('<p>').html(units[selectedArmy.soldiers[i].unitId].attackPoints).css('color', '#da8');
            defensePoints = $('<p>').html(units[selectedArmy.soldiers[i].unitId].defensePoints).css('color', '#da8');
            if (selectedArmy.flyBonus && !selectedArmy.soldiers[i].canFly) {
                attackPoints.append($('<span>').html(' +1').css('color', '#00ff00'));
                defensePoints.append($('<span>').html(' +1').css('color', '#00ff00'));
            }
            if (selectedArmy.heroKey) {
                attackPoints.append($('<span>').html(' +1').css('color', '#00ff00'));
                defensePoints.append($('<span>').html(' +1').css('color', '#00ff00'));
            }
            if (bonusTower) {
                defensePoints.append($('<span>').html(' +1').css('color', '#00ff00'));
            }
            if (castleDefense) {
                defensePoints.append($('<span>').html(' +' + castleDefense).css('color', '#00ff00'));
            }
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImageByName(img, selectedArmy.color),
                            'id': 'unit' + selectedArmy.soldiers[i].soldierId
                        })
                    ))
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html('Current moves: '))
                            .append($('<p>').html('Default moves: '))
                            .append($('<p>').html('Attack points: '))
                            .append($('<p>').html('Defense points: '))
                    )
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html(selectedArmy.soldiers[i].movesLeft).css('color', '#da8'))
                            .append($('<p>').html(units[selectedArmy.soldiers[i].unitId].numberOfMoves).css('color', '#da8'))
                            .append(attackPoints)
                            .append(defensePoints)
                    )
            );
        }
        for (i in selectedArmy.heroes) {
            numberOfUnits++;
            attackPoints = $('<p>').html(selectedArmy.heroes[i].attackPoints).css('color', '#da8');
            defensePoints = $('<p>').html(selectedArmy.heroes[i].defensePoints).css('color', '#da8');
            if (bonusTower) {
                defensePoints.append($('<span>').html(' +1').css('color', '#d00000'));
            }
            if (castleDefense) {
                defensePoints.append($('<span>').html(' +' + castleDefense).css('color', '#d00000'));
            }
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(selectedArmy.color),
                            'id': 'hero' + selectedArmy.heroes[i].heroId
                        })
                    ))
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html('Current moves: '))
                            .append($('<p>').html('Default moves: '))
                            .append($('<p>').html('Attack points: '))
                            .append($('<p>').html('Defense points: '))
                    )
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html(selectedArmy.heroes[i].movesLeft).css('color', '#da8'))
                            .append($('<p>').html(selectedArmy.heroes[i].numberOfMoves).css('color', '#da8'))
                            .append(attackPoints)
                            .append(defensePoints)
                    )

            );
        }

        this.show(army);
        this.ok();
    },
    battle: function (data, clb) {
        var battle = data.battle;
        var attackerColor = data.attackerColor;
        var defenderColor = data.defenderColor;
        var newBattle = new Array();
        var attack = $('<div>').addClass('battle attack');

        if (isTruthful(data.castleId)) {
            board.append($('<div>').addClass('castleWar').css({
                top: 40 * castles[data.castleId].y - 12 + 'px',
                left: 40 * castles[data.castleId].x - 11 + 'px'
            }));
        } else {
//            board.append($('<div>').addClass('armyWar').css({
//                top: 40 * castles[data.castleId].y + 'px',
//                left: 40 * castles[data.castleId].x + 'px'
//            }));
        }


        for (i in battle.attack.soldiers) {
            if (battle.attack.soldiers[i].succession) {
                newBattle[battle.attack.soldiers[i].succession] = {
                    'soldierId': battle.attack.soldiers[i].soldierId
                };
            }
            attack.append(
                $('<img>').attr({
                    'src': Unit.getImage(battle.attack.soldiers[i].unitId, attackerColor),
                    'id': 'unit' + battle.attack.soldiers[i].soldierId
                })
            );
        }
        for (i in battle.attack.heroes) {
            if (battle.attack.heroes[i].succession) {
                newBattle[battle.attack.heroes[i].succession] = {
                    'heroId': battle.attack.heroes[i].heroId
                };
            }
            attack.append(
                $('<img>').attr({
                    'src': Hero.getImage(attackerColor),
                    'id': 'hero' + battle.attack.heroes[i].heroId
                })
            );
        }

        var defense = $('<div>').addClass('battle defense');

        for (i in battle.defense.soldiers) {
            if (battle.defense.soldiers[i].succession) {
                newBattle[battle.defense.soldiers[i].succession] = {
                    'soldierId': battle.defense.soldiers[i].soldierId
                };
            }
            defense.append(
                $('<img>').attr({
                    'src': Unit.getImage(battle.defense.soldiers[i].unitId, defenderColor),
                    'id': 'unit' + battle.defense.soldiers[i].soldierId
                })
            );
        }

        for (i in battle.defense.heroes) {
            if (battle.defense.heroes[i].succession) {
                newBattle[battle.defense.heroes[i].succession] = {
                    'heroId': battle.defense.heroes[i].heroId
                };
            }
            defense.append(
                $('<img>').attr({
                    'src': Hero.getImage(defenderColor),
                    'id': 'hero' + battle.defense.heroes[i].heroId
                })
            );
        }

        var div = $('<div>')
            .append(attack)
            .append($('<p id="vs">').html('VS').addClass('center'))
            .append(defense)
            .append($('<div>').addClass('battle defense'))
            .append($('<div id="battleOk">').addClass('button go').html('OK'));

        this.show(div);

        if (my.color == data.attackerColor && isDigit(data.castleId) && isTruthful(data.victory)) {
            $('#battleOk').click(function () {
                Message.castle(data.castleId);
            });
        } else {
            $('#battleOk').click(function () {
                Message.remove();
            });
        }
        if (newBattle) {
            $('.message').fadeIn(100, function () {
                Message.kill(newBattle, clb, data);
            })
        }
    },
    kill: function (b, clb, data) {
        for (i in b) {
            break;
        }
        if (notSet(b[i])) {
            clb();
            if (isTruthful(data.defenderArmy) && isTruthful(data.defenderColor)) {
                if (isTruthful(data.victory)) {
                    for (i in data.defenderArmy) {
                        deleteArmy('army' + data.defenderArmy[i].armyId, data.defenderColor, 1);
                    }
                } else {
                    for (i in data.defenderArmy) {
                        players[data.defenderColor].armies['army' + data.defenderArmy[i].armyId] = new army(data.defenderArmy[i], data.defenderColor);
                    }
                }
            }

            if (isDigit(data.castleId) && isTruthful(data.victory)) {
                castleOwner(data.castleId, data.attackerColor);
            }

            setTimeout('$(".castleWar").remove()', 1000);
            setTimeout('$(".armyWar").remove()', 1000);

            return;
        }

        if (isSet(b[i].soldierId)) {
            $('#unit' + b[i].soldierId).fadeOut(1500, function () {
                delete b[i];
                Message.kill(b, clb, data);
            });
        } else if (isSet(b[i].heroId)) {
            $('#hero' + b[i].heroId).fadeOut(1500, function () {
                delete b[i];
                Message.kill(b, clb, data);
            });
        } else {
            console.log('zonk');
        }
    },
    razeCastle: function () {
        if (selectedArmy == null) {
            return;
        }
        this.show($('<div>').html('Destroy castle. Are you sure?'));
        this.ok(Websocket.razeCastle);
        this.cancel();
    },
    statistics: function () {
        var statistics = $('<div>')
            .append($('<h3>').html('Statistics'));
        var table = $('<table>')
            .append($('<tr>')
                .append($('<td>').html('Players'))
                .append($('<td>').html('Castles conquered'))
                .append($('<td>').html('Castles razed'))
                .append($('<td>').html('Units killed'))
                .append($('<td>').html('Heroes killed'))
            );
        for (i in players) {
            table.append($('<tr>').append($('<td>').html(i)));
        }
        statistics.append(table);
        this.show(statistics);
        this.ok();
    }

}
