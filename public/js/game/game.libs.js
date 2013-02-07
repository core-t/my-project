$(document)[0].oncontextmenu = function() {
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
}

function turnOff() {
    my.turn = false;
    unselectArmy();
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    makeMyCursorLock();
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
        wsStartMyTurn();
        return 0;
    } else {
        turnOff();
        return 1;
    }
}

function startGame(){
    if(!largeimageloaded){
        setTimeout ( 'startGame()', 1000 );
        return;
    }

    var x;
    var y;

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
            }else{
                enemyArmies = true;
            }
        }
        for(i in players[color].castles) {
            updateCastleDefense(i, players[color].castles[i].defenseMod);
            castleOwner(i, color);
            if(color == my.color){
                if(firstCastleId > i){
                    firstCastleId = i;
                }
                myCastles = true;
                setMyCastleProduction(i);
            }else{
                enemyCastles = true;
            }
        }
    }

    showFirstCastle();

    if(!enemyArmies && !enemyCastles){
        turnOff();
        winM(my.color);
    }else if(!myArmies && !myCastles){
        turnOff();
        lostM(my.color);
    }else{
        if(my.turn){
            turnOn();
        }else{
            turnOff();
        }
        if(my.turn && !players[my.color].turnActive){
            wsStartMyTurn();
        } else if(my.game && players[turn.color].computer){
            setTimeout ( 'wsComputer()', 1000 );
        }
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
    makeMyCursorLock();
}

function unlock(){
    if(my.turn){
        lock = false;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        makeMyCursorUnlock();
    }
}

function makeMyCursorUnlock(){
    $('body *').css('cursor','url(../img/game/cursor.png), auto');
    $('.zoomPup').css('cursor','url(../img/game/lupa.png) 13 13, crosshair');
    $('#map').css('cursor','url(../img/game/lupa.png) 13 13, crosshair');
    $('.c').css('cursor','url(../img/game/lupa.png) 13 13, crosshair');
    $('.a').css('cursor','url(../img/game/lupa.png) 13 13, crosshair');
    myCastlesAddCursor();
}

function makeMyCursorLock(){
    $('body *').css('cursor','url(../img/game/cursor_hourglass.png), wait');
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


function getColor(color){
    if(color == 'green'){
        return '#00db00';
    }else{
        return color;
    }
}

function makeTime(){
    var d = new Date();
    var minutes = d.getMinutes();
    if(minutes.length == 1){
        minutes = '0'+minutes
    }
    return d.getHours()+':'+minutes;
}

function isDigit(val){
    if(typeof val == 'undefined'){
        return false;
    }
    var intRegex = /^\d+$/;
    if(intRegex.test(val)){
        return true;
    }else{
        return false;
    }
}

function isTruthful(val){
    if(typeof val != 'undefined' && val){
        return true;
    }
    return false;
}

function prepareButtons(){
    zoomPad = $(".zoomPad");
    board = $("#board")
    .mousedown(function(event) {
        if(!lock) {
            switch (event.which) {
                case 1:
                    if(selectedArmy) {
                        wsArmyMove(cursorPosition(event.pageX, event.pageY, 1));
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
        }
    })
    .mousemove(function(e) {
        if(!lock) {
            cursorPosition(e.pageX, e.pageY);
        }
    })
    .mouseleave(function(){
        $('.path').remove()
    });
    $('#send').click(function(){
        wsChat();
    });
    $('#msg').keypress(function(e){
        if(e.which == 13){
            wsChat();
        }
    });
    $('#nextTurn').click(function(){
        nextTurnM()
    });
    $('#nextArmy').click(function(){
        findNextArmy()
    });
    $('#skipArmy').click(function(){
        skipArmy()
    });
    $('#quitArmy').click(function(){
        quitArmy()
    });
    $('#splitArmy').click(function(){
        if(selectedArmy){
            splitArmyM()
        }
    });
    $('#armyStatus').click(function(){
        if(selectedArmy){
            armyStatusM()
        }
    });
    $('#disbandArmy').click(function(){
        if(selectedArmy){
            disbandArmyM()
        }
    });
    $('#searchRuins').click(function(){
        wsSearchRuins()
    });
    $('#test').click(function(){
        test()
    });
    $('#nextTurn').addClass('buttonOff');
    $('#nextArmy').addClass('buttonOff');
    $('#skipArmy').addClass('buttonOff');
    $('#quitArmy').addClass('buttonOff');
    $('#splitArmy').addClass('buttonOff');
    $('#disbandArmy').addClass('buttonOff');
    $('#searchRuins').addClass('buttonOff');

    $('.'+my.color+' .color').append('You');
    $('.'+turn.color+' .turn').html('Turn >');
    $('#turnNumber').html(turn.nr);
}
