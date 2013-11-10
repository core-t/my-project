// *** TOWERS ***

var Tower = {
    create: function (towerId) {
        var title = 'Tower';
        board.append(
            $('<div>')
                .addClass('tower')
                .attr({
                    id: 'tower' + towerId,
                    title: title
                })
                .css({
                    left: (towers[towerId].x * 40) + 'px',
                    top: (towers[towerId].y * 40) + 'px',
                    background: 'url(/img/game/towers/' + towers[towerId].color + '.png) center center no-repeat'
                })
        );
    }

}

function isTowerAtPosition(x, y) {
    for (towerId in towers) {
        if (towers[towerId].x == x && towers[towerId].y == y) {
            return 1;
        }
    }
    return 0;
}

function searchTower(x, y) {
    for (towerId in towers) {
        if (towers[towerId].x == x && towers[towerId].y == y) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x - 1) && towers[towerId].y == (y - 1)) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x) && towers[towerId].y == (y - 1)) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x + 1) && towers[towerId].y == (y - 1)) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x - 1) && towers[towerId].y == (y)) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x + 1) && towers[towerId].y == (y)) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x - 1) && towers[towerId].y == (y + 1)) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x) && towers[towerId].y == (y + 1)) {
            changeTower(x, y, towerId);
            continue;
        }
        if (towers[towerId].x == (x + 1) && towers[towerId].y == (y + 1)) {
            changeTower(x, y, towerId);
            continue;
        }
    }
}

function changeTower(x, y, towerId) {
    if (fields[y][x] != 'e') {
        if (towers[towerId].color != Turn.shortName) {
            if (Turn.shortName == my.color || (my.game && players[Turn.shortName].computer)) {
                Websocket.addTower(towerId);
            }
            towers[towerId].color = Turn.shortName;
            $('#tower' + towerId).css('background', 'url(/img/game/towers/' + Turn.shortName + '.png) center center no-repeat');
        }
        return true;
    } else {
        return false;
    }
}

function countPlayerTowers(color) {
    var count = 0;
    for (i in towers) {
        if (towers[i].color == color) {
            count++;
        }
    }
    return count;
}