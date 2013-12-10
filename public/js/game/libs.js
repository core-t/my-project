$(document)[0].oncontextmenu = function () {
    return false;
} // usuwa menu kontekstowe spod prawego przycisku


// *** OTHER ***

function goldUpdate(gold) {
    my.gold = gold
    $('#gold #value').fadeOut(300, function () {
        $('#gold #value').html(gold)
        $('#gold #value').fadeIn()
        if (gold > 1000) {
            $('#heroHire').removeClass('buttonOff')
        } else {
            $('#heroHire').addClass('buttonOff')
        }
    })

}

function goldIncrement(gold) {
    gold += my.gold
    goldUpdate(gold)
}

function costsUpdate(gold) {
    my.costs = gold
    $('#costs #value').fadeOut(300, function () {
        $('#costs #value').html(gold)
        $('#costs #value').fadeIn(300)
    })
}

function costIncrement(gold) {
    gold += my.costs
    costsUpdate(gold)
}

function incomeUpdate(gold) {
    my.income = gold
    $('#income #value').fadeOut(300, function () {
        $('#income #value').html(gold)
        $('#income #value').fadeIn(300)
    })
}

function incomeIncrement(gold) {
    gold += my.income
    incomeUpdate(gold)
}

function setLock() {
    Gui.lock = true;
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    makeMyCursorLock();
}

function unlock() {
    if (my.turn) {
        Gui.lock = false;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        makeMyCursorUnlock();
    }
}

function makeMyCursorUnlock() {
    board.css('cursor', 'url(/img/game/cursor.png), auto')
    $('.tower').css('cursor', 'url(/img/game/cursor.png), auto')
    $('.ruin').css('cursor', 'url(/img/game/cursor.png), auto')
    $('.castle').css('cursor', 'url(/img/game/cursor.png), auto')
    $('.army').css('cursor', 'url(/img/game/cursor.png), auto')

    myCastlesAddCursor();
}

function makeMyCursorLock() {
    board.css('cursor', 'url(/img/game/cursor_hourglass.png), wait')
    $('.tower').css('cursor', 'url(/img/game/cursor_hourglass.png), auto')
    $('.ruin').css('cursor', 'url(/img/game/cursor_hourglass.png), auto')
    $('.castle').css('cursor', 'url(/img/game/cursor_hourglass.png), auto')
    $('.army').css('cursor', 'url(/img/game/cursor_hourglass.png), auto')
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
}

function makeTime() {
    var d = new Date();
    var minutes = d.getMinutes();

    if (minutes < 10) {
        minutes = '0' + minutes;
    }

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
    if (isSet(val) && val) {
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

function fieldsCopy() {
    for (y in fieldsOryginal) {
        fields[y] = new Array();
        for (x in fieldsOryginal[y]) {
            fields[y][x] = fieldsOryginal[y][x];
        }
    }
}

function makeId(lenght) {
    var text = '';
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i = 0; i < lenght; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function artifactsReformat() {
//    for (i in artifacts) {
//        for (j in artifacts[i]) {
//            if (artifacts[i][j]) {
//                console.log(j);
//                console.log(artifacts[i][j]);
//            }
//        }
//        break;
//    }
}