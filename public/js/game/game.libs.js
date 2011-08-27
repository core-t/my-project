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
            top: ruins[ruinId].y + 'px'
        })
        );
    $('#ruin' + ruinId).fadeIn(1);
}

// *** CASTLES ***

//function substrCastleId(id){
//    return id.substr(6);
//}

function castleFields(castleId, type){
    x = castles[castleId].position.x;
    y = castles[castleId].position.y;
    fields[y/40][x/40] = type;
    fields[y/40+1][x/40] = type;
    fields[y/40][x/40+1] = type;
    fields[y/40+1][x/40+1] = type;
}

function createNeutralCastle(castleId) {
    castles[castleId].defense = castles[castleId].defensePoints;
    castles[castleId].color = null;
    board.append(
        $('<div>')
        .addClass('castle')
        .attr({
            id: 'castle' + castleId,
            title: castles[castleId].name
        })
        .css({
            left: castles[castleId].position.x + 'px',
            top: castles[castleId].position.y + 'px'
        })
        .mouseover(function(){castleCursor(this.id)})
        .mousemove(function(){castleCursor(this.id)})
    );
    castleFields(castleId, 'e');
}

function castleCursor(id){
    if(lock) {
        return null;
    }
    if(my.turn && selectedArmy) {
        $('#' + id).css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
    } else {
        $('#' + id).css('cursor','default');
    }
}

function myCastleCursor(id){
    if(lock) {
        return null;
    }
    if(my.turn && !selectedArmy) {
        $('#' + id).css('cursor', 'url(../img/game/cursor_castle.png), crosshair');
    } else {
        $('#' + id).css('cursor', 'default');
    }
}

function castleUpdate(data) {
//    removeM();
    zoomer.lensSetCenter(castles[data.castleId].position['x'], castles[data.castleId].position['y']);
    if(data.razed){
        castles[data.castleId].razed = true;
        castleFields(data.castleId, 'g');
    }else{
        castles[data.castleId].defense = data.defense;
        castles[data.castleId].currentProduction = data.production;
        castles[data.castleId].currentProductionTurn = data.productionTurn;
    }
}

function castleOwner(castleId, color) {
    var castle = $('#castle' + castleId);
    if(typeof castles[castleId] != 'undefined' && castles[castleId].razed){
        castle.remove();
        delete castles[castleId];
        return null;
    }
    if(color == my.color) {
        castleFields(castleId, 'c');
        castle
        .css('z-index', 100)
        .unbind('mouseover')
        .unbind('mousemove')
        .unbind('click')
        .mouseover(function() {myCastleCursor(this.id)})
        .mousemove(function() {myCastleCursor(this.id)})
        .click(function(){castleM(castleId, color)});
    } else {
        castleFields(castleId, 'e');
        castle
        .css('z-index', 600)
        .unbind('mouseover')
        .unbind('mousemove')
        .unbind('click')
        .mouseover(function() {castleCursor(this.id)})
        .mousemove(function() {castleCursor(this.id)})
    }
    castle.removeClass()
    .addClass('castle ' + color)
    .html('')
    .css('background', 'url(../img/game/castle_'+color+'.png) center center no-repeat');
    castles[castleId].color = color;

    castle.fadeIn(1);
}

function setMyCastleProduction(castleId){
    castles[castleId].currentProduction = players[my.color].castles[castleId].production;
    castles[castleId].currentProductionTurn = players[my.color].castles[castleId].productionTurn;
    if(castles[castleId].currentProduction){
        $('#castle' + castleId).html($('<img>').attr('src','../img/game/castle_production.png').css('float','right'));
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

//function checkCastleVectorLength(castleId){
//    x = newX;
//    y = newY;
//    vectorLenth = getVectorLength(unselectedArmy.x, unselectedArmy.y, x, y);
//    if(vectorLenth < 80) {
//        return {'x':x, 'y':y};
//    }
//    x = castles[castleId].position.x;
//    y = castles[castleId].position.y;
//    vectorLenth = getVectorLength(unselectedArmy.x, unselectedArmy.y, x, y);
//    if(vectorLenth < 80) {
//        return {'x':x, 'y':y};
//    }
//    x = castles[castleId].position.x + 40;
//    y = castles[castleId].position.y;
//    vectorLenth = getVectorLength(unselectedArmy.x, unselectedArmy.y, x, y);
//    if(vectorLenth < 80) {
//        return {'x':x, 'y':y};
//    }
//    x = castles[castleId].position.x;
//    y = castles[castleId].position.y + 40;
//    vectorLenth = getVectorLength(unselectedArmy.x, unselectedArmy.y, x, y);
//    if(vectorLenth < 80) {
//        return {'x':x, 'y':y};
//    }
//    x = castles[castleId].position.x + 40;
//    y = castles[castleId].position.y + 40;
//    vectorLenth = getVectorLength(unselectedArmy.x, unselectedArmy.y, x, y);
//    if(vectorLenth < 80) {
//        return {'x':x, 'y':y};
//    }
//    return null;
//}

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
    var x = this.x/40;
    var y = this.y/40;
    this.fieldType = fields[y][x];
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
        if(this.heroes[this.heroKey].name){
            this.name = this.heroes[this.heroKey].name;
        }else{
            this.name = 'Anonymous hero';
        }
        this.img = 'hero';
        this.attack = this.heroes[this.heroKey].attackPoints;
        this.defense = this.heroes[this.heroKey].defensePoints;
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
        this.element.click(function(e) {myArmyClick(this, e)});
        this.element.mouseover(function() {myArmyMouse(this.id)});
        this.element.mousemove(function() {myArmyMouse(this.id)});
    } else { // nie moja armia
        fields[y][x] = 'e';
        enemyArmyMouse(this.element);
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

function myArmyWin(result){
//    deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
//    $('#army'+unselectedArmy.armyId).remove();
    players[my.color].armies['army'+unselectedArmy.armyId] = new army(result, my.color);
    newX = players[my.color].armies['army'+unselectedArmy.armyId].x;
    newY = players[my.color].armies['army'+unselectedArmy.armyId].y;
    wsArmy(unselectedArmy.armyId);
}

function myArmyClick(obj, e){
    if(e.which == 1){
        if(lock) {
            return null;
        }
        if(my.turn) {
            if(selectedArmy) {
                if(selectedArmy == players[my.color].armies[obj.id]) { // klikam na siebie
                    unselectArmy();
                } else { // klikam na inną jednostkę
                    armyToJoinId = players[my.color].armies[obj.id].armyId;
                    moveA(cursorPosition(e.pageX, e.pageY));
                }
            } else {
                unselectArmy();
                selectArmy(players[my.color].armies[obj.id]);
            }
        }
    }
}

function myArmyMouse(id){
    if(lock) {
        return null;
    }
    if(my.turn && !selectedArmy) {
        $('#'+id).css('cursor', 'url(../img/game/cursor_select.png), default');
    } else {
        $('#'+id).css('cursor', 'default');
    }
}

function enemyArmyMouse(el){
    return el.mouseover(function() {
        if(lock) {
            return null;
        }
        if(my.turn && selectedArmy) {
            selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
            $('#'+this.id).css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
        } else {
            $('#'+this.id).css('cursor', 'default');
        }
    })
    .mousemove(function() {
        if(lock) {
            return null;
        }
        if(my.turn && selectedArmy) {
            selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
            $('#'+this.id).css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
        } else {
            $('#'+this.id).css('cursor', 'default');
        }
    });
}

function setParentArmy(army) {
    parentArmy = army;
}

function unsetParentArmy() {
    parentArmy = null;
}

function handleParentArmy(){
    if(parentArmy){
        getArmyA(parentArmy.armyId);
        wsArmy(parentArmy.armyId);
        unsetParentArmy();
    }
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

function joinSplitedArmy(){
    if(selectedArmy.x == parentArmy.x && selectedArmy.y == parentArmy.y){
        joinArmyA(parentArmy.armyId, selectedArmy.armyId);
    }
}

function unselectArmy() {
    if(parentArmy){
        joinSplitedArmy();
    }
    $('#info').html(0);
    $('#name').html('');
    $('#moves').html(0);
    $('#attack').html(0);
    $('#defense').html(0);
    tmpUnselectArmy();
}

function tmpUnselectArmy() {
    if(selectedArmy) {
        unselectedArmy = selectedArmy;
        $('#army' + selectedArmy.armyId).css('border','none');
        board.css('cursor', 'default');
    }
    selectedArmy = null;
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
            armyFields(players[color].armies[armyId]);
            $('#' + armyId).remove();
            delete players[color].armies[armyId];
        }
    } else {
        zoomer.lensSetCenter(players[color].armies[armyId].x, players[color].armies[armyId].y);
        armyFields(players[color].armies[armyId]);
        $('#' + armyId).fadeOut(500, function() {
            $('#' + armyId).remove();
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

function armyFields(a){
    x = a.x/40;
    y = a.y/40;
    fields[y][x] = a.fieldType;
}

function changeArmyPosition(x, y, armyId, color) {
    if(typeof players[color].armies['army'+armyId] != 'undefined') {
        removeM();
        zoomer.lensSetCenter(x, y);
        $('#army' + armyId).animate({
            left: x + 'px',
            top: y + 'px'
        },300);
        players[color].armies['army'+armyId].x = x;
        players[color].armies['army'+armyId].y = y;
    }
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
        if (typeof players[my.color].armies[i].armyId == 'undefined') {
            continue;
        }
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
                if(typeof players[my.color].armies[nextArmy].armyId != 'undefined'){
                    selectArmy(players[my.color].armies[nextArmy]);
                }else{
                    console.log(players[my.color].armies[nextArmy]);
                    skipArmy();
                }
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
        unselectArmy();
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
        wsArmy(result.armyId);
        handleParentArmy();
        selectArmy(players[my.color].armies['army'+result.armyId]);
        unlock();
        return null;
    } else {
        wsArmyMove(result.path[i].x, result.path[i].y, unselectedArmy.armyId);
        zoomer.lensSetCenter(result.path[i].x, result.path[i].y);
        $('#army'+unselectedArmy.armyId).animate({
            left: result.path[i].x + 'px',
            top: result.path[i].y + 'px'
        },300,
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
    if(selectedArmy) {
        var offset = $('.zoomWindow').offset();
        var X = x - 20 - parseInt(board.css('left')) - offset.left;
        var Y = y - 20 - parseInt(board.css('top')) - offset.top;
        var vectorLenth = getVectorLength(selectedArmy.x, selectedArmy.y, X, Y);
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
    return null;
}

function getVectorLength(x1, y1, x2, y2) {
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
    var movesSpend = null;
    var dir = 'se';
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX += 1;
            pfY += 1;
            dir = 'se';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY += 1;
            dir = 's';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
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
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX += 1;
            dir = 'e';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    return movesSpend;
}

function topRight(pfX, pfY) {
    var xLenthPixels = (newX - selectedArmy.x);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (selectedArmy.y - newY);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = null;
    var dir = 'ne';
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX += 1;
            pfY -= 1;
            dir = 'ne';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY -= 1;
            dir = 'n';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
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
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX += 1;
            dir = 'e';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    return movesSpend;
}

function topLeft(pfX, pfY) {
    var xLenthPixels = (selectedArmy.x - newX);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (selectedArmy.y - newY);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = null;
    var dir = 'nw';
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX -= 1;
            pfY -= 1;
            dir = 'nw';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY -= 1;
            dir = 'n';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
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
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX -= 1;
            dir = 'w';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    return movesSpend;
}

function downLeft(pfX, pfY) {
    var xLenthPixels = (selectedArmy.x - newX);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (newY - selectedArmy.y);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = null;
    var dir = 'sw';
    if(xLenthPixels < yLenthPixels) {
        dir = 'sw';
        for(i = 1; i <= xLenthPoints; i++) {
            pfX -= 1;
            pfY += 1;
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        dir = 's';
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY += 1;
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
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
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX -= 1;
            dir = 'w';
            m = addPathDiv(pfX,pfY,dir,movesSpend);
            if(m === null || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    return movesSpend;
}

function addPathDiv(pfX,pfY,direction,movesSpend) {
    setCursorArrow(direction);
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
            moves = 0;
            break;
        case 'e':
            text = 'Enemy';
            moves = null;
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
        default:
            console.log('error');
            console.log(type);
    }
    return {
        0:text,
        1:moves
    };
}
