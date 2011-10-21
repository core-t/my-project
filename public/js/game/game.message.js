function mElement(){
    return $('.terrain');
}

function startM(){
    removeM();
    mElement().after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('Press "Start" when ready.'))
        .append(
            $('<div>')
            .addClass('button go')
            .html('Start')
            .click(function(){
                removeM();
                startGame();
            })
        )
        .css('min-height','70px')
    );
}

function lostM(){
    removeM();
    mElement().after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('You lose.'))
        .append($('<div>')
        .addClass('button go')
        .html('Ok')
        .click(function(){
            removeM();

        })
        )
        .css({
            'min-height':'70px'
        })
    );
}

function winM(){
    removeM();
    mElement().after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('You win.'))
        .append($('<div>')
        .addClass('button go')
        .html('Ok')
        .click(function(){
            removeM();

        })
        )
        .css({
            'min-height':'70px'
        })
    );
}

function turnM(){
    removeM();
    mElement().after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('Your turn.'))
        .append($('<div>')
        .addClass('button go')
        .html('Ok')
        .click(function(){
            removeM();

        })
        )
        .css({
            'min-height':'70px'
        })
    );
}

function nextTurnM(){
    removeM();
    mElement().after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('Next turn. Are you sure?'))
        .append(
            $('<div>')
            .addClass('button go')
            .html('Ok')
            .click(function(){
                removeM();
                nextTurnA();
            })
        )
        .append($('<div>').addClass('button cancel').html('Cancel').click(function(){removeM()}))
        .css({
            'min-height':'70px'
        })
    );
}

function simpleM(message){
    removeM();
    mElement().after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html(message))
        .append(
            $('<div>')
            .addClass('button go')
            .html('Ok')
            .click(function(){removeM();})
        )
        .css('min-height','70px')
    );
}

function disbandArmyM(){
    if(typeof selectedArmy == 'undefined'){
        return null;
    }
    if(!my.turn){
        return null;
    }
    removeM();
    mElement().after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('Are you sure?'))
        .append(
            $('<div>')
            .addClass('button go')
            .html('Disband')
            .click(function(){disbandArmyA()})
        )
        .append($('<div>').addClass('button cancel').html('Cancel').click(function(){removeM()}))
        .css('min-height','70px')
    );
}

function splitArmyM(a){
    if(typeof selectedArmy == 'undefined'){
        return null;
    }
    removeM();
    var army = $('<div>').addClass('split');
    var numberOfUnits = 0;
    for(i in selectedArmy.soldiers) {
        var img = selectedArmy.soldiers[i].name.replace(' ', '_').toLowerCase();
        numberOfUnits++;
        army.append(
            $('<div>')
            .addClass('row')
            .append($('<div>').addClass('nr').html(numberOfUnits))
            .append($('<div>').addClass('img').html(
                $('<img>').attr({
                    'src':'/img/game/' + img + '_' + selectedArmy.color + '.png',
                    'id':'unit'+selectedArmy.soldiers[i].soldierId
                })
            ))
            .append($('<span>').html(' Moves left: '+selectedArmy.soldiers[i].movesLeft+' '))
            .append($('<div>').addClass('right').html($('<input>').attr({
                type:'checkbox',
                name:'soldierId',
                value:selectedArmy.soldiers[i].soldierId
            })))
        );
    }
    for(i in selectedArmy.heroes) {
        numberOfUnits++;
        army.append(
            $('<div>')
            .addClass('row')
            .append($('<div>').addClass('nr').html(numberOfUnits))
            .append($('<div>').addClass('img').html(
                $('<img>').attr({
                    'src':'/img/game/hero_' + selectedArmy.color + '.png',
                    'id':'hero'+selectedArmy.heroes[i].heroId
                })
            ))
            .append($('<span>').html(' Moves left: '+selectedArmy.heroes[i].movesLeft+' '))
            .append($('<div>').addClass('right').html($('<input>').attr({
                type:'checkbox',
                name:'heroId',
                value:selectedArmy.heroes[i].heroId
            })))
        );
    }
    var height = numberOfUnits * 31 + 38;
    if(height > 561){
        height = 561;
        overflow = 'scroll';
    }else{
        overflow = 'hidden';
    }
    mElement().after(
        $('<div>')
        .addClass('message')
        .append(army)
        .append(
            $('<div>')
            .css({'height':'28px','padding':'0 2px'})
            .append(
                $('<div>').addClass('left')
                .append($('<a>').addClass('button cancel').html('Cancel').click(function(){removeM()}))
            )
            .append(
                $('<div>').addClass('right')
                .append($('<a>').addClass('button submit').html('Select units').click(function(){splitArmyA(selectedArmy.armyId)}))
            )
        )
        .css({
            'min-height':height+'px',
            'height':height+'px',
            'overflow-y':overflow
        })
    );

}

function armyStatusM(){
    if(typeof selectedArmy == 'undefined'){
        return null;
    }
    removeM();
    var army = $('<div>').addClass('status');
    var numberOfUnits = 0;
    var bonusTower = 0;
    var castleDefense = getMyCastleDefenseFromPosition(selectedArmy.x, selectedArmy.y);
    if(isTowerAtPosition(selectedArmy.x, selectedArmy.y)){
        bonusTower = 1;
    }
    for(i in selectedArmy.soldiers) {
        numberOfUnits++;
        var img = selectedArmy.soldiers[i].name.replace(' ', '_').toLowerCase();
        var attackPoints = $('<p>').html(selectedArmy.soldiers[i].attackPoints).css('color','#da8');
        var defensePoints = $('<p>').html(selectedArmy.soldiers[i].defensePoints).css('color','#da8');
        if(selectedArmy.flyBonus && !selectedArmy.soldiers[i].canFly){
            attackPoints.append($('<span>').html(' +1').css('color','#d00000'));
            defensePoints.append($('<span>').html(' +1').css('color','#d00000'));
        }
        if(selectedArmy.heroKey){
            attackPoints.append($('<span>').html(' +1').css('color','#d00000'));
            defensePoints.append($('<span>').html(' +1').css('color','#d00000'));
        }
        if(bonusTower){
            defensePoints.append($('<span>').html(' +1').css('color','#d00000'));
        }
        if(castleDefense){
            defensePoints.append($('<span>').html(' +'+castleDefense).css('color','#d00000'));
        }
        army.append(
            $('<div>')
            .addClass('row')
            .append($('<div>').addClass('nr').html(numberOfUnits))
            .append($('<div>').addClass('img').html(
                $('<img>').attr({
                    'src':'/img/game/' + img + '_' + selectedArmy.color + '.png',
                    'id':'unit'+selectedArmy.soldiers[i].soldierId
                })
            ))
            .append(
                $('<div>').addClass('left')
                .append($('<p>').html('Current moves: '))
                .append($('<p>').html('Default moves: '))
                .append($('<p>').html('Attack points: '))
                .append($('<p>').html('Defense points: '))
            )
            .append(
                $('<div>').addClass('left')
                .append($('<p>').html(selectedArmy.soldiers[i].movesLeft).css('color','#da8'))
                .append($('<p>').html(selectedArmy.soldiers[i].numberOfMoves).css('color','#da8'))
                .append(attackPoints)
                .append(defensePoints)
            )
        );
    }
    for(i in selectedArmy.heroes) {
        numberOfUnits++;
        var attackPoints = $('<p>').html(selectedArmy.heroes[i].attackPoints).css('color','#da8');
        var defensePoints = $('<p>').html(selectedArmy.heroes[i].defensePoints).css('color','#da8');
        if(bonusTower){
            defensePoints.append($('<span>').html(' +1').css('color','#d00000'));
        }
        if(castleDefense){
            defensePoints.append($('<span>').html(' +'+castleDefense).css('color','#d00000'));
        }
        army.append(
            $('<div>')
            .addClass('row')
            .append($('<div>').addClass('nr').html(numberOfUnits))
            .append($('<div>').addClass('img').html(
                $('<img>').attr({
                    'src':'/img/game/hero_' + selectedArmy.color + '.png',
                    'id':'hero'+selectedArmy.heroes[i].heroId
                })
            ))
            .append(
                $('<div>').addClass('left')
                .append($('<p>').html('Current moves: '))
                .append($('<p>').html('Default moves: '))
                .append($('<p>').html('Attack points: '))
                .append($('<p>').html('Defense points: '))
            )
            .append(
                $('<div>').addClass('left')
                .append($('<p>').html(selectedArmy.heroes[i].movesLeft).css('color','#da8'))
                .append($('<p>').html(selectedArmy.heroes[i].numberOfMoves).css('color','#da8'))
                .append(attackPoints)
                .append(defensePoints)
            )

        );
    }
    var height = numberOfUnits * 56 + 26;
    if(height > 561){
        height = 561;
        overflow = 'auto';
    }else{
        overflow = 'hidden';
    }
    mElement().after(
        $('<div>')
        .addClass('message')
        .append(army)
        .append($('<div>').addClass('button cancel').html('Ok').click(function(){removeM()}))
        .css({
            'min-height':height+'px',
            'height':height+'px',
            'overflow':overflow
        })
    );
}

function castleM(castleId, color){
    if(lock){
        return null;
    }
    if(!my.turn){
        return null;
    }
    if(selectedArmy) {
        return null;
    }
    removeM();
    var time = '';
    var attr;
        var capital = null;
    if(castles[castleId].capital){
        capital = $('<h4>').append('Capital city');
    }
    var table = $('<table>').addClass('production').append($('<label>').html('Production:'));
    var j = 0;
    var td = new Array();
    for(unitName in castles[castleId].production){
        var img = unitName.replace(' ', '_').toLowerCase();
        var unitId = getUnitId(unitName);
        var travelBy = '';
        if(unitId == castles[castleId].currentProduction){
            attr = {
                type:'radio',
                name:'production',
                value:unitName,
                checked:'checked'
            }
            time = castles[castleId].currentProductionTurn+'/';
        } else {
            attr = {
                type:'radio',
                name:'production',
                value:unitName
            }
            time = '';
        }
        if(units[unitId].canFly){
            travelBy = 'ground/air';
        }else if(units[unitId].canSwimm){
            travelBy = 'water';
        }else{
            travelBy = 'ground';
        }
        td[j] = $('<td>')
        .addClass('unit')
        .append(
            $('<p>')
            .append($('<input>').attr(attr))
            .append(' '+unitName)
        )
        .append($('<div>').append($('<img>').attr('src','/img/game/' + img + '_' + color + '.png')))
        .append(
            $('<div>')
            .addClass('attributes')
            .append($('<p>').html('Time:&nbsp;'+time+castles[castleId].production[unitName].time+'t'))
            .append($('<p>').html('Cost:&nbsp;'+castles[castleId].production[unitName].cost+'g'))
            .append($('<p>').html(travelBy))
            .append($('<p>').html('M '+units[unitId].numberOfMoves+' . A '+units[unitId].attackPoints+' . D '+units[unitId].defensePoints))
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
    table.append(
        $('<tr>')
        .append(
            $('<td>')
            .append(
                $('<input>').attr({
                    type:'radio',
                    name:'production',
                    value:'stop'
                })
            )
            .append(' Stop production')
        )
    );
    var resurrectionElement;
    var resurrection = true;
    for(armyId in players[my.color].armies){
        for(j in players[my.color].armies[armyId].heroes){
            resurrection = false;
        }
    }
    if(resurrection){
        var buttonResurestion;
        var cssResurestion;
        if($('#gold').html() < 100){
            buttonResurestion = $('<div>').addClass('button right buttonOff').html('Hero resurrection');
            cssResurestion = {'color':'#d00000'};
        }else{
            buttonResurestion = $('<div>').addClass('button right').html('Hero resurrection').click(function(){heroResurrectionA(castleId)});
            cssResurestion = {};
        }
        resurrectionElement = $('<p>')
        .addClass('h')
        .css(cssResurestion)
        .append(
            $('<input>').attr({
                type:'checkbox',
                name:'resurrection',
                value:castleId
            })
        )
        .append(' cost 100g')
        .append(buttonResurestion);
    }
    var buttonBuilDefense;
    var cssBuilDefense;
    var costBuilDefense = 0;
    for(i = 1; i <= castles[castleId].defense; i++){
        costBuilDefense += i*100;
    }
    if($('#gold').html() < costBuilDefense){
        buttonBuilDefense = $('<div>').addClass('button right buttonOff').html('Build defense');
        cssBuilDefense = {'color':'#d00000'};
    }else{
        buttonBuilDefense = $('<div>').addClass('button right').html('Build defense').click(function(){castleBuildDefenseA()});
        cssBuilDefense = {};
    }
    var height = 62 + k*54 + 96;
    mElement().after(
        $('<div>')
        .addClass('message')
        .css('min-height',height+'px')
        .append(capital)
        .append($('<h3>').append(castles[castleId].name))
        .append($('<h5>').append('Position: '+castles[castleId].position['x']+' East - '+castles[castleId].position['y']+' South'))
        .append($('<h5>').append('Defense: '+castles[castleId].defense))
        .append($('<h5>').append('Income: '+castles[castleId].income+' gold/turn'))
        .append(table)
        .append(
            $('<p>')
            .append($('<div>').addClass('button submit').html('Set production').click(function(){setProductionA(castleId)}))
        )
        .append(
            $('<p>')
            .addClass('h')
            .css(cssBuilDefense)
            .append(
                $('<input>').attr({
                    type:'checkbox',
                    name:'defense',
                    value:castleId
                })
            )
            .append(' cost '+costBuilDefense+'g')
            .append(buttonBuilDefense)
        )
        .append(
            $('<p>')
            .addClass('h')
            .append(
                $('<input>').attr({
                    type:'checkbox',
                    name:'raze',
                    value:castleId
                })
            )
            .append(' income 1000g')
            .append($('<div>').addClass('button right').html('Raze').click(function(){razeCastleA()}))
        )
        .append(
            $('<p>')
            .append($('<div>').addClass('button cancel').html('Cancel').click(function(){removeM()}))
        )
        .append(resurrectionElement)
    );

}

function battleM(battle, a, def) {
    removeM();
    var attack = $('<div>').addClass('battle attack');
    for(i in a.soldiers) {
        var img = a.soldiers[i].name.replace(' ', '_').toLowerCase();
        attack.append(
            $('<img>').attr({
                'src':'/img/game/' + img + '_' + a.color + '.png',
                'id':'unit'+a.soldiers[i].soldierId
            })
        );
    }
    for(i in a.heroes) {
        attack.append(
            $('<img>').attr({
                'src':'/img/game/hero_' + a.color + '.png',
                'id':'hero'+a.heroes[i].heroId
            })
        );
    }
    mElement().after(
        $('<div>')
        .addClass('message')
        .css('display','none')
        .append(attack)
        .append($('<p>').html('VS').addClass('center'))
    );
    var h = 0;
    for(j in def) {
        var d = def[j];
        h++;
        var defense = $('<div>').addClass('battle defense');
        for(i in d.soldiers) {
            var img = d.soldiers[i].name.replace(' ', '_').toLowerCase();
            defense.append(
                $('<img>').attr({
                    'src':'/img/game/' + img + '_' + d.color + '.png',
                    'id':'unit'+d.soldiers[i].soldierId
                })
            );
        }
        for(i in d.heroes) {
            defense.append(
                $('<img>').attr({
                    'src':'/img/game/hero_' + d.color + '.png',
                    'id':'hero'+d.heroes[i].heroId
                })
            );
        }
        $('.message').append(defense);
    }
    if(h == 0) {
        $('.message').append($('<div>').addClass('battle defense'));
    }
    var height = 62 + 31 + 14 + h * 31;
    $('.message')
    .append($('<div>').addClass('button go').html('OK').click(function(){removeM()}))
    .css('min-height',height+'px');
    if(battle){
        $('.message').fadeIn(100, function(){
            killM(battle);
        })
    }
    wait = 0;
}

function killM(r){
    for(i in r) {
        break;
    }
    if(typeof r[i] == 'undefined') {
        return null;
    }
    if(typeof r[i].soldierId != 'undefined') {
        $('#unit'+r[i].soldierId).fadeOut(1500, function(){
            delete r[i];
            killM(r);
        });
    } else if(typeof r[i].heroId != 'undefined'){
        $('#hero'+r[i].heroId).fadeOut(1500, function(){
            delete r[i];
            killM(r);
        });
    } else {
        console.log('zonk');
    }
}

function removeM(){
    if(typeof $('.message') != 'undefined') {
        $('.message').remove();
    }
}

