// *** CASTLES ***

var Castle = {
    handle: function () {
        var castleId = $('.production').attr('id');

        if (!castleId) {
            Sound.play('error');
            Message.simple('Error');
            return;
        }

        if ($('input[name=resurrection]').is(':checked')) {
            Websocket.resurrection();
            return;
        }

        var unitId = $('input:radio[name=production]:checked').val();

        if ($('input[name=relocation]').is(':checked')) {
            Message.simple('Select castle to which you want to relocate this production');
            $('.castle.' + my.color)
                .unbind('click')
                .click(function () {
                    var relocationCastleId = $(this).attr('id').substring(6);
                    Websocket.production(castleId, unitId, relocationCastleId);
                });
            return;
        }

        if (unitId) {
            Websocket.production(castleId, unitId);
            return;
        }
    },
    updateMyProduction: function (unitId, castleId, relocationCastleId) {
        if (!my.turn) {
            return;
        }

        if (isTruthful(relocationCastleId)) {
            Message.simple('Production relocated')
        } else {
            Message.simple('Production set')
        }

        if (unitId === null) {
            Castle.removeHammer(castleId);
        } else {
            Castle.addHammer(castleId);
        }

        castles[castleId].currentProductionId = unitId;
        castles[castleId].currentProductionTurn = 0;

        if (relocationCastleId) {
            $('.castle.' + my.color).each(function () {
                var thisCastleId = $(this).attr('id').substring(6);

                $(this)
                    .unbind('click')
                    .click(function () {
                        Message.castle(thisCastleId)
                    });

                if (isSet(castles[thisCastleId].relocatedProduction) && isSet(castles[thisCastleId].relocatedProduction[castleId])) {
                    delete castles[thisCastleId].relocatedProduction[castleId];
                }
            })

            if (notSet(castles[relocationCastleId].relocatedProduction)) {
                castles[relocationCastleId].relocatedProduction = {};
            }
            castles[relocationCastleId].relocatedProduction[castleId] = {
                'currentProductionId': castles[castleId].currentProductionId,
                'currentProductionTurn': castles[castleId].currentProductionTurn
            }
        }
    },
    initMyProduction: function (castleId) {
        castles[castleId].currentProductionId = players[my.color].castles[castleId].productionId;
        castles[castleId].currentProductionTurn = players[my.color].castles[castleId].productionTurn;

        var relocationCastleId = players[my.color].castles[castleId].relocationCastleId;

        if (relocationCastleId) {
            if (notSet(castles[relocationCastleId].relocatedProduction)) {
                castles[relocationCastleId].relocatedProduction = {};
            }
            castles[relocationCastleId].relocatedProduction[castleId] = {
                'currentProductionId': castles[castleId].currentProductionId,
                'currentProductionTurn': castles[castleId].currentProductionTurn
            }
        }

        if (castles[castleId].currentProductionId) {
            Castle.addHammer(castleId);
        }
    },
    addName: function (castleId) {
        $('#castle' + castleId).append($('<div>').html(castles[castleId].name).addClass('name'));
    },
    addCrown: function (castleId) {
        $('#castle' + castleId).append($('<img>').attr('src', '/img/game/crown.png').addClass('crown'));
    },
    addShield: function (castleId) {
        $('#castle' + castleId).append($('<div>').css('background', 'url(/img/game/shield.png)').addClass('shield').html(castles[castleId].defense));
    },
    addHammer: function (castleId) {
        $('#castle' + castleId).append($('<img>').attr('src', '/img/game/hammer.png').addClass('hammer'));
    },
    removeHammer: function (castleId) {
        $('#castle' + castleId + ' .hammer').remove();
    },
    show: function () {
        if (Army.selected == null) {
            return;
        }
        var castleId = this.getMy(Army.selected.x, Army.selected.y);
        if (castleId) {
            Army.deselect();
            Message.castle(castleId);
        }
    },
    get: function (x, y) {
        for (castleId in castles) {
            var pos = castles[castleId].position;
            if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castleId;
            }
        }
        return false;
    },
    getMy: function (x, y) {
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
    },
    getEnemy: function (x, y) {
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
    },
    changeFields: function (castleId, type) {
        x = castles[castleId].x;
        y = castles[castleId].y;
        fields[y][x] = type;
        fields[y + 1][x] = type;
        fields[y][x + 1] = type;
        fields[y + 1][x + 1] = type;
    },
    createNeutral: function (castleId) {
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
                    Castle.onMouse(this.id, 'g');
                })
                .mousemove(function () {
                    Castle.onMouse(this.id, 'g')
                })
                .mouseout(function () {
                    Castle.onMouse(this.id, 'e')
                })
        );

        Castle.addShield(castleId);
        Castle.addName(castleId);

        Castle.changeFields(castleId, 'e');

        zoomPad.append(
            $('<div>').css({
                'left': castles[castleId].x * 2 + 'px',
                'top': castles[castleId].y * 2 + 'px'
            })
                .attr('id', 'c' + castleId)
                .addClass('c')
        );
    },
    owner: function (castleId, color) {
        var castle = $('#castle' + castleId);

        if (isSet(castles[castleId]) && castles[castleId].razed) {
            this.raze(castleId)
            alert('oho!')
            return;
        }

        if (castles[castleId].color) {
            castles[castleId].defense -= 1;
            if (castles[castleId].defense < 1) {
                castles[castleId].defense = 1;
            }
            castle.attr('title', castles[castleId].name + '(' + castles[castleId].defense + ')');
            $('#castle' + castleId + ' .shield').html(castles[castleId].defense);
        }

        if (color == my.color) {
            Castle.changeFields(castleId, 'c');
            castle
                .css({
                    'cursor': 'url(/img/game/cursor_castle.png), default'
                })
                .unbind('mouseover')
                .unbind('mousemove')
                .unbind('mouseout')
                .unbind('click')
                .click(function () {
                    Message.castle(castleId)
                });
        } else {
            Castle.changeFields(castleId, 'e');
            castle
                .unbind('mouseover')
                .unbind('mousemove')
                .unbind('mouseout')
                .unbind('click')
                .mouseover(function () {
                    Castle.onMouse(this.id, 'g');
                })
                .mousemove(function () {
                    Castle.onMouse(this.id, 'g')
                })
                .mouseout(function () {
                    Castle.onMouse(this.id, 'e');
                })
        }

        castle.removeClass()
            .addClass('castle ' + color)
            .css('background', 'url(/img/game/castles/' + color + '.png) center center no-repeat');

        $('#castle' + castleId + ' .crown').remove();
        $('#castle' + castleId + ' .hammer').remove();

        if (castles[castleId].capital && capitals[color] == castleId) {
            Castle.addCrown(castleId);
        }

        castles[castleId].color = color;

        $('#c' + castleId).css('background', mapPlayersColors[color].minimapColor);
    },
    raze: function (castleId) {
        if (castles[castleId].color == my.color) {
            incomeIncrement(-castles[castleId].income)
        }
        Castle.changeFields(castleId, 'g')
        $('#castle' + castleId).remove();
        $('#c' + castleId).remove();
        delete castles[castleId];
    },
    showFirst: function () {
        if ($('#castle' + capitals[my.color]).length) {
            var sp = $('#castle' + capitals[my.color]);
            zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
        } else if ($('#castle' + firstCastleId).length) {
            var sp = $('#castle' + firstCastleId);
            zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
        } else {
            Army.showFirst(my.color);
        }
    },
    onMouse: function (id, type) {
        Castle.changeFields(id.substring(6), type);
    },
    updateDefense: function (castleId, defenseMod) {
        castles[castleId].defense = castles[castleId].defensePoints + defenseMod;
        if (castles[castleId].defense < 1) {
            castles[castleId].defense = 1;
        }
        $('#castle' + castleId).attr('title', castles[castleId].name + '(' + castles[castleId].defense + ')');
        $('#castle' + castleId + ' .shield').html(castles[castleId].defense);
    },
    updateCurrentProductionTurn: function (castleId, productionTurn) {
        castles[castleId].currentProductionTurn = productionTurn;
    }
}


function castlesAddCursorWhenSelectedArmy() {
    $('.castle:not(.' + my.color + ')').css('cursor', 'url(/img/game/cursor_attack.png), crosshair');
}

function castlesAddCursorWhenUnselectedArmy() {
    $('.castle:not(.' + my.color + ')').css('cursor', 'url(/img/game/cursor.png), default');
}

function myCastlesAddCursor() {
    $('.castle.' + my.color).css('cursor', 'url(/img/game/cursor_castle.png), crosshair');
}

function myCastlesRemoveCursor() {
    $('.castle.' + my.color).css('cursor', 'url(/img/game/cursor.png), default');
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


