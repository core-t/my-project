var Move = {
    start: function (r, computer) {

        switch (players[r.attackerColor].armies[r.attackerArmy.armyId].movementType) {
            case 'flying':
                Sound.play('fly');
                break;
            case 'swimming':
                Sound.play('swim');
                break;
            default:
                Sound.play('walk');
                break;
        }

        Message.remove();

        if (notSet(r.path[1])) {
            zoomer.lensSetCenter(r.attackerArmy.x * 40, r.attackerArmy.y * 40);
        } else {
            armyFields(players[r.attackerColor].armies[r.attackerArmy.armyId]);
            zoomer.lensSetCenter(r.path[1].x * 40, r.path[1].y * 40);
        }

        Army.unfortify(r.attackerArmy.armyId);

        this.loop(r, null, computer);
    },
    loop: function (r, xy, computer) {
        for (i in r.path) {
            break;
        }

        if (isSet(r.path[i])) {
            zoomer.setCenterIfOutOfScreen(r.path[i].x * 40, r.path[i].y * 40);

            $('#army' + r.oldArmyId)
                .animate({
                    left: (r.path[i].x * 40) + 'px',
                    top: (r.path[i].y * 40) + 'px'
                }, 200, function () {
                    searchTower(r.path[i].x, r.path[i].y);
                    xy = r.path[i];
                    delete r.path[i];
                    Move.loop(r, xy, computer);
                });
        } else {
            if (xy) {
                zoomer.lensSetCenter(xy.x * 40, xy.y * 40);
            }

            if (isTruthful(r.battle)) {
                Sound.play('fight');

                if (isTruthful(r.castleId)) {
                    board.append($('<div>')
                        .addClass('war')
                        .css({
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
                    board.append($('<div>')
                        .addClass('war')
                        .css({
                            top: 40 * y - 42 + 'px',
                            left: 40 * x - 41 + 'px'
                        }));
                }

                if (isTruthful(r.defenderArmy) && isTruthful(r.defenderColor)) {
                    if (isTruthful(r.victory)) {
                        for (i in r.defenderArmy) {
                            Army.delete(r.defenderArmy[i].armyId, r.defenderColor, 1);
                        }
                    } else {
                        for (i in r.defenderArmy) {
                            Army.init(r.defenderArmy[i], r.defenderColor);
                        }
                    }
                }

                if (isDigit(r.castleId) && isTruthful(r.victory)) {
                    Castle.owner(r.castleId, r.attackerColor);
                }

                setTimeout(function () {
                    Message.battle(r, computer);
                }, 2500);
            } else {
                Move.end(r, computer);
            }

            return;
        }
    },
    end: function (r, computer) {
        newX = players[r.attackerColor].armies[r.attackerArmy.armyId].x;
        newY = players[r.attackerColor].armies[r.attackerArmy.armyId].y;

        searchTower(newX, newY);

        Army.init(r.attackerArmy, r.attackerColor);

        if (isDigit(r.ruinId)) {
            Ruin.update(r.ruinId, 1);
        }

//        if (typeof r.deletedIds == 'undefined') {
//            console.log('?');
//            return;
//        }

        for (i in r.deletedIds) {
            Army.delete(r.deletedIds[i].armyId, r.attackerColor, 1);
        }

        if (isSet(computer)) {
            Websocket.computer();
        } else if (r.attackerColor == my.color) {
            if (!r.castleId && players[r.attackerColor].armies[r.attackerArmy.armyId].moves) {
                unlock();
                Army.select(players[r.attackerColor].armies[r.attackerArmy.armyId]);
            } else {
                unlock();
            }
        }

        setTimeout('$(".war").remove()', 1000);
    }
}




