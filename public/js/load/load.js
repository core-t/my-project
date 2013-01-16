function update(color){
    $.getJSON(urlUpdate+'/color/'+color, function(result) {
        var playersReady = 0;
        for(i in result) {
            if(result[i].ready) {
                playersReady++;
            }
            $('#'+result[i].color+'Id div.left').html(result[i].firstName+' '+result[i].lastName);
        }
        if(alivePlayers['length'] <= playersReady) {
            top.location = urlRedirect;
        }
    });
}

function refresh() {
    $.getJSON(urlRefresh, function(result) {
        var playersReady = 0;
        var gameMaster = '';
        var table = $('table#playersingame tr');
        for(i = 0; i < 4; i++){
            gameMaster = '';
            var color = $(table[i]).attr('id');
            if(typeof result[color] == 'undefined') {
                if(typeof alivePlayers[color] != 'undefined'){
                    if(gameMasterId == alivePlayers[color].playerId){
                        gameMaster = '/Game Master/';
                    }
                    if(alivePlayers[color].lost){
                        $('#'+color+'Id div.left').html('Dead '+gameMaster);
                    }else{
                        $('#'+color+'Id div.left').html('Waiting for "'+alivePlayers[color].firstName+' '+alivePlayers[color].lastName+'" '+gameMaster);
                    }
                }
            }else{
                if(result[color].ready) {
                    playersReady++;
                }
                if(gameMasterId == result[color].playerId){
                    gameMaster = '/Game Master/';
                }
                $('#'+color+'Id div.left').html(result[color].firstName+' '+result[color].lastName+' '+gameMaster);
            }
        }
        if(alivePlayers['length'] <= playersReady) {
            top.location = urlRedirect;
        }
    });
}

$(document).ready(function() {
    var i;
    var array = alivePlayers;
    alivePlayers = new Array();
    for(i in array){
        if(array[i].lost == false){
            alivePlayers['length']++;
        }
        alivePlayers[array[i].color] = array[i];
    }
    setTimeout('refresh()', 1500);
    setInterval ( 'refresh()', 5000 );
});

