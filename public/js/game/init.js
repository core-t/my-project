Image1 = new Image(27, 32);
Image1.src = '/img/game/cursor_attack.png';
Image2 = new Image(14, 46);
Image2.src = '/img/game/cursor_castle.png';
Image3 = new Image(25, 26);
Image3.src = '/img/game/cursor_select.png';
Image4 = new Image(9, 20);
Image4.src = '/img/game/cursor.png';
Image5 = new Image(20, 9);
Image5.src = '/img/game/cursor_pointer.png';

var fields = new Array();

var costs = 0;
var income = 0;

//var selectedEnemyArmy = null;

var firstCastleId = 1000;

var zoomer;
var zoomPad;
var board;
var coord;

var documentTitle = document.title;
var timeoutId = null;

var largeimageloaded = false;

var myArmies = false;
var myCastles = false;
var enemyArmies = false;
var enemyCastles = false;

var documentWidth;
var documentHeight;

var show = true;

var stop = 0;

var shipId;

var castlesConquered = null;
var castlesDestroyed = null;
var heroesKilled = null;
var soldiersKilled = null;
var soldiersCreated = null;


$(document).ready(function () {
    $(window).resize(function () {
        Gui.adjust();
    });

    Turn.init();
    Gui.adjust();
    fieldsCopy();
    unitsReformat();
//    artifactsReformat();
    Gui.prepareButtons();
    Websocket.init();

    for (i in castles) {
        Castle.createNeutral(i);
    }

    for (i in ruins) {
        Ruin.create(i);
    }

    for (i in towers) {
        Tower.create(i);
    }

    shipId = Unit.getShipId();
    Players.init();
    Players.draw();
    Players.turn();

});

function startGame() {
    if (!largeimageloaded) {
        setTimeout('startGame()', 1000);
        return;
    }

    Sound.play('gamestart');

    Castle.showFirst();

//    if (!enemyArmies && !enemyCastles) {
//        Turn.off();
//        Message.win(my.color);
//    } else if (!myArmies && !myCastles) {
//        Turn.off();
//        Message.lost(my.color);
//    } else {

    if (my.turn) {
        Turn.on();
    } else {
        Turn.off();
    }

    if (my.turn && !players[my.color].turnActive) {
        Websocket.startMyTurn();
    } else if (my.game && players[Turn.color].computer) {
        setTimeout('Websocket.computer()', 1000);
    }
//    }

    renderChatHistory();
    costsUpdate(costs);
    income += countPlayerTowers(my.color) * 5;
    incomeUpdate(income);
    timer.start();
}

