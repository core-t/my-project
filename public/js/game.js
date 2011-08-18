var newX = 0;
var newY = 0;

var turn = new Array();
var my = new Array();

var lock = true;

var selectedArmy = null;
var unselectedArmy = null;
var parentArmyId = null;
var selectedEnemyArmy = null;
var nextArmy = null;
var nextArmySelected = false;

var zoomer;

var socket;

var nextArmy;

var channel = 'publicA';

var cursorDirection;

$(document).ready(function() {
    zoomer = new zoom(758, 670);
    initWS()
    lock = false;
    if(my.turn){
        turnOn();
    }else{
        turnOff();
    }
/*    for(y in fields) {
        for(x in fields[y]) {
            board.append(
                $('<div>')
                .html(fields[y][x])
                .addClass('field')
                .css({
                    left:(x*40)+'px',
                    top:(y*40)+'px'
                })
            );
        }
    }*/
    startM();
});

function turnOn() {
    my.turn = true;
    $('#nextTurn').removeClass('buttonOff');
    $('#nextArmy').removeClass('buttonOff');
    showFirstCastle();
    turnM();
}

function turnOff() {
    my.turn = false;
    unselectArmy();
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
}

function changeTurn(color, nr) {
    if(!color) {
        console.log('Turn "color" not set');
        return false;
    }
    $('#'+turn.color+'Turn').html('');
    turn.color = color;
    if(typeof nr != 'undefined'){
        turn.nr = nr;
    }
    $('#'+turn.color+'Turn').html('Turn ' + turn.nr);
    if(turn.color == my.color) {
        turnOn();
        startMyTurn();
        return 0;
    } else {
        turnOff();
        return 1;
    }
}

function initGame(){
//     subscribeChannel();
    for(i in castles) {
        new castleCreate(i);
    }
    for(i in ruins) {
        new ruinCreate(i);
    }
    for(color in players) {
        $('.'+color).css('display','block');
        $('#'+color+'Color').css({
            width:'50px',
            height:'18px',
            background:color,
            border:'1px solid #cecece'
        })
        for(i in players[color].armies) {
            players[color].armies[i] = new army(players[color].armies[i], color);
            if(color == my.color){
                var myArmies = true;
            }
        }
        for(i in players[color].castles) {
            castleOwner(i, color);
            if(color == my.color){
                var myCastles = true;
            }
        }
    }
    $('.castle').fadeIn(1);
//     auth();
    showFirstCastle();
    if(typeof myArmies == 'undefined' && typeof myCastles == 'undefined'){
        lostM();
    }
}

function goldUpdate(gold){
    $('#gold').html(gold);
}