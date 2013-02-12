function showOpen(open){
    for(i in open){
        var pX = open[i].x * 40;
        var pY = open[i].y * 40;
        board.append(
            $('<div>')
            .addClass('path2')
            .css({
                left:pX,
                top:pY,
                'text-align':'center',
                'z-index':999
            })
            .html(open[i].H+' '+open[i].G+' '+open[i].F)
            );
    }
}

function showClose(close){
    for(i in close){
        var pX = close[i].x * 40;
        var pY = close[i].y * 40;
        board.append(
            $('<div>')
            .addClass('path2')
            .css({
                left:pX,
                top:pY,
                'text-align':'center',
                'z-index':999,
                'background':'#000',
                'opacity':'0.33'
            })
            .html(close[i].H+' '+close[i].G+' '+close[i].F)
            );
    }
}

function test(){
    var all = 1;
    var pX = null;
    var pY = null;

    for(y in fieldsOryginal){
        for(x in fieldsOryginal[y]){
            if(fieldsOryginal[y][x] == 'e'){
                console.log(y+' '+x);
            }
        }
    }

    $('.path').remove();
    for(y in fields){
        for(x in fields[y]){
            if(fields[y][x] == 'e'){
                pX = x*40;
                pY = y*40;
                board.append(
                    $('<div>')
                    .addClass('path')
                    .css({
                        left:pX,
                        top:pY,
                        'text-align':'center',
                        'z-index':100
                    })
                    .html('e')
                    );
            }
        //            else if(!fields[y][x]){
        //                pX = x*40;
        //                pY = y*40;
        //                board.append(
        //                    $('<div>')
        //                    .addClass('path')
        //                    .css({
        //                        left:pX,
        //                        top:pY,
        //                        'text-align':'center',
        //                        'z-index':10000
        //                    })
        //                    .html('X')
        //                    );
        //            }else if(all){
        //                pX = x*40;
        //                pY = y*40;
        //                board.append(
        //                    $('<div>')
        //                    .addClass('path')
        //                    .css({
        //                        left:pX,
        //                        top:pY,
        //                        'text-align':'center',
        //                        'z-index':10000
        //                    })
        //                    .html(fields[y][x])
        //                    );
        //            }
        }
    }
//    console.log('PLAYERS:');
//    for(color in players) {
//        for(i in players[color].armies) {
//            console.log(i);
//        }
//    }
}
