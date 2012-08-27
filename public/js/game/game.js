Image1= new Image(27,32);
Image1.src = '../img/game/cursor_attack.png';
Image2= new Image(14,46);
Image2.src = '../img/game/cursor_castle.png';
Image3= new Image(25,26);
Image3.src = '../img/game/cursor_select.png';
Image4= new Image(20,20);
Image4.src = '../img/game/footsteps_e.png';
Image5= new Image(20,20);
Image5.src = '../img/game/footsteps_n.png';
Image6= new Image(20,20);
Image6.src = '../img/game/footsteps_s.png';
Image7= new Image(20,20);
Image7.src = '../img/game/footsteps_w.png';
Image8= new Image(20,20);
Image8.src = '../img/game/footsteps_ne.png';
Image9= new Image(20,20);
Image9.src = '../img/game/footsteps_nw.png';
Image10= new Image(20,20);
Image10.src = '../img/game/footsteps_se.png';
Image11= new Image(20,20);
Image11.src = '../img/game/footsteps_sw.png';
Image12= new Image(33,18);
Image12.src = '../img/game/cursor_arrow_e.png';
Image13= new Image(18,34);
Image13.src = '../img/game/cursor_arrow_n.png';
Image14= new Image(18,34);
Image14.src = '../img/game/cursor_arrow_s.png';
Image15= new Image(33,18);
Image15.src = '../img/game/cursor_arrow_w.png';
Image16= new Image(28,28);
Image16.src = '../img/game/cursor_arrow_ne.png';
Image17= new Image(28,28);
Image17.src = '../img/game/cursor_arrow_nw.png';
Image18= new Image(28,28);
Image18.src = '../img/game/cursor_arrow_se.png';
Image19= new Image(28,28);
Image19.src = '../img/game/cursor_arrow_sw.png';

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
var quitedArmies = new Array();

var zoomer;

var cursorDirection;

var documentTitle = document.title;
var timeoutId = null;

var enemyArmiesPositions = new Array();

var largeimageloaded = false;

var wait = 0;

var urlMove = '/move/go';
var urlNextTurn = '/turn/next';
var urlGetTurn = '/turn/get';
var urlStartMyTurn = '/turn/start';
var urlFightEnemyCastle = '/fight/ecastle';
var urlFightNeutralCastle = '/fight/ncastle';
var urlFightArmy = '/fight/army';
var urlAddArmy = '/gameajax/addarmy';
var urlGetPlayerArmies = '/gameajax/armies';
var urlJoinArmy = '/gameajax/join';
var urlSplitArmy = '/gameajax/split';
var urlDisbandArmy = '/gameajax/disband';
var urlHeroResurrection = '/gameajax/resurrection';
var urlSearchRuins = '/ruin/search';
var urlGetRuins = '/ruin/get';
var urlSetProduction = '/production/set';
var urlCastleRaze = '/castle/raze';
var urlCastleBuild = '/castle/build';
var urlCastleGet = '/castle/get';
var urlTowerAdd = '/tower/add';
var urlTowerGet = '/tower/get';
var urlComputer = '/computer';
var urlChatSend = '/chat/send';

$(document).ready(function() {
    terrain();
    lWSC = new jws.jWebSocketJSONClient();
    login();
    zoomer = new zoom(760, 670);
    setTimeout ( 'connect()', 1500 );

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
        startGame();
    //        startM();
    }else{
        login();
        simpleM('Sorry, server is disconnected.');
        setTimeout ( 'connect()', 1000 );
    }
}

function startGame(){
    if(!largeimageloaded){
        setTimeout ( 'startGame()', 1000 );
    }else{
        var myArmies = false;
        var myCastles = false;
        for(i in castles) {
            new createNeutralCastle(i);
        }
        for(i in ruins) {
            new ruinCreate(i);
        }
        for(i in towers) {
            new towerCreate(i);
        }
        for(color in players) {
            players[color].active = 0;
            $('.'+color +' .color').addClass(color +'bg');
            if(players[color].computer){
                $('.'+color+' .color').css('background',color+' url(../img/game/computer.png) center center no-repeat');
            }
            //            console.log(players[color]);
            for(i in players[color].armies) {
                players[color].armies[i] = new army(players[color].armies[i], color);
                if(color == my.color){
                    myArmies = true;
                }
            }
            for(i in players[color].castles) {
                updateCastleDefense(i, players[color].castles[i].defenseMod);
                castleOwner(i, color);
                if(color == my.color){
                    myCastles = true;
                    setMyCastleProduction(i);
                }
            }
        }
        //        auth();
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
        if(my.turn && !players[my.color].turnActive){
            startMyTurnA();
        } else if(my.game && players[turn.color].computer){
            computerA();
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
}

function goldUpdate(gold){
    $('#gold').html(gold);
}

function updatePlayers(color){
    players[color].active = 2;
}

function chat(color,msg,time){
    var chatWindow = $('#chatWindow div').append('<br/>').append(color+' ('+time+'): '+msg);
    var scroll = 120 - chatWindow[0].scrollHeight;
    chatWindow.animate({
        'top':scroll
    },100);
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

//function waitOn(){
//    wait = 1;
//}
//
//function waitOff(){
//    wait = 0;
//    console.log('b');
//}

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
        .append(' Terrain: ')
        .append(
            $('<span>').attr('id','coord')
            )
        );
}

function showOpen(open){
    for(i in open){
        var pX = open[i].x * 40;
        var pY = open[i].y * 40;
        board.append(
            $('<div>')
            .addClass('path2')
            .css({
                left:pX,
                top:pY,
                'text-align':'center',
                'z-index':999
            })
            .html(open[i].H+' '+open[i].G+' '+open[i].F)
            );
    }
}

function showClose(close){
    for(i in close){
        var pX = close[i].x * 40;
        var pY = close[i].y * 40;
        board.append(
            $('<div>')
            .addClass('path2')
            .css({
                left:pX,
                top:pY,
                'text-align':'center',
                'z-index':999,
                'background':'#000',
                'opacity':'0.33'
            })
            .html(close[i].H+' '+close[i].G+' '+close[i].F)
            );
    }
}

function test(){
    var all = 0;
    $('.path').remove();
    for(y in fields){
        for(x in fields[y]){
            if(fields[y][x] == 'e'){
                var pX = x*40;
                var pY = y*40;
                board.append(
                    $('<div>')
                    .addClass('path')
                    .css({
                        left:pX,
                        top:pY,
                        'text-align':'center',
                        'z-index':10000
                    })
                    .html('e')
                    );
            }else if(!fields[y][x]){
                var pX = x*40;
                var pY = y*40;
                board.append(
                    $('<div>')
                    .addClass('path')
                    .css({
                        left:pX,
                        top:pY,
                        'text-align':'center',
                        'z-index':10000
                    })
                    .html('X')
                    );
            }else if(all){
                var pX = x*40;
                var pY = y*40;
                board.append(
                    $('<div>')
                    .addClass('path')
                    .css({
                        left:pX,
                        top:pY,
                        'text-align':'center',
                        'z-index':10000
                    })
                    .html(fields[y][x])
                    );
            }
        }
    }
    console.log('PLAYERS:');
    for(color in players) {
        for(i in players[color].armies) {
            console.log(i);
        }
    }
}

function getColor(color){
    if(color == 'green'){
        return '#00db00';
    }else{
        return color;
    }
}

