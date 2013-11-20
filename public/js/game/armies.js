// *** ARMIES ***

var Army = {
    selected: null,
    deselected: null,
    parent: null,
    skippedArmies: new Array(),
    quitedArmies: new Array(),
    nextArmyId: null,
    isNextSelected: null,
    init: function (obj, shortName) {

        $('#army' + obj.armyId).remove();

        if (obj.destroyed) {
            armyFields(players[shortName].armies[obj.armyId]);
            delete players[shortName].armies[obj.armyId];

            return;
        }

        var army = {
            armyId: obj.armyId,
            x: obj.x,
            y: obj.y,
            flyBonus: 0,
            canFly: 1,
            canSwim: 0,
            heroes: obj.heroes,
            soldiers: obj.soldiers,
            fortified: obj.fortified
        }

        if (army.fortified) {
            Army.quitedArmies.push(army.armyId);
        } else {
            this.unfortify(army.armyId);
        }

        var numberOfUnits = 0,
            numberOfHeroes = 0,
            numberOfSoldiers = 0,

            attack = 0,
            defense = 0;

        for (hero in army.heroes) {
            if (notSet(army.heroKey)) {
                army.heroKey = hero;
            }
            if (notSet(army.moves)) {
                army.moves = army.heroes[hero].movesLeft;
            }
            if (army.heroes[hero].movesLeft < army.moves) {
                army.moves = army.heroes[hero].movesLeft;
                army.heroKey = hero;
            }
            army.canFly--;
            numberOfHeroes++;
        }

        for (soldier in army.soldiers) {
            numberOfSoldiers++;

            if (notSet(army.soldierKey)) {
                army.soldierKey = soldier;
            }

            if (notSet(army.moves)) {
                army.moves = army.soldiers[soldier].movesLeft;
            }

            if (army.soldiers[soldier].movesLeft < army.moves) {
                army.moves = army.soldiers[soldier].movesLeft
            }

            if (units[army.soldiers[soldier].unitId].attackPoints > attack) {
                attack = units[army.soldiers[soldier].unitId].attackPoints;
                army.soldierKey = soldier;
            }

            if (units[army.soldiers[soldier].unitId].defensePoints > defense) {
                defense = units[army.soldiers[soldier].unitId].defensePoints;
                if (defense > units[army.soldiers[army.soldierKey].unitId].defensePoints) {
                    army.soldierKey = soldier;
                }
            }

            if (units[army.soldiers[soldier].unitId].canFly) {
                army.canFly++;
                if (!army.flyBonus) {
                    army.flyBonus = 1;
                }
            } else {
                army.canFly -= 200;
            }

            if (units[army.soldiers[soldier].unitId].canSwim) {
                army.canSwim++;
                army.moves = army.soldiers[soldier].movesLeft;
            }
        }

        if (army.canSwim) {
            for (soldier in army.soldiers) {
                if (army.soldiers[soldier].unitId != shipId) {
                    continue;
                }

                if (notSet(shipMoves)) {
                    var shipMoves = army.soldiers[soldier].movesLeft;
                }

                if (army.soldiers[soldier].movesLeft < shipMoves) {
                    shipMoves = army.soldiers[soldier].movesLeft
                }
            }

            army.movementType = 'swimming';
        } else if (army.canFly > 0) {
            for (soldier in army.soldiers) {
                if (!units[army.soldiers[soldier].unitId].canFly) {
                    continue;
                }

                if (typeof flyMoves == 'undefined') {
                    var flyMoves = army.soldiers[soldier].movesLeft;
                }

                if (army.soldiers[soldier].movesLeft < flyMoves) {
                    flyMoves = army.soldiers[soldier].movesLeft
                }
            }

            army.movementType = 'flying';
        } else {
            army.movementType = 'walking';
        }

        if (army.canSwim) {
            if (units[shipId].name_lang) {
                army.name = units[shipId].name_lang;
            } else {
                army.name = units[shipId].name;
            }
            army.img = Unit.getImage(shipId, shortName);
            army.attack = units[shipId].attackPoints;
            army.defense = units[shipId].defensePoints;
            army.moves = shipMoves;
        } else if (typeof army.heroes[army.heroKey] != 'undefined') {
            if (army.heroes[army.heroKey].name) {
                army.name = army.heroes[army.heroKey].name;
            } else {
                army.name = 'Anonymous hero';
            }
            army.img = Hero.getImage(shortName);
            army.attack = army.heroes[army.heroKey].attackPoints;
            army.defense = army.heroes[army.heroKey].defensePoints;

            if (army.canFly > 0) {
                army.moves = flyMoves;
            }
        } else if (isSet(units[army.soldiers[army.soldierKey].unitId])) {
            if (units[army.soldiers[army.soldierKey].unitId].name_lang) {
                army.name = units[army.soldiers[army.soldierKey].unitId].name_lang;
            } else {
                army.name = units[army.soldiers[army.soldierKey].unitId].name;
            }
            army.img = Unit.getImage(army.soldiers[army.soldierKey].unitId, shortName);
            army.attack = attack;
            army.defense = defense;
        } else {
            console.log('Armia nie posiada jednostek:');
            console.log(obj);
            return;
        }

        var element = $('<div>');

        if (shortName == my.color) { // moja armia
            element.click(function (e) {
                myArmyClick(army, e)
            });
            element.mouseover(function () {
                myArmyMouse(army.armyId)
            });
            element.mousemove(function () {
                myArmyMouse(army.armyId)
            });
            if (army.canSwim) {
                //            if(fields[army.y][army.x] != 'S'){
                //                army.fieldType = fields[army.y][army.x];
                //            }
                if (!Castle.isMyCastle(army.x, army.y)) {
                    fields[army.y][army.x] = 'S';
                }
            }
        } else { // nie moja armia
            fields[army.y][army.x] = 'e';
            enemyArmyMouse(element);
        }
        numberOfUnits = numberOfHeroes + numberOfSoldiers;
        if (numberOfUnits > 8) {
            numberOfUnits = 8;
        }

        board.append(element
            .addClass('army')
            .addClass(shortName)
            .attr({
                id: 'army' + army.armyId,
                title: army.name
            })
            .css({
                background: 'url(/img/game/flags/' + shortName + '_' + numberOfUnits + '.png) top left no-repeat',
                left: (army.x * 40) + 'px',
                top: (army.y * 40) + 'px'
            })
            .append(
                $('<img>')
                    .addClass('unit')
                    .attr('src', army.img)
            )
        );

        army.color = shortName;
        var mX = army.x * 2;
        var mY = army.y * 2;

        zoomPad.append(
            $('<div>')
                .css({
                    'left': mX + 'px',
                    'top': mY + 'px',
                    'background': mapPlayersColors[shortName].backgroundColor,
                    'z-index': 10
                })
                .attr('id', army.armyId)
                .addClass('a')
        );

        players[shortName].armies[army.armyId] = army;
    },
    showFirst: function (shortName) {
        for (i in players[shortName].armies) {
            zoomer.lensSetCenter(players[shortName].armies[i].x * 40, players[shortName].armies[i].y * 40);
            return;
        }
        zoomer.lensSetCenter(30, 30);
    },
    removeFromSkipped: function (armyId) {
        var index = $.inArray(armyId, Army.skippedArmies);
        if (index != -1) {
            Army.skippedArmies.splice(index, 1);
        }
    },
    skip: function () {
        if (!my.turn) {
            return;
        }

        if (lock) {
            return;
        }

        if (Army.selected) {
            Sound.play('skip');
            Army.skippedArmies.push(Army.selected.armyId);
            Army.deselect();
            this.findNext();
        }
    },
    findNext: function () {
        if (!my.turn) {
            return;
        }

        if (lock) {
            return;
        }

        Army.deselect();

        var reset = true;

        for (armyId in players[my.color].armies) {
            if (notSet(players[my.color].armies[armyId].armyId)) {
                continue;
            }
            if (players[my.color].armies[armyId].moves == 0) {
                continue;
            }
            if ($.inArray(armyId, Army.skippedArmies) != -1) {
                continue;
            }
            if ($.inArray(armyId, Army.quitedArmies) != -1) {
                continue;
            }

            if (Army.isNextSelected) {
                Army.nextArmyId = armyId;
                reset = false;
                break;
            }

            if (!Army.nextArmyId) {
                Army.nextArmyId = armyId;
            }

            if (Army.nextArmyId == armyId) {
                if (!Army.isNextSelected) {
                    Army.isNextSelected = true;
                    this.deselect();
                    Sound.play('slash');
                    Army.select(players[my.color].armies[Army.nextArmyId]);
                }
            }
        }

        Army.isNextSelected = false;

        if (reset) {
            Army.nextArmyId = null;
        }
    },
    delete: function (armyId, color, quiet) {
        if (notSet(players[color].armies[armyId])) {
            throw ('Brak armi o armyId = ' + armyId + ' i kolorze = ' + color);
            return;
        }

        armyFields(players[color].armies[armyId]);

        if (quiet) {
            $('#army' + armyId).remove();
            $('#' + armyId).remove();
        } else {
            zoomer.lensSetCenter(players[color].armies[armyId].x * 40, players[color].armies[armyId].y * 40);
            $('#' + armyId).fadeOut(500, function () {
                $('#army' + armyId).remove();
                $('#' + armyId).remove();
            });
        }

        delete players[color].armies[armyId];
    },
    select: function (a, center) {
        castlesAddCursorWhenSelectedArmy();
        armiesAddCursorWhenSelectedArmy();
        myCastlesRemoveCursor();

        this.removeFromSkipped(a.armyId);

        this.unfortify(a.armyId);

        $('#army' + a.armyId).css({
            'box-shadow': '0 0 10px #fff',
            'border': '1px solid #fff'
        });

        $('#name').html(a.name);
        $('#moves').html(a.moves);
        $('#attack').html(a.attack);
        $('#defense').html(a.defense);

        $('#splitArmy').removeClass('buttonOff');
        $('#unselectArmy').removeClass('buttonOff');
        $('#armyStatus').removeClass('buttonOff');
        $('#disbandArmy').removeClass('buttonOff');
        $('#skipArmy').removeClass('buttonOff');
        $('#quitArmy').removeClass('buttonOff');

        Army.selected = a;

        if (isSet(Army.selected.heroKey)) {
            if (Ruin.getId(Army.selected) !== null) {
                $('#searchRuins').removeClass('buttonOff');
            }
            $('#showArtifacts').removeClass('buttonOff');
        }

        if (Castle.isMyCastle(a.x, a.y)) {
            $('#razeCastle').removeClass('buttonOff');
            $('#showCastle').removeClass('buttonOff');
        }

        if (notSet(center)) {
            zoomer.setCenterIfOutOfScreen(a.x * 40, a.y * 40);
        }
    },
    deselect: function (skipJoin) {
        if (notSet(skipJoin) && Army.parent && Army.selected) {
            if (Army.selected.x == Army.parent.x && Army.selected.y == Army.parent.y) {
                Websocket.join(Army.selected.armyId);
            }
        }

        castlesAddCursorWhenUnselectedArmy();
        armiesAddCursorWhenUnselectedArmy();
        myCastlesAddCursor();

        $('#name').html('');
        $('#moves').html('');
        $('#attack').html('');
        $('#defense').html('');

        this.halfDeselect();
    },
    halfDeselect: function () {
        if (Army.selected) {
            Army.deselected = Army.selected;
            $('#army' + Army.selected.armyId).css({
                'box-shadow': 'none',
                'border': 'none'
            });
            board.css('cursor', 'url(/img/game/cursor.png), default');
        }
        Army.selected = null;
        $('.path').remove();
        $('#splitArmy').addClass('buttonOff');
        $('#unselectArmy').addClass('buttonOff');
        $('#armyStatus').addClass('buttonOff');
        $('#skipArmy').addClass('buttonOff');
        $('#quitArmy').addClass('buttonOff');
        $('#searchRuins').addClass('buttonOff');
        $('#razeCastle').addClass('buttonOff');
        $('#showCastle').addClass('buttonOff');
        $('#showArtifacts').addClass('buttonOff');
        $('#disbandArmy').addClass('buttonOff');
        Message.remove();
    },
    fortify: function () {
        if (!my.turn) {
            return;
        }
        if (lock) {
            return;
        }
        if (Army.selected) {
            Websocket.fortify(Army.selected.armyId);
            Army.quitedArmies.push(Army.selected.armyId);
            Army.deselect();
            Army.findNext();
        }
    },
    unfortify: function (armyId) {
        if (isComputer(Turn.shortName)) {
            return;
        }
        var index = $.inArray(armyId, Army.quitedArmies);
        if (index != -1) {
            Websocket.unfortify(armyId, 0);
            Army.quitedArmies.splice(index, 1);
        }
    }




}

function walking(path) {
    var className = 'path1';

    for (i in path) {
        var pX = path[i].x * 40;
        var pY = path[i].y * 40;

        if (Army.selected.moves < path[i].G) {
            className = 'path2';
            if (notSet(set)) {
                var set = {'x': pX, 'y': pY};
            }
        }

        board.append(
            $('<div>')
                .addClass('path ' + className)
                .css({
                    left: pX + 'px',
                    top: pY + 'px'
                })
                .html(path[i].G)
        );
    }
    return set;
}

function myArmyClick(army, e) {
    if (e.which == 1) {
        if (lock) {
            return;
        }

        if (!my.turn) {
            return;
        }

        if (!Army.selected) {
            Army.deselect();
            Army.select(players[my.color].armies[army.armyId], 0);
        }
    }
}

function myArmyMouse(armyId) {
    if (lock) {
        return;
    }
    if (my.turn && !Army.selected) {
        $('#army' + armyId + ' *').css('cursor', 'url(/img/game/cursor_select.png), default');
        $('#army' + armyId).css('cursor', 'url(/img/game/cursor_select.png), default');
    }
    else {
        $('#army' + armyId + ' *').css('cursor', 'url(/img/game/cursor.png), default');
        $('#army' + armyId).css('cursor', 'url(/img/game/cursor.png), default');
    }
}

function armiesAddCursorWhenSelectedArmy() {
    $('.army:not(.' + my.color + ')').css('cursor', 'url(/img/game/cursor_attack.png), crosshair');
    $('.army:not(.' + my.color + ') img').css('cursor', 'url(/img/game/cursor_attack.png), crosshair');
}

function armiesAddCursorWhenUnselectedArmy() {
    $('.army:not(.' + my.color + ')').css('cursor', 'url(/img/game/cursor.png), default');
    $('.army:not(.' + my.color + ') img').css('cursor', 'url(/img/game/cursor.png), default');
}

function enemyArmyMouse(element, x, y) {
    element
        .mouseover(function () {
            if (lock) {
                return;
            }
            if (my.turn && Army.selected) {
//                selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
                var castleId = isEnemyCastle(x, y);
                if (castleId !== null) {
                    Castle.changeFields(castleId, 'g');
                }
                fields[y][x] = 'g';
            }
        })
        .mousemove(function () {
            if (lock) {
                return;
            }
            if (my.turn && Army.selected) {
//                selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
                var castleId = isEnemyCastle(x, y);
                if (castleId !== null) {
                    Castle.changeFields(castleId, 'g');
                }
                fields[y][x] = 'g';
            }
        })
        .mouseout(function () {
            var castleId = isEnemyCastle(x, y);
            if (castleId !== null) {
                Castle.changeFields(castleId, 'e');
            }
            fields[y][x] = 'e';
        });
}

function armyFields(a) {
    if (a.color == my.color) {
        if (fields[a.y][a.x] == 'S') {
            fields[a.y][a.x] = fieldsOryginal[a.y][a.x];
        }
        return;
    }
    if (notSet(fields[a.y])) {
        console.log('Y error');
        return;
    }
    if (notSet(fields[a.y][a.x])) {
        console.log('X error');
        return;
    }

    //    console.log(a);
    //    console.log(fields[a.y][a.x]);

    if (isEnemyCastle(a.x, a.y) !== null) {
        fields[a.y][a.x] = 'e';
    } else {
        fields[a.y][a.x] = fieldsOryginal[a.y][a.x];
    }

//    console.log(fields[a.y][a.x]);

}

function computerArmiesUpdate(armies, color) {
    for (armyId in armies) {
        break;
    }

    if (notSet(armies[armyId])) {
        Websocket.computer();
        return;
    }

    Army.init(armies[armyId], color);

    delete armies[armyId]; // potrzebne do pętli

    computerArmiesUpdate(armies, color);
}

// *** UNITS ***

function unitsReformat() {
    for (i in units) {
        if (i == 0) {
            continue;
        }
        units[i]['f'] = units[i].modMovesForest;
        units[i]['s'] = units[i].modMovesSwamp;
        units[i]['m'] = units[i].modMovesHills;
    }
}

var Unit = {
    getId: function (name) {
        for (i in units) {
            if (units[i] != null && units[i].name == name) {
                return units[i].mapUnitId;
            }
        }

        return null;
    },
    getImage: function (unitId, color) {
        return '/img/game/units/' + color + '/' + units[unitId].name.replace(' ', '_').toLowerCase() + '.png'
    },
    getImageByName: function (name, color) {
        return '/img/game/units/' + color + '/' + name + '.png';
    },
    getShipId: function () {
        for (i in units) {
            if (units[i] == null) {
                continue;
            }
            if (units[i].canSwim) {
                return i;
            }
        }
    }
}

var Hero = {
    getImage: function (color) {
        return '/img/game/heroes/' + color + '.png';
    }
}