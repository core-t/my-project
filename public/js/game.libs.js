$(document)[0].oncontextmenu = function() {
    return false;
} // usuwa menu kontekstowe spod prawego przycisku

// *** CASTLES ***

function createCastle(id) {
//     console.log(castles[i]);
    this.capital = castles[i].capital;
    this.position = castles[i].position;
    this.defense = castles[i].defensePoints;
    this.income = castles[i].income;
    this.name = castles[i].name;
    this.production = castles[i].production;
    this.color = '';
    this.element = $('<div>')
    .addClass('castle')
    .attr({
        id: 'castle' + id,
        title: castles[i].name
    })
    .css({
        left: this.position.x + 'px',
        top: this.position.y + 'px',
        position:'inherit',
        width:'80px',
        height:'80px',
        background: 'url(../img/game/castle_neutral.png) center center no-repeat',
        border:'none',
        cursor: 'crosshair'
    });
    board.append(this.element);
    this.element.fadeIn(1);
    this.element.mouseover(function() {
        if(lock) {
            return null;
        }
        if(turn.myTurn) {
            if(selectedArmy) {
                castles[this.id.substr(6)].element.css({
                    cursor: 'url(../img/game/cursor_attack.png), crosshair'
                });
            } else {
                castles[this.id.substr(6)].element.css('cursor','default');
            }
        }
    });
}

function castleOwner(castleId, color) {
    if(color == my.color) {
        zindex = 100;
        castles[castleId].element.mouseover(function() {
            if(turn.myTurn){
                if(selectedArmy) {
                    castles[this.id.substr(6)].element.css({
                        cursor: 'default'
                    });
                } else {
                    castles[this.id.substr(6)].element.css({
                        cursor: 'crosshair'
                    });
                }
            } else {
                castles[this.id.substr(6)].element.css({
                    cursor: 'default'
                });
            }
        });
        castles[castleId].element.mousemove(function() {
            if(turn.myTurn){
                if(selectedArmy) {
                    castles[this.id.substr(6)].element.css({
                        cursor: 'default'
                    });
                } else {
                    castles[this.id.substr(6)].element.css({
                        cursor: 'url(../img/game/cursor_castle.png), crosshair'
                    });
                }
            } else {
                castles[this.id.substr(6)].element.css({
                    cursor: 'default'
                });
            }
        });
        castles[castleId].element.click(function(){
            if(turn.myTurn){
                if(!selectedArmy) {
                    if(typeof $('.message') != 'undefined') {
                        $('.message').remove();
                    }
                    if(castles[castleId].capital){
                        var capital = $('<h4>').append('Capital city');
                    } else {
                        var capital = null;
                    }
                    var table = $('<table>').addClass('production').append($('<label>').html('Production:'));
                    var j = 0;
                    var td = new Array();
                    for(unitName in castles[castleId].production){
                        var img = unitName.replace(' ', '_').toLowerCase();
                        if(getUnitId(unitName) == castles[castleId].currentProduction){
                            var attr = {
                                type:'radio',
                                name:'production',
                                value:unitName,
                                checked:'checked'
                            }
                        } else {
                            var attr = {
                                type:'radio',
                                name:'production',
                                value:unitName
                            }
                        }
                        td[j] = $('<td>')
                        .addClass('unit')
                        .append($('<div>').append($('<img>').attr('src','/img/game/' + img + '_' + color + '.png')))
                        .append(
                            $('<div>')
                            .append($('<p>').html('Time:&nbsp;'+castles[castleId].production[unitName].time+'t'))
                            .append($('<p>').html('Cost:&nbsp;'+castles[castleId].production[unitName].cost+'g'))
                        )
                        .append(
                            $('<p>')
                            .append($('<input>').attr(attr))
                            .append(' '+unitName)
                        );
                        j++;
                    }
                    var k = Math.ceil(j/2);
                    for(l = 0; l < k; l++) {
                        var tr = $('<tr>');
                        var m = l*2;
                        tr.append(td[m]);
                        if(typeof td[m+1] == 'undefined') {
                            tr.append($('<td>').addClass('unit').html('&nbsp;'));
                        } else {
                            tr.append(td[m+1]);
                        }
                        table.append(tr);
                    }
                    $('#game').after(
                        $('<div>')
                        .addClass('message')
                        .append(capital)
                        .append($('<h3>').append(castles[castleId].name))
//                         .append($('<div>').addClass('close').click(function(){$('.message').remove()}))
                        .append($('<h5>').append('Position: '+castles[castleId].position['x']+' East - '+castles[castleId].position['y']+' South'))
                        .append($('<h5>').append('Defense: '+castles[castleId].defense))
                        .append($('<h5>').append('Income: '+castles[castleId].income+' gold/turn'))
                        .append(table)
                        .append($('<div>').addClass('cancel').html('Cancel').click(function(){$('.message').remove()}))
                        .append($('<div>').addClass('submit').html('Set production').click(function(){setProduction(castleId)}))
                    );
                }
            }
        });
    } else {
        zindex = 600;
        castles[castleId].element.mouseover(function() {
            if(lock) {
                return null;
            }
            if(turn.myTurn && selectedArmy){
                castles[this.id.substr(6)].element.css({
                    cursor: 'url(../img/game/cursor_attack.png), crosshair'
                });
            } else {
                castles[this.id.substr(6)].element.css({
                    cursor: 'default'
                });
            }
        });
        castles[castleId].element.mousemove(function() {
            if(lock) {
                return null;
            }
            if(turn.myTurn && selectedArmy){
                castles[this.id.substr(6)].element.css({
                    cursor: 'url(../img/game/cursor_attack.png), crosshair'
                });
            } else {
                castles[this.id.substr(6)].element.css({
                    cursor: 'default'
                });
            }
        });
    }
    castles[castleId].element
    .removeClass()
    .addClass('castle castle_' + color)
    .css({
        'z-index':zindex,
        background: 'url(../img/game/castle_'+color+'.png) center center no-repeat'
    });
    castles[castleId].element.fadeIn(1);
    castles[castleId].color = color;
    if(typeof players[color].castles[castleId] == 'undefined'){
        castles[castleId].currentProduction = null;
        castles[castleId].currentProductionTurn = 0;
    } else {
        castles[castleId].currentProduction = players[color].castles[castleId].production;
        castles[castleId].currentProductionTurn = players[color].castles[castleId].productionTurn;
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
    var sp = $('.castle_' + turn.color);
    zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
}

// *** ARMIES ***

function army(obj, color) {
    var position = changePointToPosition(obj.position);
    this.x = position[0];
    this.y = position[1];
    deleteArmyByPosition(this.x, this.y, color);
    if(typeof $('#army'+obj.armyId) != 'undefined') {
        $('#army'+obj.armyId).fadeOut(1);
        $('#army'+obj.armyId).remove();
    }
    this.heroes = obj.heroes;
    var numberOfUnits = 0;
    for(hero in this.heroes) {
        numberOfUnits++;
        this.heroKey = hero;
        if(typeof this.moves == 'undefined') {
            this.moves = this.heroes[hero].movesLeft;
        }
        if(this.heroes[hero].movesLeft < this.moves) {
            this.moves = this.heroes[hero].movesLeft;
            this.heroKey = hero;
        }
    }
    this.soldiers = obj.soldiers;
//     for(j = N - 1; j > 0; j--)
//     {
//         p = 1;
//         for(i = 0; i < j; i++)
//             if(d[i] > d[i + 1])
//             {
//                 x = d[i]; d[i] = d[i + 1]; d[i + 1] = x;
//                 p = 0;
//             }
//             if(p) break;
//     }
//     for(j = 0; j < N - 1; j++)
//         for(i = 0; i < N - 1; i++)
//             if(d[i] > d[i + 1])
//             {
//                 x = d[i]; d[i] = d[i + 1]; d[i + 1] = x;
//             };
//     for(soldier in this.soldiers) {numberOfUnits++;}
//     for(j = 0; j < numberOfUnits - i; j++){
//         for(soldier in this.soldiers) {
//             var s = this.soldiers;
//             if(typeof this.moves == 'undefined') {
//                 this.moves = s[soldier].movesLeft;
//             }
//             if(s[soldier].movesLeft < this.moves) {
//                 this.moves = s[soldier].movesLeft;
//             }
//             if(s[soldier].attackPoints > s[soldier+1].attackPoints) {
//                 attack = s[soldier].attackPoints;
//                 this.soldierKey = soldier;
//             }
//         }
//     }
    for(soldier in this.soldiers) {
        numberOfUnits++;
        if(typeof this.moves == 'undefined') {
            this.moves = this.soldiers[soldier].movesLeft;
        }
        if(this.soldiers[soldier].movesLeft < this.moves) {
            this.moves = this.soldiers[soldier].movesLeft;
        }
        if(typeof attack  == 'undefined') {
            var attack = this.soldiers[soldier].attackPoints;
            this.soldierKey = soldier;
        }
        if(this.soldiers[soldier].attackPoints > attack) {
            attack = this.soldiers[soldier].attackPoints;
            this.soldierKey = soldier;
        }
    }
    if(typeof this.heroes[this.heroKey] != 'undefined') {
        this.name = 'hero';
        this.img = 'hero';
    } else if(typeof this.soldiers[this.soldierKey] != 'undefined') {
        this.name = this.soldiers[this.soldierKey].name;
        this.img = this.name.replace(' ', '_').toLowerCase();
    } else {
        console.log('Armia nie posiada jednostek.');
        return null;
    }
    this.element = $('<div>');
    if(color == my.color) { // moja armia
        this.element.click(function(e) {
            if(lock) {
                return null;
            }
            if(turn.myTurn) {
                if(selectedArmy) {
                    if(selectedArmy == players[my.color].armies[this.id]) { // klikam na siebie
                        var zindex = selectedArmy.element.css('z-index') - 1;
                        selectedArmy.element.css({
                            'z-index':zindex
                        });
                        console.log(selectedArmy);
                    } else { // klikam na inną jednostkę
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
            if(turn.myTurn) {
                if(selectedArmy) {
                    if(selectedArmy == players[my.color].armies[this.id]) {
                        selectedArmy.element.css('cursor', 'default');
                    } else {
                        players[my.color].armies[this.id].element.css('cursor', 'url(../img/game/cursor_select.png), default');
                    }
                } else {
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
            if(turn.myTurn) {
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
    if(numberOfUnits > 8) {
        numberOfUnits = 8;
    }
    this.element
    .addClass('army')
    .addClass(color)
    .attr({
        id: 'army' + obj.armyId,
        title: color + ' army ' + obj.armyId
    }).css({
        background: 'url(../img/game/flag_' + color + '_'+numberOfUnits+'.png) top left no-repeat',
        left:       this.x + 'px',
        top:        this.y + 'px'
    });
    this.img = $('<img>')
    .addClass('unit')
    .attr('src', '/img/game/' + this.img + '_' + color + '.png');
    this.element.append(this.img);
    board.append(this.element);
    this.element.fadeIn(500, function() {
//         zoomer.lensSetCenter(this.x, this.y);
    });
    this.armyId = obj.armyId;
    this.color = color;
}

function selectArmy(a) {
    a.element.css({
        border:'1px solid #ccc'
    });
    $('#info').html('');
    $('#name').html(a.name);
    $('#moves').html('Moves: '+a.moves);
    selectedArmy = a;
    zoomer.lensSetCenter(a.x, a.y);
}

function unselectArmy() {
    if(selectedArmy != null) {
        unselectedArmy = selectedArmy;
        selectedArmy.element.css({
            border:'none'
        });
        board.css('cursor', 'default');
    }
    selectedArmy = null;
    $('#info').html('');
    $('#name').html('');
    $('#moves').html('');
    $('.path').remove();
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
//                 console.log(a);
                armies[i] = a;
            }
        }
    }
    return armies;
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

        $('#info').html(movesSpend);
        $('#coord').html(newX + ' - ' + newY + ' ' + getTerrain(fields[fieldY][fieldX])[0]);
        return movesSpend;
    }
    return 0;
}

function getVectorLenth(x1, y1, x2, y2) {
    return Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y1 - y2, 2))
}

function downRight(pfX, pfY) {
    var xLenthPixels = (newX - selectedArmy.x);
    var xLenthPoints = xLenthPixels/40;
    var yLenthPixels = (newY - selectedArmy.y);
    var yLenthPoints = yLenthPixels/40;
    var movesSpend = 0;
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX += 1;
            pfY += 1;
            m = addPathDiv(pfX,pfY,'se',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY += 1;
            m = addPathDiv(pfX,pfY,'s',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX += 1;
            pfY += 1;
            m = addPathDiv(pfX,pfY,'se',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX += 1;
            m = addPathDiv(pfX,pfY,'e',movesSpend);
            if(!m || m == movesSpend) {
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
    var movesSpend = 0;
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX += 1;
            pfY -= 1;
            m = addPathDiv(pfX,pfY,'ne',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY -= 1;
            m = addPathDiv(pfX,pfY,'n',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX += 1;
            pfY -= 1;
            m = addPathDiv(pfX,pfY,'ne',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX += 1;
            m = addPathDiv(pfX,pfY,'e',movesSpend);
            if(!m || m == movesSpend) {
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
    var movesSpend = 0;
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX -= 1;
            pfY -= 1;
            m = addPathDiv(pfX,pfY,'nw',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY -= 1;
            m = addPathDiv(pfX,pfY,'n',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX -= 1;
            pfY -= 1;
            m = addPathDiv(pfX,pfY,'nw',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX -= 1;
            m = addPathDiv(pfX,pfY,'w',movesSpend);
            if(!m || m == movesSpend) {
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
    var movesSpend = 0;
    if(xLenthPixels < yLenthPixels) {
        for(i = 1; i <= xLenthPoints; i++) {
            pfX -= 1;
            pfY += 1;
            m = addPathDiv(pfX,pfY,'sw',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (yLenthPoints - xLenthPoints); i++) {
            pfY += 1;
            m = addPathDiv(pfX,pfY,'s',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    } else {
        for(i = 1; i <= yLenthPoints; i++) {
            pfX -= 1;
            pfY += 1;
            m = addPathDiv(pfX,pfY,'sw',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
        for(i = 1; i <= (xLenthPoints - yLenthPoints); i++) {
            pfX -= 1;
            m = addPathDiv(pfX,pfY,'w',movesSpend);
            if(!m || m == movesSpend) {
                return movesSpend;
            }
            movesSpend = m;
        }
    }
    return movesSpend;
}

function addPathDiv(pfX,pfY,direction,movesSpend) {
    if(movesSpend >= selectedArmy.moves) {
        return movesSpend;
    }
    var terrainType = fields[pfY][pfX];
    if(terrainType == 'M' || terrainType == 'w') {
        return 0;
    }
    pX = pfX*40;
    pY = pfY*40;
    var terrain = getTerrain(terrainType);
    //     console.log(terrain);
    if((movesSpend + terrain[1]) > selectedArmy.moves) {
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
    );
    newX = pX;
    newY = pY;
    return movesSpend + terrain[1];
}

function getTerrain(type) {
    switch(type) {
        case 'r':
            return {
                0:'Road',
                1:1
            };
        case 'w':
            return {
                0:'Water',
                1:100
            };
        case 'm':
            return {
                0:'Hills',
                1:5
            };
        case 'M':
            return {
                0:'Mountains',
                1:100
            };
        case 'g':
            return {
                0:'Grassland',
                1:2
            };
        case 'f':
            return {
                0:'Forest',
                1:3
            };
        case 's':
            return {
                0:'Swamp',
                1:4
            };
    }
}
