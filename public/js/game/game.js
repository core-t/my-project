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

var nextArmy;

var cursorDirection;

var documentTitle = document.title;
var timeoutId = null;

var enemyArmiesPositions = new Array();

var largeimageloaded = false;

$(document).ready(function() {
    terrain();
    lWSC = new jws.jWebSocketJSONClient();
    login();
    zoomer = new zoom(760, 670);
    setTimeout ( 'connect()', 1500 );

//    $(window).load(function () {
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

function chat(color,msg){
    var chatWindow = $('#chatWindow div').append('<br/>').append(color+': '+msg);
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
    var x = selectedArmy.x/40;
    var y = selectedArmy.y/40;
    var open = new Object();
    var close = new Object();
    var destX = 62;
    var destY = 58;
    var start = new node(x, y, destX, destY, 0);
    open[x+'_'+y] = start;
    aStar(close, open, destX, destY, 1);
    //    showOpen(open);
    //    showClose(close);
    showPath(close, destX+'_'+destY);
//    console.log(open);
//    console.log(close);
}

function test2(){
    var i, j = 0;
    var startX = selectedArmy.x/40;
    var startY = selectedArmy.y/40;
    var X = startX - selectedArmy.moves;
    if(X < 0){
        X = 0;
    }
    var y = startY - selectedArmy.moves;
    if(y < 0){
        y = 0;
    }
    $('.path').remove();
    var lenght = startX - X + startY - y + 1;
    var matrix = new Array(lenght);
    var weight;
    for(i = 0; i < lenght; i++){
        x = X;
        matrix[y] = new Array(lenght);
        for(j = 0; j < lenght; j++){
            weight = getTerrain(fields[y][x], selectedArmy)[1];
            matrix[y][x] = weight;
            //            var pX = x * 40;
            //            var pY = y * 40;
            //            board.append(
            //                $('<div>')
            //                .addClass('path')
            //                .css({
            //                    left:pX,
            //                    top:pY,
            //                    'text-align':'center',
            //                    'z-index':10000
            //                })
            //                .html(weight)
            //            );
            x++;
        }
        y++;
    }
    console.log(matrix);
}

function test1(){
    var all = 1;
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

