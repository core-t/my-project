// *** ARMIES ***

function showFirstArmy(color){
    for(i in players[color].armies){
        zoomer.lensSetCenter(players[color].armies[i].x*40, players[color].armies[i].y*40);
        return;
    }
    zoomer.lensSetCenter(30, 30);
}

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
        enemyArmyMouse(this);
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
        $('#'+id+' *').css('cursor', 'url(../img/game/cursor_select.png), default');
        $('#'+id).css('cursor', 'url(../img/game/cursor_select.png), default');
    }
    else {
        $('#'+id+' *').css('cursor', 'url(../img/game/cursor.png), default');
        $('#'+id).css('cursor', 'url(../img/game/cursor.png), default');
    }
}

function armiesAddCursorWhenSelectedArmy(){
    $('.army:not(.'+my.color+')').css('cursor', 'url(../img/game/cursor_attack.png), crosshair');
}

function armiesAddCursorWhenUnselectedArmy(){
    $('.army:not(.'+my.color+')').css('cursor','url(../img/game/cursor.png), default');
}

function enemyArmyMouse(army){
    army.element.mouseover(function() {
        if(lock) {
            return;
        }
        if(my.turn && selectedArmy) {
            selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
            fields[army.y][army.x] = 'c';
        }
    })
    .mousemove(function() {
        if(lock) {
            return;
        }
        if(my.turn && selectedArmy) {
            selectedEnemyArmy = players[$(this).attr("class").split(' ')[1]].armies[this.id];
            fields[army.y][army.x] = 'c';
        }
    })
    .mouseout(function(){
        fields[army.y][army.x] = 'e';
    });
}

function setParentArmy(army) {
    parentArmy = army;
}

function selectArmy(a) {
    castlesAddCursorWhenSelectedArmy();
    armiesAddCursorWhenSelectedArmy();
    myCastlesRemoveCursor();

    var index = $.inArray( a.armyId, skippedArmies );
    if(index != -1){
        skippedArmies.splice(index,1);
    }
    index = $.inArray( a.armyId, quitedArmies );
    if(index != -1){
        quitedArmies.splice(index,1);
    }
    $('#army' + a.armyId).css({
        'box-shadow':'0 0 10px #fff',
        'border':'1px solid #fff'
    });
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

    castlesAddCursorWhenUnselectedArmy();
    armiesAddCursorWhenUnselectedArmy();
    myCastlesAddCursor();

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
        $('#army' + selectedArmy.armyId).css({
            'box-shadow':'none',
            'border':'none'
        });
        board.css('cursor', 'url(../img/game/cursor.png), default');
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

function move(r, computer) {
    zoomer.lensSetCenter(r.path[1].x*40, r.path[1].y*40);
    if(typeof players[r.attackerColor].armies['army'+r.attackerArmy.armyId].fieldType != 'undefined'){
        fields[players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y][players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x] = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].fieldType;
    }
    walk(r, null, computer);
}

function walk(r, xy, computer) {
    var i;

    for(i in r.path) {
        break;
    }

    if(typeof r.path[i] == 'undefined') {
        //        console.log(data);

        zoomer.lensSetCenter(xy.x*40, xy.y*40);

        if(isTruthful(r.battle)){
            battleM(r, function(){
                walkEnd(r, r.attackerColor, r.deletedIds, computer);
            });
        }else{
            walkEnd(r, r.attackerColor, r.deletedIds, computer);
        }

        return;
    } else {
        //        zoomer.lensSetCenter(r.path[i].x*40, r.path[i].y*40);
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
                xy = r.path[i];
                delete r.path[i];
                walk(r, xy, r.attackerColor, r.deletedIds, computer);
            }
        });
    }
}

function walkEnd(r, computer){
    players[r.attackerColor].armies['army'+r.attackerArmy.armyId] = new army(r.attackerArmy, r.attackerColor);
    newX = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].x;
    newY = players[r.attackerColor].armies['army'+r.attackerArmy.armyId].y;

    if(r.attackerColor == my.color){
        if(!r.castleId && players[r.attackerColor].armies['army'+r.attackerArmy.armyId].moves){
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
