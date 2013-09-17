Image1 = new Image(27, 32);
Image1.src = '../img/game/cursor_attack.png';
Image2 = new Image(14, 46);
Image2.src = '../img/game/cursor_castle.png';
Image3 = new Image(25, 26);
Image3.src = '../img/game/cursor_select.png';
Image4 = new Image(9, 20);
Image4.src = '../img/game/cursor.png';
Image4 = new Image(20, 9);
Image4.src = '../img/game/cursor_pointer.png';

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

var documentTitle = document.title;
var timeoutId = null;

var largeimageloaded = false;

var myArmies = false;
var myCastles = false;
var enemyArmies = false;
var enemyCastles = false;

var messageLeft;
var documentWidth;
var documentHeigh;

$(document).ready(function () {
    $(window).resize(function () {
        adjustGui();
    });

    adjustGui();
    fieldsCopy();
    unitsReformat();
    prepareButtons();
    startWebSocket();
});

function startGame() {
    if (!largeimageloaded) {
        setTimeout('startGame()', 1000);
        return;
    }

    for (i in castles) {
        new createNeutralCastle(i);
    }

    for (i in ruins) {
        new ruinCreate(i);
    }
    for (i in towers) {
        new towerCreate(i);
    }
    for (color in players) {
        players[color].active = 0;
        $('.' + color + ' .color').addClass(color + 'bg');
        if (players[color].computer) {
            $('.' + color + ' .color .type').css('background', 'url(../img/game/computer.png) center center no-repeat');
        } else {
            $('.' + color + ' .color .type').css('background', 'url(..' + Hero.getImage(color) + ') center center no-repeat');
        }

        for (i in players[color].armies) {
            players[color].armies[i] = new army(players[color].armies[i], color);
            if (color == my.color) {
                for (s in players[color].armies[i].soldiers) {
                    costs += units[players[color].armies[i].soldiers[s].unitId].cost;
                }
                myArmies = true;
            } else {
                enemyArmies = true;
            }
        }

        if (players[color].armies == "" && players[color].castles == "") {
            $('.nr.' + color).html('<img src="/img/game/skull_and_crossbones.png" />');
        }

        for (i in players[color].castles) {
            updateCastleDefense(i, players[color].castles[i].defenseMod);
            castleOwner(i, color);
            if (color == my.color) {
                income += castles[i].income;
                if (firstCastleId > i) {
                    firstCastleId = i;
                }
                myCastles = true;
                setMyCastleProduction(i);
            } else {
                enemyCastles = true;
            }
        }
    }

    showFirstCastle();

    if (!enemyArmies && !enemyCastles) {
        turnOff();
        winM(my.color);
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
            wsStartMyTurn();
        } else if (my.game && players[turn.color].computer) {
            setTimeout('wsComputer()', 1000);
        }
    }

    renderChatHistory();
    costsUpdate(costs);
    income += countPlayerTowers(my.color) * 5;
    incomeUpdate(income);
    timer.start();
}

