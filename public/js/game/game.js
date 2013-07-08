Image1 = new Image(27, 32);
Image1.src = '../img/game/cursor_attack.png';
Image2 = new Image(14, 46);
Image2.src = '../img/game/cursor_castle.png';
Image3 = new Image(25, 26);
Image3.src = '../img/game/cursor_select.png';
Image4 = new Image(9, 20);
Image4.src = '../img/game/cursor.png';


//Image12 = new Image(33, 18);
//Image12.src = '../img/game/cursor_arrow_e.png';
//Image13 = new Image(18, 34);
//Image13.src = '../img/game/cursor_arrow_n.png';
//Image14 = new Image(18, 34);
//Image14.src = '../img/game/cursor_arrow_s.png';
//Image15 = new Image(33, 18);
//Image15.src = '../img/game/cursor_arrow_w.png';
//Image16 = new Image(28, 28);
//Image16.src = '../img/game/cursor_arrow_ne.png';
//Image17 = new Image(28, 28);
//Image17.src = '../img/game/cursor_arrow_nw.png';
//Image18 = new Image(28, 28);
//Image18.src = '../img/game/cursor_arrow_se.png';
//Image19 = new Image(28, 28);
//Image19.src = '../img/game/cursor_arrow_sw.png';

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

//var cursorDirection;

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
    $(window).resize(function() {
        adjustGui();
    });

    adjustGui();
    fieldsCopy();
    unitsReformat();
    prepareButtons();
    startWebSocket();
});

