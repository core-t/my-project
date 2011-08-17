function refresh() {
    $.getJSON(urlAjax, function(result) {
        if(result.start) {
            top.location = urlRedirect;
        } else {
            delete result.start;
            $('#playersout').html('');
            $('#playersout').append(th);
            var playersReady = 0;
            for(i = 0; i < numberOfPlayers; i++) {
                $('#'+colors[i]+' #td1').html('');
                $('#'+colors[i]+' #td2 a').html('Ready');
            }
            for(i in result){
                if(result[i].ready) {
                    playersReady++;
                    $('#'+result[i].color+' #td1').html(result[i].playerId);
                    if(result[i].playerId == playerId){
                        if(ready) {
                            var html = 'Unready';
                        } else {
                            var html = 'Ready';
                        }
                        $('#'+result[i].color+' #td2 a').html(html);
                    }else{
                        $('#'+result[i].color+' #td2 a').html('');
                    }
                } else {
                    $('#playersout').append('<tr><td>' + result[i].playerId + '</td></tr>');
                }
            }
            if(urlStart) {
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
    });
}
