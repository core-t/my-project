Image1 = new Image(27, 32);
Image1.src = '/img/game/cursor_attack.png';
Image2 = new Image(14, 46);
Image2.src = '/img/game/cursor_castle.png';
Image3 = new Image(25, 26);
Image3.src = '/img/game/cursor_select.png';
Image4 = new Image(9, 20);
Image4.src = '/img/game/cursor.png';
Image4 = new Image(20, 9);
Image4.src = '/img/game/cursor_pointer.png';

var newX = 0;
var newY = 0;

var fields = new Array();

var lock = true;

var costs = 0;
var income = 0;

var selectedArmy = null;
var unselectedArmy = null;
var parentArmy = null;
var selectedEnemyArmy = null;
var nextArmy = null;
var nextArmySelected = false;
var skippedArmies = new Array();
var quitedArmies = new Array();

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

$(document).ready(function () {
    $(window).resize(function () {
        Gui.adjust();
    });

    Gui.adjust();
    fieldsCopy();
    unitsReformat();
    artifactsReformat()
    Gui.prepareButtons();
    Websocket.init();

    for (i in castles) {
        new createNeutralCastle(i);
    }

    for (i in ruins) {
        new ruinCreate(i);
    }

    for (i in towers) {
        new towerCreate(i);
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

    showFirstCastle();

    if (!enemyArmies && !enemyCastles) {
        turnOff();
        Message.win(my.color);
    } else if (!myArmies && !myCastles) {
        turnOff();
        Message.lost(my.color);
    } else {
        if (my.turn) {
            turnOn();
        } else {
            turnOff();
        }
        if (my.turn && !players[my.color].turnActive) {
            Websocket.startMyTurn();
        } else if (my.game && players[turn.color].computer) {
            setTimeout('Websocket.computer()', 1000);
        }
    }

    renderChatHistory();
    costsUpdate(costs);
    income += countPlayerTowers(my.color) * 5;
    incomeUpdate(income);
    timer.start();
}

