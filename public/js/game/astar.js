
// *** A* ***

var terrain = {
    'b':'Bridge',
    'c':'Castle',
    'e':'Enemy',
    'f':'Forest',
    'g':'Grassland',
    'm':'Hills',
    'M':'Mountains',
    'r':'Road',
    's':'Swamp',
    'S':'Ship',
    'w':'Water'
}

function cursorPosition(x, y, force) {
    if(selectedArmy) {
        var offset = $('.zoomWindow').offset();
        var X = x - 20 - parseInt(board.css('left')) - offset.left;
        var Y = y - 20 - parseInt(board.css('top')) - offset.top;
        var destX = Math.round(X/40);
        var destY = Math.round(Y/40);
        var tmpX = destX*40;
        var tmpY = destY*40;
        if(newX != tmpX || newY != tmpY || force == 1){
            $('.path').remove();
            newX = tmpX;
            newY = tmpY;
            var startX = selectedArmy.x;
            var startY = selectedArmy.y;
            var open = new Object();
            var close = new Object();
            var start = new node(startX, startY, destX, destY, 0);
            open[startX+'_'+startY] = start;
            aStar(close, open, destX, destY, 1);
            $('#coord').html(destX + ' - ' + destY + ' ' + terrain[fields[destY][destX]]);
            return showPath(close, destX+'_'+destY, selectedArmy.moves);
        }
    }
    return null;
}

function setCursorArrow(dir){
    if(cursorDirection != dir){
        board.css('cursor','url(../img/game/cursor_arrow_'+dir+'.png), crosshair');
        cursorDirection = dir;
    //         console.log(cursorDirection);
    }
}

function showPath(close, key, moves){
    if(typeof close[key] == 'undefined'){
        return 0;
    }
    var klasa = 'path2';
    while(typeof close[key].parent != 'undefined'){
        var pX = close[key].x * 40;
        var pY = close[key].y * 40;
        if(close[key].G <= moves){
            if(typeof set == 'undefined'){
                var set = new Object();
                set.x = pX;
                set.y = pY;
                set.movesSpend = close[key].G;
            }
            klasa = 'path1';
        }
        board.append(
            $('<div>')
            .addClass('path '+klasa)
            .css({
                left:pX+'px',
                top:pY+'px'
            })
            .html(close[key].G)
            );
        key = close[key].parent.x+'_'+close[key].parent.y;
    }
    if(typeof set == 'undefined'){
        return null;
    }else{
        newX = set.x;
        newY = set.y;
        return set.movesSpend;
    }
}

function aStar(close, open, destX, destY, nr){
    nr++;
    var f = findSmallestF(open);
    var x = open[f].x;
    var y = open[f].y;
    close[f] = open[f];
    delete open[f];
    addOpen(x, y, close, open, destX, destY);
    if(x == destX && y == destY){
        //        console.log(nr + ' bingo!');
        return;
    }
    if(!isNotEmpty(open)){
        //        console.log('dupa!');
        return;
    }
    if(nr > 30000){
        //        console.log(open);
        //        console.log(close);
        nr--;
        console.log('>'+nr);
        return;
    }
    aStar(close, open, destX, destY, nr);
    return;
}

function isNotEmpty(obj){
    for (key in obj) {
        if (obj.hasOwnProperty(key)){
            return true;
        }
    }
    return false;
}

function findSmallestF(open){
    var i;
    var f;
    for(i in open){
        if(typeof open[f] == 'undefined'){
            f = i;
        }
        if(open[i].F < open[f].F){
            f = i;
        }
    }
    return f;
}

function addOpen(x, y, close, open, destX, destY){
    var startX = x - 1;
    var startY = y - 1;
    var endX = x + 1;
    var endY = y + 1;
    var i,j = 0;
    for(i = startX; i <= endX; i++){
        for(j = startY; j <= endY; j++){
            var key = i+'_'+j;
            if(x == i && y == j){
                continue;
            }
            if(typeof close[key] != 'undefined' && close[key].x == i && close[key].y == j){
                continue;
            }
            if(typeof fields[j] == 'undefined'){
                continue;
            }
            if(typeof fields[j][i] == 'undefined'){
                continue;
            }
            var type = fields[j][i];
            if(type == 'e'){
                continue;
            }
            var g = selectedArmy.terrainCosts[type];
            if (g > 5) {
                continue;
            }
            if(typeof open[key] != 'undefined'){
                calculatePath(x+'_'+y, open, close, g, key);
                continue;
            }
            var parent = {
                'x':x,
                'y':y
            };
            g += close[x+'_'+y].G;
            open[key] = new node(i, j, destX, destY, g, parent);
        }
    }
}

function calculatePath(kA, open, close, g, key){
    if(open[key].G > (g + close[kA].G)){
        open[key].parent = {
            'x':close[kA].x,
            'y':close[kA].y
        };
        open[key].G = g + close[kA].G;
        open[key].F = open[key].G + open[key].H;
    }
}

function calculateH(x, y, destX, destY){
    var h = 0;
    var xLengthPoints = x - destX;
    var yLengthPoints = y - destY;
    if(xLengthPoints < yLengthPoints) {
        for(i = 1; i <= xLengthPoints; i++) {
            h++;
        }
        for(i = 1; i <= (yLengthPoints - xLengthPoints); i++) {
            h++;
        }
    } else {
        for(i = 1; i <= yLengthPoints; i++) {
            h++;
        }
        for(i = 1; i <= (xLengthPoints - yLengthPoints); i++) {
            h++;
        }
    }
    return h;
}

function node(x, y, destX, destY, g, parent){
    this.x = x;
    this.y = y;
    this.G = g;
    this.H = calculateH(this.x, this.y, destX, destY);
    this.F = this.H + this.G;
    this.parent = parent;
}

function getVectorLength(x1, y1, x2, y2) {
    return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y1 - y2, 2))
}

