var start = 0;
var gameMaster = 0;
var playersReady = 0;


$(document).ready(function() {
    initWebSocket();
});

function initWebSocket(){
    ws = new WebSocket(wsURL+'/public');

    ws.onopen = function() {
        wsClosed = false;
        wsRegister();
    };

    ws.onmessage = function(e) {
        var r=$.parseJSON( e.data );

        if(typeof r.type == 'undefined'){
            return;
        }

        switch(r.type){

            case 'start':
                top.location = '/gamesetup/start';
                break;

            case 'update':
                //                console.log(r);
                if(typeof r.gameMasterId == 'undefined'){
                    return;
                }

                $('#playersout').html('');

                prepareButtons(r.gameMasterId);

                var playersReady = 0;

                for(i in r){
                    if(typeof r[i].color == 'undefined'){
                        continue;
                    }
                    if(r[i].color){
                        playersReady++;
                        $('#'+r[i].color+' .td1 div.left').html(r[i].firstName+' '+r[i].lastName);


                        if(r[i].playerId == playerId){
                            $('#'+r[i].color+' .td2 a').html('Unselect');
                        }else{
                            if(r.gameMasterId == playerId){
                                $('#'+r[i].color+' .td2 a').html('Kick');
                            }else{
                                $('#'+r[i].color+' .td2 a').remove();
                            }
                        }

                        if(r[i].computer){
                            $('#'+r[i].color+' .td3').html('Computer');
                        }else{
                            $('#'+r[i].color+' .td3').html('Human');
                        }
                    }else{
                        if(r[i].computer){
                            continue;
                        }
                        $('#playersout').append('<tr><td># ' + r[i].firstName+' '+r[i].lastName + '</td></tr>');
                    }
                }

                prepareStartButton(r.gameMasterId, playersReady);
                break;
        }
    };

    ws.onclose = function() {
        wsClosed = true;
        setTimeout ( 'initWebSocket()', 1000 );
    };

}

function wsRegister(){
    var token = {
        type: 'register',
        gameId:gameId,
        playerId:playerId,
        accessKey:accessKey
    };

    ws.send(JSON.stringify(token));
}

function wsChange(color){
    var token = {
        type: 'change',
        color:color
    };

    ws.send(JSON.stringify(token));
}

function wsComputer(color){
    var token = {
        type: 'computer',
        color:color
    };

    ws.send(JSON.stringify(token));
}

function prepareButtons(gameMasterId){
    for(i = 0; i < numberOfPlayers; i++) {
        $('#'+colors[i]+' .td1 div.left').html('');

        $('#'+colors[i]+' .td2').html(
            $('<a>')
            .addClass('button')
            .html('Select')
            .attr('id',colors[i])
            .click(function(){
                wsChange(this.id)
            }));

        if(gameMasterId == playerId){
            $('#'+colors[i]+' .td3').html(
                $('<a>')
                .addClass('button')
                .html('Set computer')
                .attr('id',colors[i])
                .click(function(){
                    wsComputer(this.id)
                }));
        }else{
            $('#'+colors[i]+' .td3').html('');
        }
    }
}

function prepareStartButton(gameMasterId, playersReady){
    if(gameMasterId == playerId){
        $('#start').html($('<a>').addClass('button').html('Start game'));
        $('#start a').click(function(){
            if(start){
                wsStart();
            //                    $('#start a').addClass('buttonOff');
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

function wsStart(){
    var token = {
        type: 'start'
    };

    ws.send(JSON.stringify(token));
}