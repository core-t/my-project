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

$(document).ready(function() {
    setTimeout('refresh()', 1500);
    setInterval ( 'refresh()', 5000 );
});

