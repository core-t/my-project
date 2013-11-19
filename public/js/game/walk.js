function move(r, computer) {
    play('move');
    Message.remove();
    if (typeof r.path[1] == 'undefined') {

        zoomer.lensSetCenter(r.attackerArmy.x * 40, r.attackerArmy.y * 40);
    } else {
        armyFields(players[r.attackerColor].armies['army' + r.attackerArmy.armyId]);
        zoomer.lensSetCenter(r.path[1].x * 40, r.path[1].y * 40);
    }

    unfortifyArmy(r.attackerArmy.armyId);

    walk(r, null, computer);
}

function walk(r, xy, computer) {
    var i;

    for (i in r.path) {
        break;
    }

    if (typeof r.path[i] == 'undefined') {
        //        console.log(data);
        if (xy) {
            zoomer.lensSetCenter(xy.x * 40, xy.y * 40);
        }

        if (isTruthful(r.battle)) {
            play('fight');

            if (isTruthful(r.castleId)) {
                board.append($('<div>').addClass('war').css({
                    top: 40 * castles[r.castleId].y - 12 + 'px',
                    left: 40 * castles[r.castleId].x - 11 + 'px'
                }));
            } else {
                if (isSet(r.attackerArmy.x)) {
                    var x = r.attackerArmy.x;
                    var y = r.attackerArmy.y;
                } else if (isSet(r.defenderArmy.x)) {
                    var x = r.attackerArmy.x;
                    var y = r.defenderArmy.y;
                }
                board.append($('<div>').addClass('war').css({
                    top: 40 * y - 42 + 'px',
                    left: 40 * x - 41 + 'px'
                }));
            }

            if (isTruthful(r.defenderArmy) && isTruthful(r.defenderColor)) {
                if (isTruthful(r.victory)) {
                    for (i in r.defenderArmy) {
                        deleteArmy('army' + r.defenderArmy[i].armyId, r.defenderColor, 1);
                    }
                } else {
                    for (i in r.defenderArmy) {
                        players[r.defenderColor].armies['army' + r.defenderArmy[i].armyId] = new army(r.defenderArmy[i], r.defenderColor);
                    }
                }
            }

            if (isDigit(r.castleId) && isTruthful(r.victory)) {
                castleOwner(r.castleId, r.attackerColor);
            }

            Message.battle(r, function () {
                walkEnd(r, r.attackerColor, r.deletedIds, computer);
            });
        } else {
            walkEnd(r, r.attackerColor, r.deletedIds, computer);
        }

        return;
    } else {
        zoomer.setCenterIfOutOfScreen(r.path[i].x * 40, r.path[i].y * 40);
        $('#army' + r.oldArmyId).animate({
                left: (r.path[i].x * 40) + 'px',
                top: (r.path[i].y * 40) + 'px'
            }, 200,
            function () {
                if (typeof r.path[i] == 'undefined') {
                    console.log('coś tu nie gra');
                    console.log(r);
                } else {
                    searchTower(r.path[i].x, r.path[i].y);
                    xy = r.path[i];
                    delete r.path[i];
                    walk(r, xy, r.attackerColor, r.deletedIds, computer);
                }
            });
    }
}

function walkEnd(r, computer) {
    players[r.attackerColor].armies['army' + r.attackerArmy.armyId] = new army(r.attackerArmy, r.attackerColor);
    newX = players[r.attackerColor].armies['army' + r.attackerArmy.armyId].x;
    newY = players[r.attackerColor].armies['army' + r.attackerArmy.armyId].y;

    searchTower(newX, newY);

    if (isDigit(r.ruinId)) {
        Ruin.update(r.ruinId, 1);
    }

    if (typeof r.deletedIds == 'undefined') {
        console.log('?');
        return;
    }

    for (i in r.deletedIds) {
        deleteArmy('army' + r.deletedIds[i]['armyId'], r.attackerColor, 1);
    }

    if (typeof computer != 'undefined') {
        Websocket.computer();
    }

    if (r.attackerColor == my.color) {
        if (!r.castleId && players[r.attackerColor].armies['army' + r.attackerArmy.armyId].moves) {
            unlock();
            selectArmy(players[r.attackerColor].armies['army' + r.attackerArmy.armyId]);
        } else {
            unlock();
        }
    }
}

