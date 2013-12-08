// *** ARMIES ***

var Army = {
    selected: null,
    deselected: null,
    parent: null,
    skippedArmies: {},
    quitedArmies: {},
    nextArmyId: null,
    isNextSelected: null,
    getHeroKey: function (heroes) {
        for (heroId in heroes) {
            return heroId;
        }
    },
    getSoldierKey: function (soldiers) {
        for (key in soldiers) {
            return key;
        }
    },
    getFlySwim: function (army) {
        for (key in army.soldiers) {
            if (units[army.soldiers[key].unitId].canFly) {
                army.canFly++;
                if (!army.flyBonus) {
                    army.flyBonus = 1;
                }
            } else {
                army.canFly -= 200;
            }

            if (units[army.soldiers[key].unitId].canSwim) {
                army.canSwim++;
                army.moves = army.soldiers[key].movesLeft;
            }
        }

        army.canFly -= army.heroes.length;

        return army;
    },
    getMovementType: function (army) {
        if (army.canSwim) {
            army.movementType = 'swimming';
            for (key in terrain) {
                army.terrain[key] = terrain[key][army.movementType];
            }

            for (key in army.soldiers) {
                if (army.soldiers[key].unitId != shipId) {
                    continue;
                }

                if (notSet(shipMoves)) {
                    var shipMoves = army.soldiers[key].movesLeft;
                }

                if (army.soldiers[key].movesLeft < shipMoves) {
                    shipMoves = army.soldiers[key].movesLeft
                }
            }

            army.moves = shipMoves;
        } else if (army.canFly > 0) {
            army.movementType = 'flying';
            for (key in terrain) {
                army.terrain[key] = terrain[key][army.movementType];
            }

            for (key in army.soldiers) {
                if (!units[army.soldiers[key].unitId].canFly) {
                    continue;
                }

                if (notSet(flyMoves)) {
                    var flyMoves = army.soldiers[key].movesLeft;
                }

                if (army.soldiers[key].movesLeft < flyMoves) {
                    flyMoves = army.soldiers[key].movesLeft
                }
            }

            army.moves = flyMoves;
        } else {
            army.movementType = 'walking';
            for (key in terrain) {
                army.terrain[key] = terrain[key][army.movementType];
            }

            var f = 0,
                m = 0,
                s = 0;

            for (key in army.heroes) {
                if (notSet(moves)) {
                    var moves = army.heroes[key].movesLeft;
                }

                if (army.heroes[key].movesLeft < moves) {
                    moves = army.heroes[key].movesLeft
                }
            }

            for (key in army.soldiers) {
                if (units[army.soldiers[key].unitId].f > f) {
                    f = units[army.soldiers[key].unitId].f;
                }
                if (units[army.soldiers[key].unitId].m > m) {
                    f = units[army.soldiers[key].unitId].m;
                }
                if (units[army.soldiers[key].unitId].s > s) {
                    f = units[army.soldiers[key].unitId].s;
                }

                if (notSet(moves)) {
                    var moves = army.soldiers[key].movesLeft;
                }

                if (army.soldiers[key].movesLeft < moves) {
                    moves = army.soldiers[key].movesLeft
                }
            }

            army.terrain[f] = f;
            army.terrain[m] = m;
            army.terrain[s] = s;

            army.moves = moves;
        }
        return army;
    },
    getHeroMovesLeft: function (a, key) {
        return a.heroes[key].movesLeft
    },
    getSoldierMovesLeft: function (a, key) {
        return a.soldiers[key].movesLeft
    },
    getImg: function (army, heroKey, soldierKey) {
        if (army.canSwim) {
            if (units[shipId].name_lang) {
                army.name = units[shipId].name_lang;
            } else {
                army.name = units[shipId].name;
            }

            army.img = Unit.getImage(shipId, army.color);
            army.attack = units[shipId].attackPoints;
            army.defense = units[shipId].defensePoints;
        } else if (heroKey) {
            if (army.heroes[heroKey].name) {
                army.name = army.heroes[heroKey].name;
            } else {
                army.name = 'Anonymous hero';
            }

            army.img = Hero.getImage(army.color);
            army.attack = army.heroes[heroKey].attackPoints;
            army.defense = army.heroes[heroKey].defensePoints;
        } else if (soldierKey) {
            if (units[army.soldiers[soldierKey].unitId].name_lang) {
                army.name = units[army.soldiers[soldierKey].unitId].name_lang;
            } else {
                army.name = units[army.soldiers[soldierKey].unitId].name;
            }

            army.img = Unit.getImage(army.soldiers[soldierKey].unitId, army.color);
            army.attack = units[army.soldiers[soldierKey].unitId].attackPoints;
            army.defense = units[army.soldiers[soldierKey].unitId].defensePoints;
        }

        return army;
    },
    myClick: function (army, e) {
        if (e.which == 1) {
            if (Gui.lock) {
                return;
            }

            if (!my.turn) {
                return;
            }

            if (Army.selected) {
                if (Army.selected.armyId == army.armyId) {
                    if (Army.selected.heroes.length + Army.selected.soldiers.length == 1) {
                        return;
                    }

                    $('#army' + Army.selected.armyId).css('background', 'none');
                    $('#army' + Army.selected.armyId + ' .unit').css('background', 'url(/img/game/units/' + Army.selected.color + '/border_unit.gif)');

                    var newHeroKey = null;
                    var newSoldierKey = null;

                    Army.selected.heroSplitKey = null
                    Army.selected.soldierSplitKey = null

                    for (key in Army.selected.heroes) {
                        if (Army.selected.skippedHeroes[key]) {
                            continue;
                        }
                        Army.selected.skippedHeroes[key] = true;
                        newHeroKey = key;
                        Army.selected.heroSplitKey = key;
                        break;
                    }

                    if (newHeroKey !== null) {
                        Army.getImg(Army.selected, newHeroKey, null);
                        Army.changeImg(Army.selected)
                        Army.updateInfo(Army.selected)
                        $('#moves').html(Army.getHeroMovesLeft(Army.selected, newHeroKey));
                        return;
                    }

                    for (key in Army.selected.soldiers) {
                        if (Army.selected.skippedSoldiers[key]) {
                            continue;
                        }
                        Army.selected.skippedSoldiers[key] = true;
                        newSoldierKey = key;
                        Army.selected.soldierSplitKey = key;
                        break;
                    }

                    if (newSoldierKey !== null) {
                        Army.getImg(Army.selected, null, newSoldierKey);
                        Army.changeImg(Army.selected)
                        Army.updateInfo(Army.selected)
                        $('#moves').html(Army.getSoldierMovesLeft(Army.selected, newSoldierKey));
                        return;
                    }

                    Army.getImg(Army.selected, Army.selected.heroKey, Army.selected.soldierKey);
                    Army.changeImg(Army.selected)
                    Army.getMovementType(Army.selected);
                    Army.updateInfo(Army.selected)
                    $('#name').html('Army');

                    $('#army' + Army.selected.armyId).css('background', 'url(/img/game/units/' + Army.selected.color + '/border_army.gif)');
                    $('#army' + Army.selected.armyId + ' .unit').css('background', 'none');

                    Army.selected.skippedHeroes = {};
                    Army.selected.skippedSoldiers = {};
                } else {
                    Army.deselect();
                    Sound.play('slash');
                    Army.select(players[my.color].armies[army.armyId], 0);
                }
            } else {
                Sound.play('slash');
                Army.select(players[my.color].armies[army.armyId], 0);
            }
        }
    },
    changeImg: function (army) {
        $('#army' + army.armyId + ' .unit img').attr('src', army.img);
    },
    init: function (obj, color) {

        $('#army' + obj.armyId).remove();
        $('#' + obj.armyId + '.a').remove();

        if (obj.destroyed) {
            armyFields(players[color].armies[obj.armyId]);
            delete players[color].armies[obj.armyId];

            return;
        }

        var army = {
            armyId: obj.armyId,
            x: obj.x,
            y: obj.y,
            flyBonus: 0,
            canFly: 0,
            canSwim: 0,
            heroes: obj.heroes,
            soldiers: obj.soldiers,
            fortified: obj.fortified,
            color: color,
            moves: 0,
            heroKey: this.getHeroKey(obj.heroes),
            soldierKey: this.getSoldierKey(obj.soldiers),
            skippedHeroes: {},
            skippedSoldiers: {},
            terrain: {},
            heroSplitKey: null,
            soldierSplitKey: null
        }

        if (army.fortified) {
            Army.quitedArmies[army.armyId] = 1;
        } else {
            this.unfortify(army.armyId);
        }

        army = this.getFlySwim(army);
        army = this.getMovementType(army);
        army = this.getImg(army, army.heroKey, army.soldierKey);

        var element = $('<div>')
            .addClass('army ' + color)
            .attr({
                id: 'army' + army.armyId,
                title: army.name
            })
            .css({
                left: (army.x * 40 - 1) + 'px',
                top: (army.y * 40 - 1) + 'px'
            });

        if (color == my.color) { // moja armia
            element.click(function (e) {
                Army.myClick(army, e)
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
                if (!Castle.getMy(army.x, army.y)) {
                    fields[army.y][army.x] = 'S';
                }
            }
        } else { // nie moja armia
            fields[army.y][army.x] = 'e';
            enemyArmyMouse(element, army.x, army.y);
        }

        var numberOfUnits = army.heroes.length + army.soldiers.length;
        if (numberOfUnits > 8) {
            numberOfUnits = 8;
        }

        board.append(
            element
                .append(
                    $('<div>')
                        .addClass('flag')
                        .css('background', 'url(/img/game/flags/' + color + '_' + numberOfUnits + '.png) top left no-repeat')
                        .append(
                            $('<div>')
                                .addClass('unit')
                                .append(
                                    $('<img>')
                                        .attr('src', army.img)
                                )
                        )
                )
        );

        zoomPad.append(
            $('<div>')
                .css({
                    'left': army.x * 2 + 'px',
                    'top': army.y * 2 + 'px',
                    'background': mapPlayersColors[color].minimapColor,
                    'z-index': 10
                })
                .attr('id', army.armyId)
                .addClass('a')
        );

        players[color].armies[army.armyId] = army;
    },
    showFirst: function (color) {
        for (i in players[color].armies) {
            zoomer.lensSetCenter(players[color].armies[i].x * 40, players[color].armies[i].y * 40);
            return;
        }
        zoomer.lensSetCenter(30, 30);
    },
    removeFromSkipped: function (armyId) {
        if (isTruthful(Army.skippedArmies[armyId])) {
            delete Army.skippedArmies[armyId];
        }
    },
    skip: function () {
        if (!my.turn) {
            return;
        }

        if (Gui.lock) {
            return;
        }

        if (Army.selected) {
            Sound.play('skip');
            Army.skippedArmies[Army.selected.armyId] = 1;
            Army.deselect();
            this.findNext();
        }
    },
    findNext: function () {
        if (!my.turn) {
            return;
        }

        if (Gui.lock) {
            return;
        }

        Army.deselect();

        var reset = true;

        for (armyId in players[my.color].armies) {
//            if (notSet(players[my.color].armies[armyId].armyId)) {
//                continue;
//            }
            if (players[my.color].armies[armyId].moves == 0) {
                continue;
            }

            if (isTruthful(Army.skippedArmies[armyId])) {
                continue;
            }

            if (isTruthful(Army.quitedArmies[armyId])) {
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
            if (!Army.selected) {
                Sound.play('error');
            }
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
    updateInfo: function (a) {
        $('#name').html(a.name);
        $('#attack').html(a.attack);
        $('#defense').html(a.defense);
        $('#moves').html(a.moves);
    },
    select: function (a, center) {
        castlesAddCursorWhenSelectedArmy();
        armiesAddCursorWhenSelectedArmy();
        myCastlesRemoveCursor();

        this.removeFromSkipped(a.armyId);

        this.unfortify(a.armyId);

        $('#army' + a.armyId)
            .css('background', 'url(/img/game/units/' + a.color + '/border_army.gif)');

        this.updateInfo(a);
        $('#name').html('Army');

        $('#splitArmy').removeClass('buttonOff');
        $('#deselectArmy').removeClass('buttonOff');
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

        if (Castle.getMy(a.x, a.y)) {
            $('#razeCastle').removeClass('buttonOff');
            $('#buildCastleDefense').removeClass('buttonOff');
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
            if (Army.selected.heroKey) {
                $('#army' + Army.selected.armyId + ' .unit img').attr('src', Hero.getImage(Army.selected.color));
            } else {
                $('#army' + Army.selected.armyId + ' .unit img').attr('src', Unit.getImage(Army.selected.soldiers[Army.selected.soldierKey].unitId, Army.selected.color));
            }

            Army.selected.heroSplitKey = null
            Army.selected.soldierSplitKey = null

            Army.selected.skippedHeroes = {};
            Army.selected.skippedSoldiers = {};

            Army.deselected = Army.selected;

            $('#army' + Army.selected.armyId)
                .css('background', 'none');
            $('#army' + Army.selected.armyId + ' .unit')
                .css('background', 'none');

            board.css('cursor', 'url(/img/game/cursor.png), default');
        }
        Army.selected = null;
        $('.path').remove();
        $('#splitArmy').addClass('buttonOff');
        $('#deselectArmy').addClass('buttonOff');
        $('#armyStatus').addClass('buttonOff');
        $('#skipArmy').addClass('buttonOff');
        $('#quitArmy').addClass('buttonOff');
        $('#searchRuins').addClass('buttonOff');
        $('#razeCastle').addClass('buttonOff');
        $('#buildCastleDefense').addClass('buttonOff');
        $('#showCastle').addClass('buttonOff');
        $('#showArtifacts').addClass('buttonOff');
        $('#disbandArmy').addClass('buttonOff');
        Message.remove();
    },
    fortify: function () {
        if (!my.turn) {
            return;
        }
        if (Gui.lock) {
            return;
        }
        if (Army.selected) {
            Websocket.fortify(Army.selected.armyId);
            Army.quitedArmies[Army.selected.armyId] = 1;
            Army.deselect();
            Army.findNext();
        }
    },
    unfortify: function (armyId) {
        if (isComputer(Turn.color)) {
            return;
        }

        if (isTruthful(Army.quitedArmies[armyId])) {
            Websocket.unfortify(armyId, 0);
            delete Army.quitedArmies[armyId];
        }
    }


}

function myArmyMouse(armyId) {
    if (Gui.lock) {
        return;
    }

    if (my.turn && !Army.selected) {
        $('#army' + armyId + ' *').css('cursor', 'url(/img/game/cursor_select.png), default');
        $('#army' + armyId).css('cursor', 'url(/img/game/cursor_select.png), default');
    } else {
        $('#army' + armyId + ' *').css('cursor', 'url(/img/game/cursor.png), default');
        $('#army' + armyId).css('cursor', 'url(/img/game/cursor.png), default');
    }
}

function armiesAddCursorWhenSelectedArmy() {
    $('.army:not(.' + my.color + ') *').css('cursor', 'url(/img/game/cursor_attack.png) 13 16, crosshair');
}

function armiesAddCursorWhenUnselectedArmy() {
    $('.army:not(.' + my.color + ') *').css('cursor', 'url(/img/game/cursor.png), default');
}

function enemyArmyMouse(element, x, y) {
    element
        .mouseover(function () {
            if (Gui.lock) {
                return;
            }
            if (my.turn && Army.selected) {
//                selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
                var castleId = Castle.getEnemy(x, y);
                if (castleId !== null) {
                    Castle.changeFields(castleId, 'g');
                }
                fields[y][x] = 'g';
            }
        })
        .mousemove(function () {
            if (Gui.lock) {
                return;
            }
            if (my.turn && Army.selected) {
//                selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
                var castleId = Castle.getEnemy(x, y);
                if (castleId !== null) {
                    Castle.changeFields(castleId, 'g');
                }
                fields[y][x] = 'g';
            }
        })
        .mouseout(function () {
            var castleId = Castle.getEnemy(x, y);
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

    if (Castle.getEnemy(a.x, a.y) !== null) {
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
    },
    findMy: function () {
        for (armyId in players[my.color].armies) {
            for (j in players[my.color].armies[armyId].heroes) {
                return players[my.color].armies[armyId].heroes[j].heroId;
            }
        }
    }
}