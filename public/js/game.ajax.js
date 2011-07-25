function sendNextTurn() {
    lock = true;
    if(typeof socket == 'undefined') {
        alert('Socket disconnected!');
        return null;
    }
    $.getJSON(urlNextTurn, function(result) {
        unselectArmy();
        changeTurn(result.playerId, result.color);
        wsTurn(result.playerId, result.color);
    });
}

function sendMove(movesSpend) {
    if(movesSpend == 0) {
        return null;
    }
    if(typeof socket == 'undefined') {
        alert('Socket disconnected!');
        return null;
    }
    unselectArmy();
    if(unselectedArmy.x == newX && unselectedArmy.y == newY) {
        return null;
    }
    lock = true;
    var castleId = isEnemyCastle(newX, newY)
    if(castleId) {
        var vectorLenth = getVectorLenth(unselectedArmy.x, unselectedArmy.y, newX, newY);
        if(vectorLenth >= 80) {
            lock = false;
            return null;
        }
        if(unselectedArmy.moves < (movesSpend + 1)) {
            console.log(movesSpend);
            lock = false;
            return null;
        }
        $.getJSON(urlFightCastle + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/cid/' + castleId, function(result) {
            var enemyArmies = getEnemyCastleGarrison(castleId);
            if(result.victory) {
                deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
                players[my.color].armies['army'+unselectedArmy.armyId] = new army(result, my.color);
                newX = players[my.color].armies['army'+unselectedArmy.armyId].x;
                newY = players[my.color].armies['army'+unselectedArmy.armyId].y;
                wsArmyAdd(unselectedArmy.armyId);

                // delete enemy - find enemy at position?
//                 var armiesToDelete = getEnemyCastleGarrison(castleId);
                for(i in enemyArmies) {
                    deleteArmy('army' + enemyArmies[i].armyId, enemyArmies[i].color);
                    wsArmyDelete(enemyArmies[i].armyId, enemyArmies[i].color);
                }

                wsCastleOwner(castleId, my.color);
                castleOwner(castleId, my.color);
            } else {
                for(i in enemyArmies) {
                    console.log(enemyArmies[i]);
                }
                deleteArmy('army' + unselectedArmy.armyId, my.color);
                wsArmyDelete(unselectedArmy.armyId, my.color);
            }
            battle(result.battle, unselectedArmy, enemyArmies);
            lock = false;
        });
    } else if(selectedEnemyArmy && selectedEnemyArmy.x == newX && selectedEnemyArmy.y == newY) {
        var vectorLenth = getVectorLenth(unselectedArmy.x, unselectedArmy.y, newX, newY);
        if(vectorLenth >= 80) {
            lock = false;
            unselectEnemyArmy();
            return null;
        }
        if(unselectedArmy.moves < (movesSpend + 1)) {
            console.log(movesSpend);
            lock = false;
            unselectEnemyArmy();
            return null;
        }
        $.getJSON(urlFightArmy + '/armyId/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY +  '/eid/' + selectedEnemyArmy.armyId, function(result) {
            if(result.victory == true) {
                deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
                players[my.color].armies['army'+unselectedArmy.armyId] = new army(result, my.color);
                newX = players[my.color].armies['army'+unselectedArmy.armyId].x;
                newY = players[my.color].armies['army'+unselectedArmy.armyId].y;
                wsArmyAdd(unselectedArmy.armyId);

                deleteArmyByPosition(newX, newY, selectedEnemyArmy.color);
                wsArmyDelete(selectedEnemyArmy.armyId, selectedEnemyArmy.color);

                selectArmy(players[my.color].armies['army'+unselectedArmy.armyId]);
            } else if(result.victory == false) {
                deleteArmyByPosition(unselectedArmy.x, unselectedArmy.y, my.color);
                wsArmyDelete(unselectedArmy.armyId, my.color);
                getAddArmy(selectedEnemyArmy.armyId);
                wsArmyAdd(selectedEnemyArmy.armyId);
            }

            battle(result.battle, unselectedArmy, {0:selectedEnemyArmy});
            unselectEnemyArmy();
            lock = false;
        });
    } else {
        $.getJSON(urlMove + '/aid/' + unselectedArmy.armyId + '/x/' + newX + '/y/' + newY, function(result) {
            if(result) {
                walk(result, players[my.color].armies['army'+unselectedArmy.armyId].element);
            }
        });
    }
    return true;
}

function battle(battle, a, def) {
    console.log(a);
    console.log(d);
    var attack = $('<div>').addClass('battle attack');
    for(i in a.soldiers) {
        var img = a.soldiers[i].name.replace(' ', '_').toLowerCase();
        attack.append(
            $('<img>').attr({
                'src':'/img/game/' + img + '_' + a.color + '.png',
                'id':'unit'+a.soldiers[i].soldierId
            })
        );
    }
    for(i in a.heroes) {
        attack.append(
            $('<img>').attr({
                'src':'/img/game/hero_' + a.color + '.png',
                'id':'hero'+a.heroes[i].heroId
            })
        );
    }
    $('#game').after(
        $('<div>')
        .addClass('message')
        .append(attack)
        .append($('<p>').html('VS').addClass('center'))
    );
    var h = 0;
    for(j in def) {
        var d = def[j];
        h++;
        var defense = $('<div>').addClass('battle defense');
        for(i in d.soldiers) {
            var img = d.soldiers[i].name.replace(' ', '_').toLowerCase();
            defense.append(
                $('<img>').attr({
                    'src':'/img/game/' + img + '_' + d.color + '.png',
                    'id':'unit'+d.soldiers[i].soldierId
                })
            );
        }
        $('.message').append(defense);
    }
    var height = 62 + 31 + 14 + h * 31; //67
    $('.message')
    .append($('<div>').addClass('go').html('OK').click(function(){$('.message').remove()}))
    .css('height',height+'px');

    console.log(battle);
}

function walk(result, el) {
    for(i in result.path) {
        break;
    }
    if(typeof result.path[i] == 'undefined') {
        deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        newX = players[my.color].armies['army'+result.armyId].x;
        newY = players[my.color].armies['army'+result.armyId].y;
        wsArmyAdd(result.armyId);
        lock = false;
        return null;
    } else {
        wsArmyMove(result.path[i].x, result.path[i].y, unselectedArmy.armyId);
        el.css({
            display:'none',
            left: result.path[i].x + 'px',
            top: result.path[i].y + 'px'
        });
        zoomer.lensSetCenter(result.path[i].x, result.path[i].y);
        el.fadeIn(1, function() {
            delete result.path[i];
            walk(result, el);
        });
    }
}

function startMyTurn() {
    $.getJSON(urlStartMyTurn, function(result) {
        for(i in result) {
            players[my.color].armies[i] = new army(result[i], my.color);
            wsArmyAdd(result[i].armyId);
        }
        lock = false;
    });
}

function getAddArmy(armyId) {
    $.getJSON(urlAddArmy+'/armyId/'+armyId, function(result) {
        if(typeof result.armyId != 'undefined') {
            players[result.color].armies['army' + result.armyId] = new army(result, result.color);
            console.log('WS dodana ' + armyId + ' - ' + result.color);
        }
    });
}

function setProduction(castleId) {
    var unitId = getUnitId($("input:radio[name=production]:checked").val());
    if(!unitId) {
        return null;
    }
    $.getJSON(urlSetProduction+'/castleId/'+castleId+'/unitId/'+unitId, function(result) {
        if(result.set) {
            $('.message').remove();
            castles[castleId].currentProduction = unitId;
        }
    });
}
