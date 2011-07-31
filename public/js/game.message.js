function startM(){
    removeM();
    $('#game').after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('Press "Start" when ready.'))
        .append($('<div>')
        .addClass('button go')
        .html('Start')
        .click(function(){
            $('.message').remove();
            initGame();
            wsConnect();
            showFirstCastle();
        })
        )
        .css({
            'min-height':'70px'
        })
        );
}

function lostM(){
    removeM();
    $('#game').after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('You lose.'))
        .append($('<div>')
        .addClass('button go')
        .html('Ok')
        .click(function(){
            $('.message').remove();

        })
        )
        .css({
            'min-height':'70px'
        })
        );
}

function splitArmyM(a){
    removeM();
    var army = $('<div>').addClass('split');
    var numberOfUnits = 0;
    for(i in a.soldiers) {
        var img = a.soldiers[i].name.replace(' ', '_').toLowerCase();
        army.append(
            $('<p>')
            .append(
                $('<img>').attr({
                    'src':'/img/game/' + img + '_' + a.color + '.png',
                    'id':'unit'+a.soldiers[i].soldierId
                })
            )
            .append(' Moves left: '+a.soldiers[i].movesLeft+' ')
            .append($('<input>').attr({
                type:'checkbox',
                name:'soldierId',
                value:a.soldiers[i].soldierId
            }))
        );
        numberOfUnits++;
    }
    for(i in a.heroes) {
        army.append(
            $('<p>')
            .append(
                $('<img>').attr({
                    'src':'/img/game/hero_' + a.color + '.png',
                    'id':'hero'+a.heroes[i].heroId
                })
            )
            .append(' Moves left: '+a.heroes[i].movesLeft+' ')
            .append($('<input>').attr({
                type:'checkbox',
                name:'heroId',
                value:a.heroes[i].heroId
            }))
        );
        numberOfUnits++;
    }
    var height = numberOfUnits * 31 + 32;
    $('#game').after(
        $('<div>')
        .addClass('message')
        .append(army)
        .append($('<div>').addClass('button cancel').html('Cancel').click(function(){$('.message').remove()}))
        .append($('<div>').addClass('button submit').html('Select units').click(function(){splitArmy(a.armyId)}))
        .css('min-height',height+'px')
    );

}

function castleM(castleId, color){
    removeM();
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
        .append($('<div>').addClass('button cancel').html('Cancel').click(function(){$('.message').remove()}))
        .append($('<div>').addClass('button submit').html('Set production').click(function(){setProduction(castleId)}))
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
    $('#game').after(
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
    .append($('<div>').addClass('button go').html('OK').click(function(){$('.message').remove()}))
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

function walkM(result) {
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
        lock = false;
        return null;
    } else {
        wsArmyMove(result.path[i].x, result.path[i].y, unselectedArmy.armyId);
        $('#army'+unselectedArmy.armyId).css({
            display:'none',
            left: result.path[i].x + 'px',
            top: result.path[i].y + 'px'
        });
        zoomer.lensSetCenter(result.path[i].x, result.path[i].y);
        $('#army'+unselectedArmy.armyId).fadeIn(1, function() {
            delete result.path[i];
            walkM(result);
        });
    }
}

function removeM(){
    if(typeof $('.message') != 'undefined') {
        $('.message').remove();
    }
}
