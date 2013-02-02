$(document)[0].oncontextmenu = function() {
    return false;
} // usuwa menu kontekstowe spod prawego przycisku

// *** TOWERS ***

function towerCreate(towerId){
    var title = 'Tower';
    board.append(
        $('<div>')
        .addClass('tower')
        .attr({
            id: 'tower' + towerId,
            title: title
        })
        .css({
            left: (towers[towerId].x*40) + 'px',
            top: (towers[towerId].y*40) + 'px',
            background:'url(../img/game/tower_'+towers[towerId].color+'.png) center center no-repeat'
        })
        );
}

function isTowerAtPosition(x, y){
    for(towerId in towers){
        if(towers[towerId].x == x && towers[towerId].y == y){
            return 1;
        }
    }
    return 0;
}

function searchTower(x, y){
    for(towerId in towers){
        if(towers[towerId].x == x && towers[towerId].y == y){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x-1) && towers[towerId].y == (y-1)){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x) && towers[towerId].y == (y-1)){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x+1) && towers[towerId].y == (y-1)){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x-1) && towers[towerId].y == (y)){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x+1) && towers[towerId].y == (y)){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x-1) && towers[towerId].y == (y+1)){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x) && towers[towerId].y == (y+1)){
            changeTower(x, y, towerId);
            continue;
        }
        if(towers[towerId].x == (x+1) && towers[towerId].y == (y+1)){
            changeTower(x, y, towerId);
            continue;
        }
    }
}

function changeTower(x, y, towerId){
    if(fields[y][x] != 'e'){
        if(towers[towerId].color != turn.color){
            if(turn.color==my.color){
                addTowerA(towerId);
            }
            towers[towerId].color = turn.color;
            $('#tower' + towerId).css('background','url(../img/game/tower_'+turn.color+'.png) center center no-repeat');
        }
        return true;
    }else{
        return false;
    }
}

function changeEnemyTower(towerId, color){
    towers[towerId].color = color;
    $('#tower' + towerId).css('background','url(../img/game/tower_'+color+'.png) center center no-repeat');
}

// *** RUINS ***

function ruinCreate(ruinId){
    var title;
    var css;
    if(typeof ruins[ruinId].e == 'undefined'){
        title = 'Ruins';
        css = '';
    }else{
        title = 'Ruins (empty)';
        css = '_empty';
    }
    board.append(
        $('<div>')
        .addClass('ruin')
        .attr({
            id: 'ruin' + ruinId,
            title: title
        })
        .css({
            left: (ruins[ruinId].x*40) + 'px',
            top: (ruins[ruinId].y*40) + 'px',
            background:'url(../img/game/ruin'+css+'.png) center center no-repeat'
        })
        );
}

function ruinUpdate(ruinId, empty){
    var title;
    var css;
    if(empty){
        ruins[ruinId].e = 1;
        title = 'Ruins (empty)';
        css = '_empty';
    }else{
        title = 'Ruins';
        css = '';
    }
    $('#ruin'+ruinId).attr('title',title)
    .css('background','url(../img/game/ruin'+css+'.png) center center no-repeat');
}

function getRuinId(a){
    for(i in ruins){
        if(a.x == ruins[i].x && a.y == ruins[i].y){
            if(typeof ruins[i].e == 'undefined'){
                return i;
            }
            return null;
        }
    }
    return null;
}

// *** CASTLES ***

function castleFields(castleId, type){
    x = castles[castleId].position.x;
    y = castles[castleId].position.y;
    fields[y][x] = type;
    fields[y+1][x] = type;
    fields[y][x+1] = type;
    fields[y+1][x+1] = type;
}

function createNeutralCastle(castleId) {
    castles[castleId].defense = castles[castleId].defensePoints;
    castles[castleId].color = null;

    board.append(
        $('<div>')
        .addClass('castle')
        .attr({
            id: 'castle' + castleId,
            title: castles[castleId].name+'('+castles[castleId].defense+')'
        })
        .css({
            left: (castles[castleId].position.x*40) + 'px',
            top: (castles[castleId].position.y*40) + 'px'
        })
        .mouseover(function(){
            castleCursor(this.id)
        })
        .mousemove(function(){
            castleCursor(this.id)
        })
        );
    castleFields(castleId, 'e');
    mX = castles[castleId].position.x*2;
    mY = castles[castleId].position.y*2;
    zoomPad.append(
        $('<div>').css({
            'left':mX + 'px',
            'top':mY + 'px'
        })
        .attr('id','c'+castleId)
        .addClass('c')
        );
}

function castleCursor(id){
    if(lock) {
        return;
    }
    if(my.turn && selectedArmy) {
        $('#' + id).css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
    } else {
        $('#' + id).css('cursor','default');
    }
}

function myCastleCursor(id){
    if(lock) {
        return;
    }
    if(my.turn && !selectedArmy) {
        $('#' + id).css('cursor', 'url(../img/game/cursor_castle.png), crosshair');
    } else {
        $('#' + id).css('cursor', 'default');
    }
}

function castleUpdate(data) {
    //    removeM();
    zoomer.lensSetCenter(castles[data.castleId].position['x']*40, castles[data.castleId].position['y']*40);
    if(data.razed){
        castles[data.castleId].razed = true;
        castleFields(data.castleId, 'g');
    }else{
        castles[data.castleId].defense = data.defensePoints;
        castles[data.castleId].currentProduction = data.production;
        castles[data.castleId].currentProductionTurn = data.productionTurn;
        updateCastleDefense(data.castleId, data.defenseMod);
    }
}

function castleOwner(castleId, color) {
    var castle = $('#castle' + castleId);

    if(typeof castles[castleId] != 'undefined' && castles[castleId].razed){
        castle.remove();
        $('#c'+castleId).remove();
        delete castles[castleId];
        return;
    }

    if(color == my.color) {
        castleFields(castleId, 'c');
        castle
        .css('z-index', 10)
        .unbind('mouseover')
        .unbind('mousemove')
        .unbind('click')
        .mouseover(function() {
            myCastleCursor(this.id)
        })
        .mousemove(function() {
            myCastleCursor(this.id)
        })
        .click(function(){
            castleM(castleId, color)
        });
    }
    else {
        castleFields(castleId, 'e');
        castle
        .css('z-index', 202)
        .unbind('mouseover')
        .unbind('mousemove')
        .unbind('click')
        .mouseover(function() {
            castleCursor(this.id)
        })
        .mousemove(function() {
            castleCursor(this.id)
        })
    }

    castle.removeClass()
    .addClass('castle ' + color)
    .html('')
    .css('background', 'url(../img/game/castle_'+color+'.png) center center no-repeat');

    castles[castleId].color = color;

    $('#c'+castleId).css('background',getColor(color));
//    castle.fadeIn(1);
}

function setMyCastleProduction(castleId){
    castles[castleId].currentProduction = players[my.color].castles[castleId].production;
    castles[castleId].currentProductionTurn = players[my.color].castles[castleId].productionTurn;
    if(castles[castleId].currentProduction){
        $('#castle' + castleId).html($('<img>').attr('src','../img/game/castle_production.png').css('float','right'));
    }
}

function updateCastleCurrentProductionTurn(castleId, productionTurn){
    castles[castleId].currentProductionTurn = productionTurn;
}

function updateCastleDefense(castleId, defenseMod){
    castles[castleId].defense += defenseMod;
    if(castles[castleId].defense > 0){
        $('#castle' + castleId).attr('title', castles[castleId].name+'('+castles[castleId].defense+')');
    }else{
        $('#castle' + castleId).attr('title', castles[castleId].name+'(1)');
    }
}

function isEnemyCastle(x, y) {
    for(castleId in castles) {
        if(castles[castleId].color == my.color) {
            continue;
        }
        var pos = castles[castleId].position;
        if((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
            return castleId;
        }
    }
    return false;
}

function isNeutralCastle(x, y) {
    for(castleId in castles) {
        if(castles[castleId].position.x == x && castles[castleId].position.y == y && castles[castleId].color == null){
            return true;
        }
    }
    return false;
}

function getMyCastleDefenseFromPosition(x, y) {
    for(castleId in castles) {
        if(castles[castleId].color == my.color) {
            var pos = castles[castleId].position;
            if((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castles[castleId].defense;
            }
        }
    }
    return 0;
}

function showFirstCastle() {
    var sp = $('#castle' + firstCastleId);
    zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
}

// *** ARMIES ***

function army(obj, color) {
    $('#army'+obj.armyId).remove();
    $('#'+obj.armyId).remove();
    if(obj.destroyed){
        if(typeof players[color].armies[obj.armyId] != 'undefined'){
            armyFields(players[color].armies[obj.armyId]);
            delete players[color].armies[obj.armyId];
        }
        return;
    }
    this.x = obj.x;
    this.y = obj.y;

    this.flyBonus = 0;
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
        if(typeof defense == 'undefined') {
            var defense = this.soldiers[soldier].defensePoints;
        }
        if(this.soldiers[soldier].defensePoints > defense) {
            defense = this.soldiers[soldier].defensePoints;
            if(defense > this.soldiers[this.soldierKey].defensePoints){
                this.soldierKey = soldier;
            }
        }
        if(typeof moves == 'undefined') {
            var moves = this.soldiers[soldier].numberOfMoves;
        }
        if(this.soldiers[soldier].numberOfMoves > moves) {
            moves = this.soldiers[soldier].numberOfMoves;
            if(moves > this.soldiers[this.soldierKey].numberOfMoves){
                this.soldierKey = soldier;
            }
        }
        if(typeof this.moves == 'undefined') {
            this.moves = this.soldiers[soldier].movesLeft;
        }
        if(this.soldiers[soldier].movesLeft < this.moves) {
            this.moves = this.soldiers[soldier].movesLeft;
        }
        if(this.soldiers[soldier].canFly){
            this.canFly++;
            if(!this.flyBonus){
                this.flyBonus = 1;
            }
        }
        else{
            this.canFly -= 200;
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
        console.log('Armia nie posiada jednostek:');
        console.log(obj);
        delete players[color].armies[obj.armyId];
        return;
    }
    this.element = $('<div>');
    if(color == my.color) { // moja armia
        this.element.click(function(e) {
            myArmyClick(this, e)
        });
        this.element.mouseover(function() {
            myArmyMouse(this.id)
        });
        this.element.mousemove(function() {
            myArmyMouse(this.id)
        });
        if(this.canSwim){
            if(fields[this.y][this.x] != 'S'){
                this.fieldType = fields[this.y][this.x];
            }
            fields[this.y][this.x] = 'S';
        }
    } else { // nie moja armia
        if(fields[this.y][this.x] != 'e'){
            this.fieldType = fields[this.y][this.x];
        }
        fields[this.y][this.x] = 'e';
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
        left:       (this.x*40) + 'px',
        top:        (this.y*40) + 'px'
    });
    this.element.append(
        $('<img>')
        .addClass('unit')
        .attr('src', '/img/game/' + this.img + '_' + color + '.png')
        );
    board.append(this.element);

    //    if(typeof dontFade == 'undefined'){
    //        $('#army'+obj.armyId).fadeIn(1);
    //    }
    this.armyId = obj.armyId;
    this.color = color;
    var mX = this.x*2;
    var mY = this.y*2;

    zoomPad.append(
        $('<div>').css({
            'left':mX + 'px',
            'top':mY + 'px',
            'background':getColor(color),
            'z-index':10
        })
        .attr('id',this.armyId)
        .addClass('a')
        );

}

function myArmyClick(obj, e){
    if(e.which == 1){
        if(lock) {
            return;
        }
        if(my.turn) {
            if(selectedArmy) {
                if(selectedArmy != players[my.color].armies[obj.id]) { // klikam na siebie
                    wsJoinArmy(players[my.color].armies[obj.id].armyId);
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
        return;
    }
    if(my.turn && !selectedArmy) {
        $('#'+id).css('cursor', 'url(../img/game/cursor_select.png), default');
    }
    else {
        $('#'+id).css('cursor', 'default');
    }
}

function enemyArmyMouse(el){
    return el.mouseover(function() {
        if(lock) {
            return;
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
            return;
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

function selectArmy(a) {
    var index = $.inArray( a.armyId, skippedArmies );
    if(index != -1){
        skippedArmies.splice(index,1);
    }
    index = $.inArray( a.armyId, quitedArmies );
    if(index != -1){
        quitedArmies.splice(index,1);
    }
    $('#army' + a.armyId).css('border','1px solid #ccc');
    $('#name').html(a.name);
    $('#moves').html(a.moves);
    $('#attack').html(a.attack);
    $('#defense').html(a.defense);
    $('#splitArmy').removeClass('buttonOff');
    $('#armyStatus').removeClass('buttonOff');
    $('#disbandArmy').removeClass('buttonOff');
    $('#skipArmy').removeClass('buttonOff');
    $('#quitArmy').removeClass('buttonOff');
    selectedArmy = a;
    if(typeof selectedArmy.heroKey != 'undefined' && getRuinId(selectedArmy) !== null){
        $('#searchRuins').removeClass('buttonOff');
    }
    zoomer.lensSetCenter(a.x*40, a.y*40);
}

function unselectArmy(skipJoin) {
    if(typeof skipJoin == 'undefined' && parentArmy && selectedArmy){
        if(selectedArmy.x == parentArmy.x && selectedArmy.y == parentArmy.y){
            wsJoinArmy(selectedArmy.armyId);
        }
    }
    //    $('#info').html('');
    $('#name').html('');
    $('#moves').html('');
    $('#attack').html('');
    $('#defense').html('');
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
    $('#armyStatus').addClass('buttonOff');
    $('#skipArmy').addClass('buttonOff');
    $('#quitArmy').addClass('buttonOff');
    $('#searchRuins').addClass('buttonOff');
    $('#disbandArmy').addClass('buttonOff');
    removeM();
}

function unselectEnemyArmy() {
    selectedEnemyArmy = null;
}

function deleteArmy(armyId, color, quiet) {
    if(typeof players[color].armies[armyId] == 'undefined') {
        console.log('Brak armi o armyId = '+armyId+' i kolorze ='+color);
    }
    if(quiet) {
        armyFields(players[color].armies[armyId]);
        $('#' + armyId).remove();
        $('#' + armyId.substr(4)).remove();
        delete players[color].armies[armyId];
    } else {
        zoomer.lensSetCenter(players[color].armies[armyId].x*40, players[color].armies[armyId].y*40);
        armyFields(players[color].armies[armyId]);
        $('#' + armyId).fadeOut(500, function() {
            $('#' + armyId).remove();
            $('#' + armyId.substr(4)).remove();
            delete players[color].armies[armyId];
        //            console.log('usuni\u0119ta ' + armyId + ' - ' + color);
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
    if(a.color == my.color){
        return;
    }
    if(typeof fields[a.y] == 'undefined'){
        console.log('Y error');
        return;
    }
    if(typeof fields[a.y][a.x] == 'undefined'){
        console.log('X error');
        return;
    }
    if(typeof a.fieldType == 'undefined'){
        return;
    }
    if(isEnemyCastle(a.x, a.y) !== false){
        fields[a.y][a.x] = 'e';
    }else{
        fields[a.y][a.x] = a.fieldType;
    }
}

function changeArmyPosition(x, y, armyId, color) {
    if(typeof players[color].armies['army'+armyId] != 'undefined') {
        removeM();
        zoomer.lensSetCenter(x*40, y*40);
        $('#army' + armyId).animate({
            left: (x*40) + 'px',
            top: (y*40) + 'px'
        },300);
    }else{
        console.log('Army undefined');
    }
}

function getEnemyCastleGarrison(castleId) {
    var pos = castles[castleId].position;
    var armies = new Array();
    for(color in players) {
        if(color == turn.color) {
            continue;
        }
        for(i in players[color].armies) {
            var a = players[color].armies[i];
            if((a.x >= pos.x) && (a.x <= (pos.x + 1)) && (a.y >= pos.y) && (a.y <= (pos.y + 1))) {
                armies[i] = a;
            }
        }
    }
    return armies;
}

function getNeutralCastleGarrison(){
    var numberOfSoldiers = Math.ceil(turn.nr/10);
    var string = '';
    for(i = 1; i <= numberOfSoldiers; i++){
        if(string){
            string += ',';
        }
        string += '{"soldierId":"s'+i+'","name":"light infantry"}';
    }
    return jQuery.parseJSON('{"color":"neutral","heroes":[],"soldiers":['+string+']}');
}

function findNextArmy() {
    if(!my.turn){
        return;
    }
    if(lock) {
        return;
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
        if($.inArray( players[my.color].armies[i].armyId, quitedArmies ) != -1){
            continue;
        }
        if(nextArmySelected) {
            nextArmy = i;
            reset = false;
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
        return;
    }
    if(lock) {
        return;
    }
    if(selectedArmy){
        skippedArmies.push(selectedArmy.armyId);
        unselectArmy();
        findNextArmy();
    }
}

function quitArmy(){
    if(!my.turn){
        return;
    }
    if(lock) {
        return;
    }
    if(selectedArmy){
        quitedArmies.push(selectedArmy.armyId);
        unselectArmy();
        findNextArmy();
    }
}

function computerArmiesUpdate(armies, color){
    var i;

    for(i in armies){
        break;
    }

    if(typeof armies[i] == 'undefined') {
        wsComputer();
        return;
    }

    players[color].armies[i] = new army(armies[i], color);

    delete armies[i];

    computerArmiesUpdate(armies, color);
}

function fight(r){
    console.log(r);
    if(r.victory) {
        players[r.attackerColor].armies['army'+r.attackerArmy.armyId] = new army(r.attackerArmy, r.attackerColor);
        if(r.attackerColor==my.color){
            newX = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x;
            newY = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y;
        }
        if(isTruthful(r.defenderArmy)){
            for(i in r.defenderArmy) {
                deleteArmy('army' + r.defenderArmy[i].armyId, r.defenderColor);
            }
        }
        if(isTruthful(r.castleId)){
            castleOwner(r.castleId, r.attackerColor);
        }
    } else {
        if(isTruthful(r.defenderArmy)){
            for(i in r.defenderArmy){
                players[r.defenderColor].armies['army'+r.defenderArmy[i].armyId] = new army(r.defenderArmy[i], r.defenderColor);
            }
        }
        deleteArmy('army' + r.attackerArmy.armyId, r.attackerColor);
    }

    if(r.attackerColor==my.color){
        unselectEnemyArmy();
        unlock();
    }
}

function move(r, computer) {
    if(typeof players[r.attackerColor].armies['army'+r.attackerArmy.armyId].fieldType != 'undefined'){
        fields[players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y][players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x] = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].fieldType;
    }
    walk(r, r.attackerColor, r.deletedIds, computer);
}

function walk(r, computer) {
    var i;

    for(i in r.path) {
        break;
    }

    if(typeof r.path[i] == 'undefined') {
        //        console.log(data);

        if(isTruthful(r.battle)){
            battleM(r, function(){
                walkEnd(r, r.attackerColor, r.deletedIds, computer);
            });
        }else{
            walkEnd(r, r.attackerColor, r.deletedIds, computer);
        }

        return;
    } else {
        zoomer.lensSetCenter(r.path[i].x*40, r.path[i].y*40);
        $('#army'+r.oldArmyId).animate({
            left: (r.path[i].x*40) + 'px',
            top: (r.path[i].y*40) + 'px'
        },300,
        function(){
            if(typeof r.path[i] == 'undefined'){
                console.log('coś tu niegra');
                console.log(r);
            }else{
                searchTower(r.path[i].x, r.path[i].y);
                delete r.path[i];
                walk(r, r.attackerColor, r.deletedIds, computer);
            }
        });
    }
}

function walkEnd(r, computer){
    players[r.attackerColor].armies['army'+r.attackerArmy.armyId] = new army(r.attackerArmy, r.attackerColor);
    newX = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x;
    newY = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y;

    if(r.attackerColor == my.color){
        if(players[r.attackerColor].armies['army'+r.attackerArmy.armyId].moves){
            selectArmy(players[r.attackerColor].armies['army'+r.attackerArmy.armyId]);
        }else{
            unselectArmy();
        }
        unlock();
    }

    if(isDigit(r.ruinId)){
        ruinUpdate(r.ruinId, 1);
    }

    if(typeof r.deletedIds == 'undefined'){
        console.log('?');
        return;
    }

    for(i in r.deletedIds){
        deleteArmy('army'+r.deletedIds[i]['armyId'], r.attackerColor, 1);
    }

    if(typeof computer != 'undefined'){
        wsComputer();
    }
}

function getEnemyArmy(enemyArmyId){
    for(color in players) {
        for(i in players[color].armies) {
            if(i == 'army'+enemyArmyId){
                return players[color].armies[i];
            }
        }
    }
    return null;
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
            if(typeof castlesPositionToId[destY+'_'+destX] != 'undefined'){
                castleFields(castlesPositionToId[destY+'_'+destX], 'c')
            }
            aStar(close, open, destX, destY, 1);
            $('#coord').html(destX + ' - ' + destY + ' ' + getTerrain(fields[destY][destX], selectedArmy)[0]);
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

function getTerrain(type, a) {
    var text;
    var moves;
    switch(type) {
        case 'b':
            text = 'Bridge';
            if(a.canSwim){
                moves = 1;
            }else if(a.canFly > 0){
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
            if(a.canSwim){
                moves = 100;
            }else if(a.canFly > 0){
                moves = 2;
            }else{
                moves = 3;
            }
            break;
        case 'g':
            text = 'Grassland';
            if(a.canSwim){
                moves = 100;
            }else if(a.canFly > 0){
                moves = 2;
            }else{
                moves = 2;
            }
            break;
        case 'm':
            text = 'Hills';
            if(a.canSwim){
                moves = 200;
            }else if(a.canFly > 0){
                moves = 2;
            }else{
                moves = 5;
            }
            break;
        case 'M':
            text = 'Mountains';
            if(a.canSwim){
                moves = 1000;
            }else if(a.canFly > 0){
                moves = 2;
            }else{
                moves = 100;
            }
            break;
        case 'r':
            text = 'Road';
            if(a.canSwim){
                moves = 100;
            }else if(a.canFly > 0){
                moves = 2;
            }else{
                moves = 1;
            }
            break;
        case 's':
            text = 'Swamp';
            if(a.canSwim){
                moves = 100;
            }else if(a.canFly > 0){
                moves = 2;
            }else{
                moves = 4;
            }
            break;
        case 'S':
            text = 'Ship';
            moves = 1;
            break;
        case 'w':
            text = 'Water';
            if(a.canSwim){
                moves = 1;
            }else if(a.canFly > 0){
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
            var g = getTerrain(type, selectedArmy)[1];
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

