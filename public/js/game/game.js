var newX = 0;
var newY = 0;

var turn = new Array();
var my = new Array();

var lock = true;

var selectedArmy = null;
var unselectedArmy = null;
var parentArmy = null;
var selectedEnemyArmy = null;
var nextArmy = null;
var nextArmySelected = false;
var armyToJoinId = null;
var skippedArmies = new Array();

var zoomer;

var nextArmy;

var cursorDirection;

var documentTitle = document.title;
var timeoutId = null;

$(document).ready(function() {
        terrain();

//    $(window).load(function () {
        lWSC = new jws.jWebSocketJSONClient();
        login();
        zoomer = new zoom(758, 670);
        setTimeout ( 'connect()', 1500 );
//    });
    
});

function turnOn() {
    skippedArmies = new Array();
    my.turn = true;
    $('#nextTurn').removeClass('buttonOff');
    $('#nextArmy').removeClass('buttonOff');
    showFirstCastle();
    turnM();
    titleBlink('Your turn!');
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
    $('.'+turn.color+' .turn').html('');
    turn.color = color;
    if(typeof nr != 'undefined'){
        turn.nr = nr;
    }
    $('.'+turn.color+' .turn').html('Turn >');
    $('#turnNumber').html(turn.nr);
    if(turn.color == my.color) {
        turnOn();
        startMyTurnA();
        return 0;
    } else {
        turnOff();
        return 1;
    }
}

function connect(){
    if(lWSC.isOpened()){
        lock = false;
        startM();
    }else{
        login();
        simpleM('Sorry, server is disconnected.');
        setTimeout ( 'connect()', 5000 );
    }
}

function startGame(){
    var myArmies = false;
    var myCastles = false;
    for(i in castles) {
        new createNeutralCastle(i);
    }
    for(i in ruins) {
        new ruinCreate(i);
    }
    for(color in players) {
        players[color].active = 0;
        $('.'+color +' .color').css('background',color);
        for(i in players[color].armies) {
            players[color].armies[i] = new army(players[color].armies[i], color);
            if(color == my.color){
                myArmies = true;
            }
        }
        for(i in players[color].castles) {
            castleOwner(i, color);
            if(color == my.color){
                myCastles = true;
                setMyCastleProduction(i);
            }
        }
    }
    $('.castle').fadeIn(1);
    auth();
    showFirstCastle();
    if(!myArmies && !myCastles){
        lostM();
    }else{
        if(my.turn){
            turnOn();
        }else{
            turnOff();
        }
    }
    wsPing();
    setInterval ( 'wsPing()', 10000 );
    if(my.turn && !players[my.color].turnActive){
        startMyTurnA();
    }
//    for(y in fields) {
//        for(x in fields[y]) {
//            board.append(
//                $('<div>')
//                .html(fields[y][x])
//                .addClass('field')
//                .css({
//                    left:(x*40)+'px',
//                    top:(y*40)+'px'
//                })
//            );
//        }
//    }
}

function goldUpdate(gold){
    $('#gold').html(gold);
}

function updatePlayers(color){
    players[color].active = 2;
}

function chat(color,msg){
    var chatWindow = $('#chatWindow div').append('<br/>').append(color+': '+msg);
    var scroll = 110 - chatWindow[0].scrollHeight;
    chatWindow.animate({'top':scroll},100);
    $('#msg').focus();
}

function setlock(){
    lock = true;
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
}

function unlock(){
    lock = false;
    if(my.turn){
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
    }
}

function titleBlink(msg) {
    if(timeoutId){
        clearInterval(timeoutId);
    }
    timeoutId = setInterval(function() {
        if(document.title == msg){
            document.title = '...';
        }else{
            document.title = msg;
        }
    });
    window.onmousemove = function() {
        clearInterval(timeoutId);
        document.title = documentTitle;
        window.onmousemove = null;
    };
}

function terrain(){
    board.after(
        $('<div>')
        .addClass('terrain')
        .append('Terrain: ')
        .append(
            $('<span>').attr('id','coord')
        )
    );
}
