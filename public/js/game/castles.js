// *** CASTLES ***

function castleFields(castleId, type) {
    x = castles[castleId].x;
    y = castles[castleId].y;
    fields[y][x] = type;
    fields[y + 1][x] = type;
    fields[y][x + 1] = type;
    fields[y + 1][x + 1] = type;
}

function createNeutralCastle(castleId) {
    castles[castleId].defense = castles[castleId].defensePoints;
    castles[castleId].color = null;

    board.append(
        $('<div>')
            .addClass('castle')
            .attr({
                id: 'castle' + castleId,
                title: castles[castleId].name + ' (' + castles[castleId].defense + ')'
            })
            .css({
                left: (castles[castleId].x * 40) + 'px',
                top: (castles[castleId].y * 40) + 'px'
            })
            .mouseover(function () {
                castleOnMouse(this.id, 'g');
            })
            .mousemove(function () {
                castleOnMouse(this.id, 'g')
            })
            .mouseout(function () {
                castleOnMouse(this.id, 'e')
            })
    );
    castleFields(castleId, 'e');
    mX = castles[castleId].x * 2;
    mY = castles[castleId].y * 2;
    zoomPad.append(
        $('<div>').css({
            'left': mX + 'px',
            'top': mY + 'px'
        })
            .attr('id', 'c' + castleId)
            .addClass('c')
    );
}

function castleOnMouse(id, type) {
    castleFields(id.substring(6), type);
}

function castlesAddCursorWhenSelectedArmy() {
    $('.castle:not(.' + my.color + ')').css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
}

function castlesAddCursorWhenUnselectedArmy() {
    $('.castle:not(.' + my.color + ')').css('cursor', 'url(../img/game/cursor.png), default');
}

function myCastlesAddCursor() {
    $('.castle.' + my.color).css('cursor', 'url(../img/game/cursor_castle.png), crosshair');
}

function myCastlesRemoveCursor() {
    $('.castle.' + my.color).css('cursor', 'url(../img/game/cursor.png), default');
}

function castleOwner(castleId, color) {
    var castle = $('#castle' + castleId);

    if (typeof castles[castleId] != 'undefined' && castles[castleId].razed) {
        castle.remove();
        $('#c' + castleId).remove();
        delete castles[castleId];
        return;
    }

    if (castles[castleId].color) {
        castles[castleId].defense -= 1;
        if (castles[castleId].defense < 1) {
            castles[castleId].defense = 1;
        }
        castle.attr('title', castles[castleId].name + '(' + castles[castleId].defense + ')');
    }

    if (color == my.color) {
        castleFields(castleId, 'c');
        castle
            .css({
                'cursor': 'url(../img/game/cursor_castle.png), default'
            })
            .unbind('mouseover')
            .unbind('mousemove')
            .unbind('mouseout')
            .unbind('click')
            .click(function () {
                castleM(castleId, color)
            });
    } else {
        castleFields(castleId, 'e');
        castle
            .unbind('mouseover')
            .unbind('mousemove')
            .unbind('mouseout')
            .unbind('click')
            .mouseover(function () {
                castleOnMouse(this.id, 'g');
            })
            .mousemove(function () {
                castleOnMouse(this.id, 'g')
            })
            .mouseout(function () {
                castleOnMouse(this.id, 'e');
            })
    }

    castle.removeClass()
        .addClass('castle ' + color)
        .html('')
        .css('background', 'url(../img/game/castle_' + color + '.png) center center no-repeat');

    castles[castleId].color = color;

    $('#c' + castleId).css('background', getColor(color));
}

function setMyCastleProduction(castleId) {
    castles[castleId].currentProduction = players[my.color].castles[castleId].production;
    castles[castleId].currentProductionTurn = players[my.color].castles[castleId].productionTurn;
    if (castles[castleId].currentProduction) {
        $('#castle' + castleId).html($('<img>').attr('src', '../img/game/castle_production.png').css('float', 'right'));
    }
}

function updateCastleCurrentProductionTurn(castleId, productionTurn) {
    castles[castleId].currentProductionTurn = productionTurn;
}

function updateCastleDefense(castleId, defenseMod) {
    castles[castleId].defense = castles[castleId].defensePoints + defenseMod;
    if (castles[castleId].defense < 1) {
        castles[castleId].defense = 1;
    }
    $('#castle' + castleId).attr('title', castles[castleId].name + '(' + castles[castleId].defense + ')');
}

function isEnemyCastle(x, y) {
    for (castleId in castles) {
        if (castles[castleId].color == my.color) {
            continue;
        }
        var pos = castles[castleId].position;
        if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
            return castleId;
        }
    }
    return null;
}

function isMyCastle(x, y) {
    for (castleId in castles) {
        if (castles[castleId].color != my.color) {
            continue;
        }
        var pos = castles[castleId].position;
        if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
            return castleId;
        }
    }
    return false;
}

function getMyCastleDefenseFromPosition(x, y) {
    for (castleId in castles) {
        if (castles[castleId].color == my.color) {
            var pos = castles[castleId].position;
            if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castles[castleId].defense;
            }
        }
    }
    return 0;
}

function showFirstCastle() {
    var sp = $('#castle' + firstCastleId);
    if ($(sp).length) {
        zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
    } else {
        showFirstArmy(my.color);
    }
}