var Move = {
    start: function (r) {
        console.log('move start 0')
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

        this.loop(r, null);
        console.log('move start 1')
    },
    loop: function (r, xy) {
        console.log('move loop 0')
        for (step in r.path) {
            break;
        }

        if (isSet(r.path[step])) {
            zoomer.setCenterIfOutOfScreen(r.path[step].x * 40, r.path[step].y * 40);

            $('#army' + r.oldArmyId)
                .animate({
                    left: (r.path[step].x * 40) + 'px',
                    top: (r.path[step].y * 40) + 'px'
                }, 200, function () {
                    searchTower(r.path[step].x, r.path[step].y);
                    xy = r.path[step];
                    delete r.path[step];
                    Move.loop(r, xy);
                });
        } else {
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
                    board.append($('<div>')
                        .addClass('war')
                        .css({
                            top: 40 * r.attackerArmy.y - 42 + 'px',
                            left: 40 * r.attackerArmy.x - 41 + 'px'
                        }));
                }

                Message.battle(r);
            } else {
                Move.end(r);
            }
        }
        console.log('move loop 1')
    },
    end: function (r) {
        console.log('move end 0')

        AStar.x = players[r.attackerColor].armies[r.attackerArmy.armyId].x;
        AStar.y = players[r.attackerColor].armies[r.attackerArmy.armyId].y;

        searchTower(AStar.x, AStar.y);

        Army.init(r.attackerArmy, r.attackerColor);

        if (isDigit(r.ruinId)) {
            Ruin.update(r.ruinId, 1);
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

        for (i in r.deletedIds) {
            Army.delete(r.deletedIds[i].armyId, r.attackerColor, 1);
        }

        if (isDigit(r.castleId) && isTruthful(r.victory)) {
            Castle.owner(r.castleId, r.attackerColor);
        }

        if (players[r.attackerColor].computer) {
            Websocket.computer();
        } else if (r.attackerColor == my.color) {
            if (!r.castleId && isSet(players[r.attackerColor].armies[r.attackerArmy.armyId]) && players[r.attackerColor].armies[r.attackerArmy.armyId].moves) {
                unlock()
                Army.select(players[r.attackerColor].armies[r.attackerArmy.armyId])
            } else {
                Army.deselect()
                unlock()
                if (isDigit(r.castleId) && isTruthful(r.victory)) {
                    incomeIncrement(castles[r.castleId].income);
                    Message.castle()
                }
            }
            if (!Hero.findMy()) {
                $('#heroResurrection').removeClass('buttonOff')
            }
        }

        setTimeout('$(".war").remove()', 100);
        console.log('move end 1')
    }
}
