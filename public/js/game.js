var tempX = 0;
var tempY = 0;
var newX = 0;
var newY = 0;

var turn = new Array();
var my = new Array();

var lock = true;

var selectedArmy = null;
var unselectedArmy = null;
var selectedEnemyArmy = null;

var zoomer;

var socket;

$(document).ready(function() {
    for(i in castles) {
        castles[i] = new createCastle(i);
    }
    for(player in players) {
        for(i in players[player].armies) {
            players[player].armies[i] = new army(players[player].armies[i], player);
        }
        for(i in players[player].castles) {
            castleOwner(i, player);
        }
    }
    setInterval ( 'wsPing()', 9000 );
    sp = $('.castle_' + turn.color);
    zoomer = new zoom(758, 670, sp.css('left'), sp.css('top'));

    board.mousedown(function(event) {
        if(lock) {
            return null;
        }

        switch (event.which) {
            case 1:
                if(selectedArmy != null) {
                    sendMove(cursorPosition());
                }
                break;
            case 2:
                alert('Middle mouse button pressed');
                break;
            case 3:
                unselectArmy();
                break;
            default:
                alert('You have a strange mouse');
        }
    });
    board.mousemove(function(e) {
        if(lock) {
            return null;
        }
        tempX = e.pageX;
        tempY = e.pageY;
        cursorPosition();
    });
    $('#nextArmy').click(function() {
        if(lock) {
            return null;
        }
        for(i in players[my.color].armies) {
            if(selectedArmy) {
                if(selectedArmy == players[my.color].armies[i]) {
                    unselectArmy();
                }
            } else {
                selectArmy(players[my.color].armies[i]);
                break;
            }
        }
    });
    $('#log').html('DISCONNECTED!');
    lock = false;
//     for(y in fields) {
//         for(x in fields[y]) {
//             board.append(
//                 $('<div>')
//                 .html(fields[y][x])
//                 .addClass('field')
//                 .css({
//                     left:(x*40)+'px',
//                     top:(y*40)+'px'
//                 })
//             );
//         }
//     }
});

function turnOn() {
    turn.myTurn = true;
    $('#nextTurn').html('<a href="javascript:sendNextTurn()">Next turn</a>');
    $('#nextArmy').html('Next army');
    sp = $('.castle_' + turn.color);
    zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
}

function turnOff() {
    turn.myTurn = false;
    unselectArmy();
    $('#nextTurn').html(turn.color + ' turn');
    $('#nextArmy').html('');
}

function changeTurn(playerId, color) {
    if(!color) {
        console.log('Turn "color" not set');
        return false;
    }
    if(!playerId) {
        console.log('Turn "playerId" not set');
        return false;
    }
    turn.color = color;
    turn.playerId = playerId;
    if(turn.playerId == my.playerId) {
        turnOn();
        startMyTurn();
        return 0;
    } else {
        turnOff();
        return 1;
    }
}
