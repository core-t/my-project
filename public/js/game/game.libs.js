$(document)[0].oncontextmenu = function() {
    return false;
} // usuwa menu kontekstowe spod prawego przycisku

// *** RUINS ***

function ruinCreate(ruinId){
    var title;
    if(typeof ruins[ruinId].e == 'undefined'){
        title = 'Ruins';
    }else{
        title = 'Ruins (empty)';
    }
    board.append(
        $('<div>')
        .addClass('ruin')
        .attr({
            id: 'ruin' + ruinId,
            title: title
        })
        .css({
            left: ruins[ruinId].x + 'px',
            top: ruins[ruinId].y + 'px',
        })
        );
    $('#ruin' + ruinId).fadeIn(1);
}

// *** CASTLES ***

function castleUpdate(data) {
    if(data.razed){
        $('#castle' + data.castleId).remove();
        delete castles[data.castleId];
    }else{
        castles[data.castleId].defense = data.defense;
        castles[data.castleId].currentProduction = data.production;
        castles[data.castleId].currentProductionTurn = data.productionTurn;
    }
}

function castleCreate(castleId) {
    castles[castleId].defense = castles[castleId].defensePoints;
    board.append(
        $('<div>')
        .addClass('castle')
        .attr({
            id: 'castle' + castleId,
            title: castles[castleId].name
        })
        .css({
            left: castles[castleId].position.x + 'px',
            top: castles[castleId].position.y + 'px',
        })
        );
    //     $('#castle' + castleId).fadeIn(1);
    $('#castle' + castleId).mouseover(function(){
        if(lock) {
            return null;
        }
        if(my.turn) {
            if(selectedArmy) {
                $('#' + this.id).css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
            } else {
                $('#' + this.id).css('cursor','default');
            }
        }
    });
}

function castleOwner(castleId, color) {
    var castle = $('#castle' + castleId);
    if(typeof players[color].castles[castleId] != 'undefined' && players[color].castles[castleId].razed){
        castle.remove();
        delete castles[castleId];
    }else{
        if(color == my.color) {
            zindex = 100;
            castle.mouseover(function() {
                if(my.turn){
                    if(selectedArmy) {
                        castle.css('cursor', 'default');
                    } else {
                        castle.css('cursor', 'crosshair');
                    }
                } else {
                    castle.css('cursor', 'default');
                }
            });
            castle.mousemove(function() {
                if(my.turn){
                    if(selectedArmy) {
                        castle.css('cursor', 'default');
                    } else {
                        castle.css('cursor', 'url(../img/game/cursor_castle.png), crosshair');
                    }
                } else {
                    castle.css('cursor', 'default');
                }
            });
            castle.click(function(){
                if(my.turn){
                    if(!selectedArmy) {
                        castleM(castleId, color);
                    }
                }
            });
        } else {
            zindex = 600;
            castle.mouseover(function() {
                if(lock) {
                    return null;
                }
                if(my.turn && selectedArmy){
                    castle.css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
                } else {
                    castle.css('cursor', 'default');
                }
            });
            castle.mousemove(function() {
                if(lock) {
                    return null;
                }
                if(my.turn && selectedArmy){
                    castle.css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
                } else {
                    castle.css('cursor', 'default');
                }
            });
            castle.click(function(){return null});
        }
        castle
        .removeClass()
        .addClass('castle ' + color)
        .css({
            'z-index':zindex,
            background: 'url(../img/game/castle_'+color+'.png) center center no-repeat'
        });
        castles[castleId].color = color;
        if(typeof players[color].castles[castleId] == 'undefined'){
            castles[castleId].currentProduction = null;
            castles[castleId].currentProductionTurn = 0;
        } else {
            castles[castleId].currentProduction = players[color].castles[castleId].production;
            castles[castleId].currentProductionTurn = players[color].castles[castleId].productionTurn;
            if(color == my.color && castles[castleId].currentProduction){
                castle.html($('<img>').attr('src','../img/game/castle_production.png').css('float','right'));
            }else{
                castle.html('');
            }
        }
        castle.fadeIn(1);
    }
}

function isEnemyCastle(x, y) {
    for(castleId in castles) {
        if(castles[castleId].color == my.color) {
            continue;
        }
        var pos = castles[castleId].position;
        if((x >= pos.x) && (x < (pos.x + 80)) && (y >= pos.y) && (y < (pos.y + 80))) {
            return castleId;
        }
    }
    return false;
}

function showFirstCastle() {
    var sp = $('.castle.' + turn.color);
    zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
}

// *** ARMIES ***

function army(obj, color, dontFade) {
    if(obj.destroyed){
        if(typeof players[color].armies[obj.armyId] != 'undefined'){
            delete players[color].armies[obj.armyId];
        }
        if(typeof $('#army'+obj.armyId) != 'undefined') {
            $('#army'+obj.armyId).remove();
        }
        return null;
    }
    var position = changePointToPosition(obj.position);
    this.x = position[0];
    this.y = position[1];
    deleteArmyByPosition(this.x, this.y, color);
    if(typeof $('#army'+obj.armyId) != 'undefined') {
        $('#army'+obj.armyId).remove();
    }
    this.canFly = 1;
    this.canSwim = 0;
    this.heroes = obj.heroes;
    var numberOfUnits = 0;
    var numberOfHeroes = 0;
    var numberOfSoldiers = 0;
    for(hero in this.heroes) {
        this.heroKey = hero;
        if(typeof this.moves == 'undefined') {
            this.moves = this.heroes[hero].movesLeft;
        }
        if(this.heroes[hero].movesLeft < this.moves) {
            this.moves = this.heroes[hero].movesLeft;
            this.heroKey = hero;
        }
        this.canFly--;
        numberOfHeroes++;
    }
    this.soldiers = obj.soldiers;
    for(soldier in this.soldiers) {
        if(typeof attack  == 'undefined') {
            var attack = this.soldiers[soldier].attackPoints;
            this.soldierKey = soldier;
        }
        if(this.soldiers[soldier].attackPoints > attack) {
            attack = this.soldiers[soldier].attackPoints;
            this.soldierKey = soldier;
        }
        if(typeof defense  == 'undefined') {
            var defense = this.soldiers[soldier].defensePoints;
        }
        if(this.soldiers[soldier].defensePoints > defense) {
            defense = this.soldiers[soldier].defensePoints;
        }
        if(typeof this.moves == 'undefined') {
            this.moves = this.soldiers[soldier].movesLeft;
        }
        if(this.soldiers[soldier].movesLeft < this.moves) {
            this.moves = this.soldiers[soldier].movesLeft;
        }
        if(this.soldiers[soldier].canFly){
            this.canFly++;
        }else{
            this.canFly -= 2;
        }
        if(this.soldiers[soldier].canSwim){
            this.canSwim++;
        }
        numberOfSoldiers++;
    }
    if(typeof this.heroes[this.heroKey] != 'undefined') {
        if(this.heroes[this.heroKey].name == 'undefined'){
            this.name = 'Anonymous hero';
        }else{
            this.name = this.heroes[this.heroKey].name;
        }
        this.img = 'hero';
        this.attack = this.heroes[this.heroKey].attackPoints;
        this.defense = this.heroes[this.heroKey].defensePoints;
        heroResurection = false;
    } else if(typeof this.soldiers[this.soldierKey] != 'undefined') {
        this.name = this.soldiers[this.soldierKey].name;
        this.img = this.name.replace(' ', '_').toLowerCase();
        this.attack = attack;
        this.defense = defense;
    } else {
        console.log('Armia nie posiada jednostek.');
        console.log(obj);
        delete players[color].armies[obj.armyId];
        return null;
    }
    this.element = $('<div>');
    if(color == my.color) { // moja armia
        this.element.click(function(e) {
            if(lock) {
                return null;
            }
            if(my.turn) {
                if(selectedArmy) {
                    if(selectedArmy == players[my.color].armies[this.id]) { // klikam na siebie
                        splitArmyM();
                        unselectArmy();
                    } else { // klikam na inną jednostkę
                        armyToJoinId = players[my.color].armies[this.id].armyId;
                        sendMove(cursorPosition(e.pageX, e.pageY));
                    }
                } else {
                    unselectArmy();
                    selectArmy(players[my.color].armies[this.id]);
                }
            }
        });
        this.element.mouseover(function() {
            if(lock) {
                return null;
            }
            if(my.turn) {
                if(!selectedArmy) {
                    players[my.color].armies[this.id].element.css('cursor', 'url(../img/game/cursor_select.png), default');
                }
            } else {
                players[my.color].armies[this.id].element.css('cursor', 'default');
            }
        });
    } else { // nie moja armia
        this.element.mouseover(function() {
            if(lock) {
                return null;
            }
            if(my.turn) {
                if(selectedArmy) {
                    selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
                    selectedEnemyArmy.element.css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
                } else {
                    players[$(this).attr("class").split(' ')[1]].armies[this.id].element.css('cursor', 'default');
                }
            } else {
                players[$(this).attr("class").split(' ')[1]].armies[this.id].element.css('cursor', 'default');
            }
        });
    }
    numberOfUnits = numberOfHeroes + numberOfSoldiers;
    if(numberOfUnits > 8) {
        numberOfUnits = 8;
    }
    this.element
    .addClass('army')
    .addClass(color)
    .attr({
        id: 'army' + obj.armyId,
        title: obj.armyId + ' ' + color + ' army'
    }).css({
        background: 'url(../img/game/flag_' + color + '_'+numberOfUnits+'.png) top left no-repeat',
        left:       this.x + 'px',
        top:        this.y + 'px'
    });
    this.element.append(
        $('<img>')
        .addClass('unit')
        .attr('src', '/img/game/' + this.img + '_' + color + '.png')
        );
    board.append(this.element);

    if(typeof dontFade == 'undefined'){
        $('#army'+obj.armyId).fadeIn(1);
    }
    this.armyId = obj.armyId;
    this.color = color;
}

function setParentArmyId(armyId) {
    parentArmyId = armyId;
}

function unsetParentArmyId() {
    parentArmyId = null;
}

function inRuins(){
    for(i in ruins){
        if(selectedArmy.x == ruins[i].x && selectedArmy.y == ruins[i].y){
            return true;
        }
    }
}

function selectArmy(a) {
    var index = $.inArray( a.armyId, skippedArmies );
    if(index != -1){
        skippedArmies.splice(index,1);
    }
    $('#army' + a.armyId).css('border','1px solid #ccc');
    $('#name').html(a.name);
    $('#moves').html(a.moves);
    $('#attack').html(a.attack);
    $('#defense').html(a.defense);
    $('#splitArmy').removeClass('buttonOff');
    $('#disbandArmy').removeClass('buttonOff');
    $('#skipArmy').removeClass('buttonOff');
    selectedArmy = a;
    if(typeof selectedArmy.heroKey != 'undefined' && inRuins()){
        $('#searchRuins').removeClass('buttonOff');
    }
    zoomer.lensSetCenter(a.x, a.y);
}

function unselectArmy() {
    if(selectedArmy) {
        unselectedArmy = selectedArmy;
        $('#army' + selectedArmy.armyId).css('border','none');
        board.css('cursor', 'default');
    }
    selectedArmy = null;
    $('#info').html('0');
    $('#name').html('');
    $('#moves').html('0');
    $('#attack').html(0);
    $('#defense').html(0);
    $('.path').remove();
    $('#splitArmy').addClass('buttonOff');
    $('#skipArmy').addClass('buttonOff');
    $('#searchRuins').addClass('buttonOff');
    $('#disbandArmy').addClass('buttonOff');
    removeM();
}

function unselectEnemyArmy() {
    selectedEnemyArmy = null;
}

function deleteArmy(armyId, color, quiet) {
    if(quiet) {
        if(typeof players[color].armies[armyId] != 'undefined') {
            players[color].armies[armyId].element.fadeOut(1);
            players[color].armies[armyId].element.remove();
            delete players[color].armies[armyId];
        }
    } else {
        zoomer.lensSetCenter(players[color].armies[armyId].x, players[color].armies[armyId].y);
        players[color].armies[armyId].element.fadeOut(500, function() {
            players[color].armies[armyId].element.remove();
            delete players[color].armies[armyId];
            console.log('usuni\u0119ta ' + armyId + ' - ' + color);
        });
    }
}

function deleteArmyByPosition(x, y, color) {
    for(i in players[color].armies) {
        if(players[color].armies[i].x == x && players[color].armies[i].y == y) {
            deleteArmy(i, color, true);
        }
    }
}

function changeArmyPosition(x, y, armyId, color) {
    if(typeof players[color].armies['army'+armyId] != 'undefined') {
        players[color].armies['army'+armyId].element.css({
            left: x + 'px',
            top: y + 'px'
        });
        players[color].armies['army'+armyId].x = x;
        players[color].armies['army'+armyId].y = y;
        zoomer.lensSetCenter(x, y);
    }
}

function changeArmyMoves(m, armyId, color) {
    players[color].armies['army'+armyId].moves = m;
}

function getEnemyCastleGarrison(castleId) {
    var pos = castles[castleId].position;
    var armies = new Array();
    for(color in players) {
        if(color == my.color) {
            continue;
        }
        for(i in players[color].armies) {
            var a = players[color].armies[i];
            if((a.x >= pos.x) && (a.x <= (pos.x + 40)) && (a.y >= pos.y) && (a.y <= (pos.y + 40))) {
                armies[i] = a;
            }
        }
    }
    return armies;
}

function getNeutralCastleGarrison(){
    return jQuery.parseJSON('{"color":"neutral","heroes":[],"soldiers":[{"soldierId":"s1","name":"light infantry"},{"soldierId":"s2","name":"light infantry"},{"soldierId":"s3","name":"light infantry"}]}');
}

function findNextArmy() {
    if(!my.turn){
        return null;
    }
    if(lock) {
        return null;
    }
    var reset = true;
    for(i in players[my.color].armies) {
        if(players[my.color].armies[i].moves == 0){
            continue;
        }
        if($.inArray( players[my.color].armies[i].armyId, skippedArmies ) != -1){
            continue;
        }
        if(nextArmySelected) {
            nextArmy = i;
            var reset = false;
            break;
        }
        if(!nextArmy) {
            nextArmy = i;
        }
        if(nextArmy == i){
            if(nextArmySelected == false){
                nextArmySelected = true;
                unselectArmy();
                console.log(players[my.color].armies[nextArmy]);
                selectArmy(players[my.color].armies[nextArmy]);
            }
        }
    }
    nextArmySelected = false;
    if(reset) {
        nextArmy = null;
    }
}

function skipArmy(){
    if(!my.turn){
        return null;
    }
    if(lock) {
        return null;
    }
    if(selectedArmy){
        skippedArmies.push(selectedArmy.armyId);
        findNextArmy();
    }
}


function walk(result) {
    for(i in result.path) {
        break;
    }
    if(typeof result.path[i] == 'undefined') {
        deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        newX = players[my.color].armies['army'+result.armyId].x;
        newY = players[my.color].armies['army'+result.armyId].y;
        wsArmyAdd(result.armyId);
        if(parentArmyId){
            getAddArmy(parentArmyId);
            wsArmyAdd(parentArmyId);
            unsetParentArmyId();
        }
        selectArmy(players[my.color].armies['army'+result.armyId]);
        unlock();
        return null;
    } else {
        wsArmyMove(result.path[i].x, result.path[i].y, unselectedArmy.armyId);
        zoomer.lensSetCenter(result.path[i].x, result.path[i].y);
        $('#army'+unselectedArmy.armyId).animate({left: result.path[i].x + 'px',top: result.path[i].y + 'px'},300,
            function(){
                delete result.path[i];
                walk(result);
            }
        );
    }
}

// *** UNITS ***

function getUnitId(name) {
    switch(name){
        case 'Light Infantry':
            return 1;
        case 'Heavy Infantry':
            return 2;
        case 'Cavalry':
            return 3;
        case 'Giants':
            return 4;
        case 'Wolves':
            return 5;
        case 'Navy':
            return 6;
        case 'Archers':
            return 7;
        case 'Pegasi':
            return 8;
        case 'Dwarves':
            return 9;
        case 'Griffins':
            return 10;
        default:
            return null;
    }
}

// *** POSITIONING ***

function changePointToPosition(point) {
    position = point.substr(1);
    position = position.split(',');
    position = new Array(parseInt(position[0]), parseInt(position[1]));
    return position;
}

function cursorPosition(x, y) {
    if(selectedArmy != null) {
        var offset = $('.zoomWindow').offset();
        var X = x - 20 - parseInt(board.css('left')) - offset.left;
        var Y = y - 20 - parseInt(board.css('top')) - offset.top;
        var vectorLenth = getVectorLenth(selectedArmy.x, selectedArmy.y, X, Y);
        var cosa = (X - selectedArmy.x)/vectorLenth;
        var sina = (Y - selectedArmy.y)/vectorLenth;

        $('.path').remove();

        var fieldX = Math.round(X/40);
        var fieldY = Math.round(Y/40);
        newX = fieldX*40;
        newY = fieldY*40;

        var pfX = selectedArmy.x/40;
        var pfY = selectedArmy.y/40;
        if(cosa>=0 && sina>=0) {
            movesSpend = downRight(pfX, pfY);
        } else if (cosa>=0 && sina<=0) {
            movesSpend = topRight(pfX, pfY);
        } else if (cosa<=0 && sina<=0) {
            movesSpend = topLeft(pfX, pfY);
        } else if (cosa<=0 && sina>=0) {
            movesSpend = downLeft(pfX, pfY);
        }

        $('#coord').html(newX + ' - ' + newY + ' ' + getTerrain(fields[fieldY][fieldX])[0]);
        return movesSpend;
    }
    return 0;
}

function getVectorLenth(x1, y1, x2, y2) {
    return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y1 - y2, 2))
}

function setCursorArrow(dir){
    if(cursorDirection != dir){
        board.css('cursor','url(../img/game/cursor_arrow_'+dir+'.png), crosshair');
        cursorDirection = dir;
    //         console.log(cursorDirection);
    }
}

function downRight(pfX, pfY) {
    var xLenthPixels = (newX - selectedArmy.x);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (newY - selectedArmy.y);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = 0;
    var dir = 'se';
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX += 1;
            pfY += 1;
            dir = 'se';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY += 1;
            dir = 's';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX += 1;
            pfY += 1;
            dir = 'se';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX += 1;
            dir = 'e';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    setCursorArrow(dir);
    return movesSpend;
}

function topRight(pfX, pfY) {
    var xLenthPixels = (newX - selectedArmy.x);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (selectedArmy.y - newY);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = 0;
    var dir = 'ne';
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX += 1;
            pfY -= 1;
            dir = 'ne';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY -= 1;
            dir = 'n';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX += 1;
            pfY -= 1;
            dir = 'ne';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX += 1;
            dir = 'e';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    setCursorArrow(dir);
    return movesSpend;
}

function topLeft(pfX, pfY) {
    var xLenthPixels = (selectedArmy.x - newX);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (selectedArmy.y - newY);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = 0;
    var dir = 'nw';
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX -= 1;
            pfY -= 1;
            dir = 'nw';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY -= 1;
            dir = 'n';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX -= 1;
            pfY -= 1;
            dir = 'nw';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX -= 1;
            dir = 'w';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    setCursorArrow(dir);
    return movesSpend;
}

function downLeft(pfX, pfY) {
    var xLenthPixels = (selectedArmy.x - newX);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (newY - selectedArmy.y);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = 0;
    var dir = 'sw';
    if(xLenthPixels < yLenthPixels) {
        dir = 'sw';
        for(i = 1; i <= xLenthPoints; i++) {
            pfX -= 1;
            pfY += 1;
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        dir = 's';
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY += 1;
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX -= 1;
            pfY += 1;
            dir = 'sw';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX -= 1;
            dir = 'w';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    setCursorArrow(dir);
    return movesSpend;
}

function addPathDiv(pfX,pfY,direction,movesSpend) {
    if(movesSpend >= selectedArmy.moves) {
        return movesSpend;
    }
    var terrainType = fields[pfY][pfX];
    pX = pfX*40;
    pY = pfY*40;
    var terrain = getTerrain(terrainType);
    var moves = movesSpend + terrain[1];
    if(moves > selectedArmy.moves) {
        return movesSpend;
    }
    board.append(
        $('<div>')
        .addClass('path')
        .css({
            background:'url(../img/game/footsteps_'+direction+'.png) center center no-repeat',
            left:pX,
            top:pY
        })
        .html(moves)
        );
    newX = pX;
    newY = pY;
    return moves;
}

function getTerrain(type) {
    var text;
    var moves;
    switch(type) {
        case 'r':
            text = 'Road';
            if(selectedArmy.canSwim){
                moves = 100;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 1;
            }
            break;
        case 'b':
            text = 'Bridge';
            if(selectedArmy.canSwim){
                moves = 1;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 1;
            }
            break;
        case 'c':
            text = 'Castle';
            moves = 1;
            break;
        case 'w':
            text = 'Water';
            if(selectedArmy.canSwim){
                moves = 1;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 100;
            }
            break;
        case 'm':
            text = 'Hills';
            if(selectedArmy.canSwim){
                moves = 200;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 5;
            }
            break;
        case 'M':
            text = 'Mountains';
            if(selectedArmy.canSwim){
                moves = 1000;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 100;
            }
            break;
        case 'g':
            text = 'Grassland';
            if(selectedArmy.canSwim){
                moves = 100;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 2;
            }
            break;
        case 'f':
            text = 'Forest';
            if(selectedArmy.canSwim){
                moves = 100;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 3;
            }
            break;
        case 's':
            text = 'Swamp';
            if(selectedArmy.canSwim){
                moves = 100;
            }else if(selectedArmy.canFly > 0){
                moves = 2;
            }else{
                moves = 4;
            }
            break;
    }
    return {
        0:text,
        1:moves
    };
}
