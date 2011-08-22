function refresh() {
    $.getJSON(urlRefresh, function(result) {
//         console.log(result);
        if(result.start) {
            top.location = urlRedirectStart;
        }else if(result.kick) {
            top.location = urlRedirectKick;
        } else {
            delete result.start;
            delete result.kick;
            $('#playersout').html('');
            var playersReady = 0;
            for(i = 0; i < numberOfPlayers; i++) {
                $('#'+colors[i]+' #td1').html('');
                $('#'+colors[i]+' #td2').html(
                    $('<a>')
                    .addClass('button')
                    .html('Select')
                    .attr('id',colors[i])
                )
                $('#'+colors[i]+' #td2 a').click(function(){playerReady(this.id)});
            }
            for(i in result){
                if(result[i].ready) {
                    playersReady++;
                    $('#'+result[i].color+' #td1').html(result[i].firstName+' '+result[i].lastName);
                    if(result[i].playerId == playerId){
                        if(ready) {
                            var html = 'Unselect';
                        } else {
                            var html = 'Select';
                        }
                        $('#'+result[i].color+' #td2 a').html(html);
                    }else if(result[i].gameMasterId == playerId){
                        $('#'+result[i].color+' #td2 a')
                        .html('Kick')
                        .click(function(){kick(this.id)});
                    }else{
                        $('#'+result[i].color+' #td2').html('');
                    }

                } else {
                    $('#playersout').append('<tr><td>' + result[i].firstName+' '+result[i].lastName + '</td></tr>');
                }
            }
            if(result[i].gameMasterId == playerId) {
                $('#start').html($('<a>').addClass('button').html('Start game'));
                $('#start a').click(function(){
                    if(start){
                        $.getJSON(urlStart, function(result) {
                            $('#start a').addClass('buttonOff');
                        });
                    }
                });
                if(numberOfPlayers <= playersReady) {
                    $('#start a').removeClass('buttonOff');
                    start = 1;
                } else {
                    $('#start a').addClass('buttonOff');
                    start = 0;
                }
            }
        }
    });
}
$(document).ready(function() {
    setInterval ( 'refresh()', 5000 );
    refresh();
});
function playerReady(color) {
    $.getJSON(urlReady+'/color/'+color, function(result) {
        if(typeof result.ready != 'undefined') {
            ready = result.ready;
        }
        refresh();
    });
}

function kick(color){
    $.getJSON(urlKick+'/color/'+color, function(result) {
        refresh();
    });
}
