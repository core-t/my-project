function startM(){
    $('#game').after(
        $('<div>')
        .addClass('message')
        .append($('<h3>').addClass('center').html('Press "Start" when ready.'))
        .append($('<div>')
            .addClass('go')
            .html('Start')
            .click(function(){
                $('.message').remove();
                initGame();
                wsConnect();
                showFirstCastle();
            })
        )
        .css({
            height:'70px',
//             top:'20px'
        })
    );
}

function battleM(battle, a, def) {
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
        $('.message').append(defense);
    }
    if(h == 0) {
        $('.message').append($('<div>').addClass('battle defense'));
    }
    var height = 62 + 31 + 14 + h * 31;
    $('.message')
    .append($('<div>').addClass('go').html('OK').click(function(){$('.message').remove()}))
    .css('height',height+'px');
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
//     console.log(r[i]);
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

function walkM(result, el) {
    for(i in result.path) {
        break;
    }
    if(typeof result.path[i] == 'undefined') {
        deleteArmyByPosition(players[my.color].armies['army'+unselectedArmy.armyId].x, players[my.color].armies['army'+unselectedArmy.armyId].y, my.color);
        players[my.color].armies['army'+result.armyId] = new army(result, my.color);
        newX = players[my.color].armies['army'+result.armyId].x;
        newY = players[my.color].armies['army'+result.armyId].y;
        wsArmyAdd(result.armyId);
        lock = false;
        return null;
    } else {
        wsArmyMove(result.path[i].x, result.path[i].y, unselectedArmy.armyId);
        el.css({
            display:'none',
            left: result.path[i].x + 'px',
            top: result.path[i].y + 'px'
        });
        zoomer.lensSetCenter(result.path[i].x, result.path[i].y);
        el.fadeIn(1, function() {
            delete result.path[i];
            walkM(result, el);
        });
    }
}
