
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

