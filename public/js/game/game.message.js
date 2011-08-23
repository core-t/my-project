function mElement(){
    return board;
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
            .click(function(){disbandArmy()})
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
        army.append(
            $('<p>')
            .append(
                $('<img>').attr({
                    'src':'/img/game/' + img + '_' + selectedArmy.color + '.png',
                    'id':'unit'+selectedArmy.soldiers[i].soldierId
                })
            )
            .append(' Moves left: '+selectedArmy.soldiers[i].movesLeft+' ')
            .append($('<input>').attr({
                type:'checkbox',
                name:'soldierId',
                value:selectedArmy.soldiers[i].soldierId
            }))
        );
        numberOfUnits++;
    }
    for(i in selectedArmy.heroes) {
        army.append(
            $('<p>')
            .append(
                $('<img>').attr({
                    'src':'/img/game/hero_' + selectedArmy.color + '.png',
                    'id':'hero'+selectedArmy.heroes[i].heroId
                })
            )
            .append(' Moves left: '+selectedArmy.heroes[i].movesLeft+' ')
            .append($('<input>').attr({
                type:'checkbox',
                name:'heroId',
                value:selectedArmy.heroes[i].heroId
            }))
        );
        numberOfUnits++;
    }
    var height = numberOfUnits * 31 + 32;
    mElement().after(
        $('<div>')
        .addClass('message')
        .append(army)
        .append($('<div>').addClass('button cancel').html('Cancel').click(function(){removeM()}))
        .append($('<div>').addClass('button submit').html('Select units').click(function(){splitArmyA(selectedArmy.armyId)}))
        .css('min-height',height+'px')
    );

}

function castleM(castleId, color){
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
        resurrectionElement = $('<p>')
        .addClass('h')
        .append(
            $('<input>').attr({
                type:'checkbox',
                name:'resurrection',
                value:castleId
            })
        )
        .append(' cost 100g')
        .append($('<div>').addClass('button right').html('Hero resurrection').click(function(){heroResurrectionA(castleId)}));
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
            .append(
                $('<input>').attr({
                    type:'checkbox',
                    name:'defense',
                    value:castleId
                })
            )
            .append(' cost 300g')
            .append($('<div>').addClass('button right').html('Build defense').click(function(){castleBuildDefenseA()}))
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
