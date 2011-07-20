function refresh() {
    $.getJSON(urlAjax, function(result) {
        console.log(result.start);
        if(result.start) {
            top.location = urlRedirect;
        } else {
            delete result.start;
            $('#playersingame').html('');
            $('#playersingame').append(th);
            $('#playersout').html('');
            $('#playersout').append(th);
            var playersReady = 0;
            for(i in result) {
                if(result[i].ready) {
                    playersReady++;
                    $('#playersingame').append('<tr id="' + result[i].color + '"><td>' + result[i].playerId + '</td><td>' + result[i].color + '</td><td id="ready' + result[i].playerId + '">' + result[i].ready + '</td></tr>');
                } else {
                    $('#playersout').append('<tr id="' + result[i].color + '"><td>' + result[i].playerId + '</td><td>' + result[i].color + '</td><td id="ready' + result[i].playerId + '">' + result[i].ready + '</td></tr>');
                }
                if(result[i].playerId == playerId) {
                    ready = result[i].ready;
                }
            }
            if(urlStart) {
                if(numberOfPlayers <= playersReady) {
                    $('#start').html('<a href="javascript:start()">Start game</a>');
                } else {
                    $('#start').html('Start game');
                }
            }
        }
        makeReadyStatus();
    });
}
$(document).ready(function() {
    setInterval ( 'refresh()', 5000 );
    refresh();
});
function playerReady() {
    console.log(ready);
    if(ready) ready = 0;
    else ready = 1;
    $.getJSON(urlReady + '/readystatus/' + ready, function(result) {
        if(result) {
            makeReadyStatus();
        }
    });
}
    
var makeReadyStatus = function () {
    if(ready) {
        var html = 'Unready';
    } else {
        var html = 'Ready';
    }
    $('#ready').html(html);
}
