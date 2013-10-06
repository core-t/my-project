$(document)[0].oncontextmenu = function () {
    return false;
} // usuwa menu kontekstowe spod prawego przycisku


// *** OTHER ***

function turnOn() {
    makeMyCursorUnlock();
    skippedArmies = new Array();
    my.turn = true;
    $('#nextTurn').removeClass('buttonOff');
    $('#nextArmy').removeClass('buttonOff');
    showFirstCastle();
    turnM();
    titleBlink('Your turn!');
//    test();
}

function turnOff() {
    my.turn = false;
    unselectArmy();
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    makeMyCursorLock();
}

function changeTurn(color, nr) {
    if (!color) {
        console.log('Turn "color" not set');
        return;
    }

    turn.color = color;

    if (typeof nr != 'undefined') {
        turn.nr = nr;
    }

    Players.turn();

    timer.update();

    if (turn.color == my.color) {
        turnOn();
        wsStartMyTurn();
        return;
    } else {
        turnOff();
        return;
    }
}

function goldUpdate(gold) {
    $('#gold').html(gold);
}

function costsUpdate(gold) {
    $('#costs').html(gold);
}

function incomeUpdate(gold) {
    $('#income').html(gold);
}

//function updatePlayers(color) {
//    players[color].active = 2;
//}

function setLock() {
    lock = true;
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    makeMyCursorLock();
}

function unlock() {
    if (my.turn) {
        lock = false;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        makeMyCursorUnlock();
    }
}

function makeMyCursorUnlock() {
    $('body *').css('cursor', 'url(../img/game/cursor.png), auto');
    $('#chatBox #msg').css('cursor', 'auto');
    $('.button').css('cursor', 'url(../img/game/cursor_pointer.png), pointer');
    $('#surrender').css('cursor', 'url(../img/game/cursor_pointer.png), pointer');
    $('.zoomPup').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('#map').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('.c').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('.a').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    myCastlesAddCursor();
}

function makeMyCursorLock() {
    $('body *').css('cursor', 'url(../img/game/cursor_hourglass.png), wait');
    $('#chatBox *').css('cursor', 'url(../img/game/cursor.png), auto');
    $('#chatBox #msg').css('cursor', 'auto');
    $('#chatBox #send').css('cursor', 'url(../img/game/cursor_pointer.png), pointer');
    $('.zoomPup').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('#map').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('.c').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
    $('.a').css('cursor', 'url(../img/game/lupa.png) 13 13, crosshair');
}

function titleBlink(msg) {
    if (timeoutId) {
        clearInterval(timeoutId);
    }
    timeoutId = setInterval(function () {
        if (document.title == msg) {
            document.title = '...';
        } else {
            document.title = msg;
        }
    });
    $(document).bind("mousemove keypress", function () {
        clearInterval(timeoutId);
        document.title = documentTitle;
        window.onmousemove = null;
    });
//    window.onmousemove = function () {
//        clearInterval(timeoutId);
//        document.title = documentTitle;
//        window.onmousemove = null;
//    };
}

function getColor(color) {
    switch (color) {
        case 'green':
            return '#00db00';
        case 'selentines':
            return '#000CFF';
        case 'horse_lords':
            return '#00BFFF';
        default:
            return color;
    }
}

function makeTime() {
    var d = new Date();
    var minutes = d.getMinutes();
    if (minutes.length == 1) {
        minutes = '0' + minutes
    }
    return d.getHours() + ':' + minutes;
}

function getISODateTime(d) {
    // padding function
    var s = function (a, b) {
        return(1e15 + a + "").slice(-b)
    };

    // default date parameter
    if (typeof d === 'undefined' || !d) {
        d = new Date();
    } else {
        d = new Date(d.substr(0, 4), d.substr(5, 2), d.substr(8, 2), d.substr(11, 2), d.substr(14, 2), d.substr(17, 2));
    }

    // return ISO datetime
    return d.getFullYear() + '-' +
        s(d.getMonth() + 1, 2) + '-' +
        s(d.getDate(), 2) + ' ' +
        s(d.getHours(), 2) + ':' +
        s(d.getMinutes(), 2) + ':' +
        s(d.getSeconds(), 2);
}

function isDigit(val) {
    if (typeof val == 'undefined') {
        return false;
    }
    var intRegex = /^\d+$/;
    if (intRegex.test(val)) {
        return true;
    } else {
        return false;
    }
}

function isTruthful(val) {
    if (typeof val != 'undefined' && val) {
        return true;
    }
    return false;
}

function isSet(val) {
    if (typeof val == 'undefined') {
        return false;
    } else {
        return true;
    }
}

function notSet(val) {
    return !isSet(val);
}

function isComputer(color) {
    return players[color].computer;
}

function prepareButtons() {
    zoomPad = $(".zoomPad");
    board = $("#board");

    $('#send').click(function () {
        wsChat();
    });
    $('#msg').keypress(function (e) {
        if (e.which == 13) {
            wsChat();
        }
    });
    $('#nextTurn').click(function () {
        nextTurnM()
    });
    $('#surrender').click(function () {
        Message.surrender()
    });
    $('#nextArmy').click(function () {
        findNextArmy()
    });
    $('#skipArmy').click(function () {
        skipArmy()
    });
    $('#quitArmy').click(function () {
        fortifyArmy()
    });
    $('#splitArmy').click(function () {
        if (selectedArmy) {
            splitArmyM()
        }
    });
    $('#armyStatus').click(function () {
        if (selectedArmy) {
            armyStatusM()
        }
    });
    $('#disbandArmy').click(function () {
        if (selectedArmy) {
            disbandArmyM()
        }
    });
    $('#unselectArmy').click(function () {
        if (selectedArmy) {
            unselectArmy();
        }
    });
    $('#searchRuins').click(function () {
        wsSearchRuins()
    });
    $('#showArtifacts').click(function () {
        Message.showArtifacts();
    });
    $('#test').click(function () {
        test()
    });
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    $('#skipArmy').addClass('buttonOff');
    $('#quitArmy').addClass('buttonOff');
    $('#splitArmy').addClass('buttonOff');
    $('#disbandArmy').addClass('buttonOff');
    $('#searchRuins').addClass('buttonOff');
}

function fieldsCopy() {
    for (y in fieldsOryginal) {
        fields[y] = new Array();
        for (x in fieldsOryginal[y]) {
            fields[y][x] = fieldsOryginal[y][x];
        }
    }
}

function adjustGui() {
    documentWidth = $(document).width();
    documentHeigh = $(document).height() - 35;
    $('.zoomWindow').css('height', documentHeigh + 'px');

    messageLeft = documentWidth / 2 - 160;

    var left = documentWidth - 237;
    var chatLeft = documentWidth - 507;
    var chatTop = documentHeigh - 169;
    $('#chatBox').css({
        'left': chatLeft + 'px',
        'top': chatTop + 'px'
    });
    var goldBoxLeft = left / 2;
    $('#goldBox').css({
        'left': goldBoxLeft + 'px'
    });
    $('#playersBox').css({
        'left': left + 'px'
    });
    $('#armyBox').css({
        'left': left + 'px'
    });
//    $('#timerBox').css({
//        'left': left + 'px'
//    });

    var zoomPadLayoutHeight = parseInt($('#map').css('height'));

    $('.zoomPadLayout').css({
        width: parseInt($('#map').css('width')) + 20 + 'px',
        height: zoomPadLayoutHeight + 40 + 'px'
    });

    $('#terrain').css('top', zoomPadLayoutHeight + 5 + 'px');

    if (!zoomer) {
        zoomer = new zoom(documentWidth, documentHeigh);
    } else {
        zoomer.setSettings(parseInt($('.zoomWindow').css('width')), parseInt($('.zoomWindow').css('height')));
        zoomer.lens.setdimensions();
    }
}

function artifactsReformat() {
    for (i in artifacts) {
        for (j in artifacts[i]) {
            if (artifacts[i][j]) {
                console.log(j);
                console.log(artifacts[i][j]);
            }
        }
        break;
    }
}