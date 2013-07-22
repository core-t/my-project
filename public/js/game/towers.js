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
            if(turn.color==my.color || (my.game && players[turn.color].computer)){
                wsAddTower(towerId);
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

function countPlayerTowers(color){
    var count = 0;
    for(i in towers){
        if(towers[i].color == color){
            count++;
        }
    }
    return count;
}