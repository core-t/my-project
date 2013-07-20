function mElement() {
    return $('#terrain');
}

function surrenderM() {
    removeM();
    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append($('<h3>').html('Surrender. Are you sure?'))
            .append(
                $('<div>')
                    .addClass('button go')
                    .html('Ok')
                    .click(function () {
                        removeM();
                        wsSurrender();
                    })
            )
            .append($('<div>').addClass('button cancel').html('Cancel').click(function () {
                removeM()
            }))
            .css({
                'left': messageLeft + 'px'
            })
    );
}

function lostM(color) {
    removeM();

    $('.nr.' + color).html('<img src="/img/game/skull_and_crossbones.png" />');

    var msg;

    if (color == my.color) {
        msg = '<br/>GAME OVER<br/><br/>You lose!';
    } else {
        msg = color.charAt(0).toUpperCase() + color.slice(1) + ' no longer fights!';
    }

    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append($('<h3>').html(msg))
            .append($('<div>')
                .addClass('button go')
                .html('Ok')
                .click(function () {
                    removeM();

                })
            )
            .css({
                'left': messageLeft + 'px'
            })
    );
}

function winM(color) {
    removeM();
    setLock();

    var msg;

    if (color == my.color) {
        msg = '<br/>GAME OVER<br/><br/>You won!';

    } else {
        msg = '<br/>GAME OVER<br/><br/>' + color.charAt(0).toUpperCase() + color.slice(1) + ' won!';
    }

    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append($('<h3>').html(msg))
            .append($('<div>')
                .addClass('button go')
                .html('Ok')
                .click(function () {
                    removeM();

                })
            )
            .css({
                'left': messageLeft + 'px'
            })
    );
}

function turnM() {
    removeM();
    if (my.turn && turn.nr == 1) {
        castleM(firstCastleId, my.color);
    }

    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append($('<h3>').html('Your turn.'))
            .append($('<div>')
                .addClass('button go')
                .html('Ok')
                .click(function () {
                    removeM();

                })
            )
            .css({
                'left': messageLeft + 'px'
            })
    );
}

function nextTurnM() {
    removeM();
    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append($('<h3>').html('Next turn. Are you sure?'))
            .append(
                $('<div>')
                    .addClass('button go')
                    .html('Ok')
                    .click(function () {
                        removeM();
                        wsNextTurn();
                        //                nextTurnA();
                    })
            )
            .append($('<div>').addClass('button cancel').html('Cancel').click(function () {
                removeM()
            }))
            .css({
                'left': messageLeft + 'px'
            })
    );
}

function simpleM(message) {
    removeM();
    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append($('<h3>').html(message))
            .append(
                $('<div>')
                    .addClass('button go')
                    .html('Ok')
                    .click(function () {
                        removeM();
                    })
            )
            .css({
                'left': messageLeft + 'px'
            })
    );
}

function disbandArmyM() {
    if (typeof selectedArmy == 'undefined') {
        return;
    }
    if (!my.turn) {
        return;
    }
    removeM();
    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append($('<h3>').html('Are you sure?'))
            .append(
                $('<div>')
                    .addClass('button go')
                    .html('Disband')
                    .click(function () {
                        wsDisbandArmy()
                    })
            )
            .append($('<div>').addClass('button cancel').html('Cancel').click(function () {
                removeM()
            }))
            .css({
                'left': messageLeft + 'px'
            })
    );
}

function splitArmyM(a) {
    if (typeof selectedArmy == 'undefined') {
        return;
    }
    removeM();
    var army = $('<div>').addClass('split');
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
                        'src': '/img/game/' + img + '_' + selectedArmy.color + '.png',
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
                        'src': '/img/game/hero_' + selectedArmy.color + '.png',
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
    var height = numberOfUnits * 39 + 40;
    if (height > documentHeigh - 100) {
        height = documentHeigh - 100;
        overflow = 'auto';
    } else {
        overflow = 'hidden';
    }

    mElement().after(
        $('<div>')
            .addClass('message')
            .append(army)
            .append(
                $('<div>')
                    .css({
                        'margin-top': '15px'
                    })
                    .append(
                        $('<div>').addClass('left')
                            .append($('<a>').addClass('button cancel').html('Cancel').click(function () {
                                removeM()
                            }))
                    )
                    .append(
                        $('<div>').addClass('right')
                            .append($('<a>').addClass('button submit').html('Select units').click(function () {
                                wsSplitArmy(selectedArmy.armyId)
                            }))
                    )
            )
            .css({
                'height': height + 'px',
                'left': messageLeft + 'px',
                'overflow-y': overflow
            }));
}

function armyStatusM() {
    if (typeof selectedArmy == 'undefined') {
        return;
    }
    removeM();
    var army = $('<div>').addClass('status');
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
            attackPoints.append($('<span>').html(' +1').css('color', '#d00000'));
            defensePoints.append($('<span>').html(' +1').css('color', '#d00000'));
        }
        if (selectedArmy.heroKey) {
            attackPoints.append($('<span>').html(' +1').css('color', '#d00000'));
            defensePoints.append($('<span>').html(' +1').css('color', '#d00000'));
        }
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
                        'src': '/img/game/' + img + '_' + selectedArmy.color + '.png',
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
                        'src': '/img/game/hero_' + selectedArmy.color + '.png',
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
    var height = numberOfUnits * 60 + 40;
    if (height > documentHeigh - 100) {
        height = documentHeigh - 100;
        overflow = 'auto';
    } else {
        overflow = 'hidden';
    }
    mElement().after(
        $('<div>')
            .addClass('message')
            .addClass('center')
            .append(army)
            .append($('<div>')
                .css({
                    'margin-top': '15px'
                })
                .append($('<div>')
                    .addClass('button cancel')
                    .html('Ok')
                    .click(function () {
                        removeM()
                    }))
            )
            .css({
                'height': height + 'px',
                'left': messageLeft + 'px',
                'overflow': overflow
            })
    );
}

function castleM(castleId, color) {
    if (lock) {
        return;
    }
    if (!my.turn) {
        return;
    }
    if (selectedArmy) {
        return;
    }
    removeM();
    var time = '';
    var attr;
    var capital = null;
    if (castles[castleId].capital) {
        capital = $('<h4>').append('Capital city');
    }
    var table = $('<table>').addClass('production').append($('<label>').html('Production:'));
    var j = 0;
    var td = new Array();

    for (unitName in castles[castleId].production) {
        var img = unitName.replace(' ', '_').toLowerCase();
        var unitId = getUnitId(unitName);
        var travelBy = '';
        if (unitId == castles[castleId].currentProduction) {
            attr = {
                type: 'radio',
                name: 'production',
                value: unitName,
                checked: 'checked'
            }
            time = castles[castleId].currentProductionTurn + '/';
        } else {
            attr = {
                type: 'radio',
                name: 'production',
                value: unitName
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
        td[j] = $('<td>')
            .addClass('unit')
            .append(
                $('<p>')
                    .append($('<input>').attr(attr))
                    .append(' ' + unitName)
            )
            .append($('<div>').append($('<img>').attr('src', '/img/game/' + img + '_' + color + '.png')))
            .append(
                $('<div>')
                    .addClass('attributes')
                    .append($('<p>').html('Time:&nbsp;' + time + castles[castleId].production[unitName].time + 't'))
                    .append($('<p>').html('Cost:&nbsp;' + castles[castleId].production[unitName].cost + 'g'))
                    .append($('<p>').html(travelBy))
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
            tr.append($('<td>').addClass('unit').html('&nbsp;'));
        } else {
            tr.append(td[m + 1]);
        }
        table.append(tr);
    }
    table.append(
        $('<tr>')
            .append(
                $('<td>')
                    .append(
                        $('<input>').attr({
                            type: 'radio',
                            name: 'production',
                            value: 'stop'
                        })
                    )
                    .append(' Stop production')
            )
            .append(
                $('<td id="blank">')
                    .append($('<div>').addClass('button submit').html('Apply').click(function () {
                        setProductionA(castleId)
                    })
                    ))
    )
    ;
    var resurrectionElement;
    var resurrection = true;
    for (armyId in players[my.color].armies) {
        for (j in players[my.color].armies[armyId].heroes) {
            resurrection = false;
        }
    }
    if (resurrection) {
        var buttonResurestion;
        var cssResurestion;
        if ($('#gold').html() < 100) {
            buttonResurestion = $('<div>').addClass('button right buttonOff').html('Hero resurrection (cost 100g)');
            cssResurestion = {
                'color': '#d00000'
            };
        } else {
            buttonResurestion = $('<div>').addClass('button right').html('Hero resurrection (cost 100g)').click(function () {
                wsHeroResurrection(castleId)
            });
            cssResurestion = {};
        }
        resurrectionElement = $('<p>')
            .addClass('h')
            .css(cssResurestion)
            .append(
                $('<input>').attr({
                    type: 'checkbox',
                    name: 'resurrection',
                    value: castleId
                })
            )
            .append(buttonResurestion);
    }
    var buttonBuilDefense;
    var cssBuilDefense;
    var costBuilDefense = 0;
    for (i = 1; i <= castles[castleId].defense; i++) {
        costBuilDefense += i * 100;
    }
    if ($('#gold').html() < costBuilDefense) {
        buttonBuilDefense = $('<div>').addClass('button right buttonOff').html('Build defense (cost ' + costBuilDefense + 'g)');
    } else {
        buttonBuilDefense = $('<div>').addClass('button right').html('Build defense (cost ' + costBuilDefense + 'g)').click(function () {
            wsCastleBuildDefense();
        });
    }
    mElement().after(
        $('<div>')
            .addClass('message')
            .css({
                'left': messageLeft + 'px'
            })
            .append(capital)
            .append($('<h3>').append(castles[castleId].name))
            .append($('<h5>').append('Position: ' + castles[castleId].position['x'] + ' East - ' + castles[castleId].position['y'] + ' South'))
            .append($('<h5>').append('Defense: ' + castles[castleId].defense))
            .append($('<h5>').append('Income: ' + castles[castleId].income + ' gold/turn'))
            .append(table)
            .append(
                $('<p>')
                    .addClass('h')
                    .append(
                        $('<input>').attr({
                            type: 'checkbox',
                            name: 'defense',
                            value: castleId
                        })
                    )
                    .append(buttonBuilDefense)
            )
            .append(
                $('<p>')
                    .addClass('h')
                    .append(
                        $('<input>').attr({
                            type: 'checkbox',
                            name: 'raze',
                            value: castleId
                        })
                    )
                    .append($('<div>').addClass('button right').html('Raze (income 1000g)').click(function () {
                        wsRazeCastle()
                    }))
            )
            .append(
                $('<p>')
                    .append($('<div>').addClass('button cancel').html('Cancel').click(function () {
                        removeM()
                    }))
            )
            .append(resurrectionElement)
    );

}

function battleM(data, clb) {
    var battle = data.battle;
    var attackerColor = data.attackerColor;
    var defenderColor = data.defenderColor;
    var newBattle = new Array();
    var attack = $('<div>').addClass('battle attack');
    for (i in battle.attack.soldiers) {
        if (battle.attack.soldiers[i].succession) {
            newBattle[battle.attack.soldiers[i].succession] = {
                'soldierId': battle.attack.soldiers[i].soldierId
            };
        }
        attack.append(
            $('<img>').attr({
                'src': getUnitImage(battle.attack.soldiers[i].unitId, attackerColor),
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
                'src': '/img/game/hero_' + attackerColor + '.png',
                'id': 'hero' + battle.attack.heroes[i].heroId
            })
        );
    }
    mElement().after(
        $('<div>')
            .addClass('message')
            .css({
                'left': messageLeft + 'px',
                'display': 'none'
            })
            .append(attack)
            .append($('<p id="vs">').html('VS').addClass('center'))
    );

    var defense = $('<div>').addClass('battle defense');

    for (i in battle.defense.soldiers) {
        if (battle.defense.soldiers[i].succession) {
            newBattle[battle.defense.soldiers[i].succession] = {
                'soldierId': battle.defense.soldiers[i].soldierId
            };
        }
        defense.append(
            $('<img>').attr({
                'src': getUnitImage(battle.defense.soldiers[i].unitId, defenderColor),
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
                'src': '/img/game/hero_' + defenderColor + '.png',
                'id': 'hero' + battle.defense.heroes[i].heroId
            })
        );
    }

    $('.message')
        .addClass('center')
        .append(defense)
        .append($('<div>').addClass('battle defense'))
        .append($('<div id="battleOk">').addClass('button go').html('OK'));

    if (my.color == data.attackerColor && isDigit(data.castleId) && isTruthful(data.victory)) {
        $('#battleOk').click(function () {
            castleM(data.castleId, data.attackerColor);
        });
    } else {
        $('#battleOk').click(function () {
            removeM();
        });
    }
    if (newBattle) {
        $('.message').fadeIn(100, function () {
            killM(newBattle, clb, data);
        })
    }
}

function killM(b, clb, data) {
    for (i in b) {
        break;
    }
    if (typeof b[i] == 'undefined') {
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

        return;
    }

    if (typeof b[i].soldierId != 'undefined') {
        $('#unit' + b[i].soldierId).fadeOut(1500, function () {
            delete b[i];
            killM(b, clb, data);
        });
    } else if (typeof b[i].heroId != 'undefined') {
        $('#hero' + b[i].heroId).fadeOut(1500, function () {
            delete b[i];
            killM(b, clb, data);
        });
    } else {
        console.log('zonk');
    }
}

function removeM() {
    if (typeof $('.message') != 'undefined') {
        //        console.log('removeM');
        $('.message').remove();
    }
}

