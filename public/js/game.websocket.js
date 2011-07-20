var FancyWebSocket = function(url){
    var conn = new WebSocket(url);

    var callbacks = {};

    this.bind = function(event_name, callback){
        callbacks[event_name] = callbacks[event_name] || [];
        callbacks[event_name].push(callback);
        return this;// chainable
    };

    this.send = function(event_name, event_data){
        var payload = JSON.stringify({
            event:event_name, 
            data: event_data
        });
        conn.send( payload ); // <= send JSON data to socket server
        return this;
    };

    // dispatch to the right handlers
    conn.onmessage = function(evt){
        var data = evt.data;
        if(data.substr(0,1) != '{') {
            data = data.substr(1);
        }
        var json = JSON.parse(data);
        dispatch(json.event, json.data);
    };

    conn.onclose = function(){
        $('#log').html('DISCONNECTED!'+this.readyState);
        dispatch('close',null)
    }
    conn.onopen = function(){
        $('#log').html('CONNECTED'+this.readyState);
        dispatch('open',null)

    }

    var dispatch = function(event_name, message){
        var chain = callbacks[event_name];
        if(typeof chain == 'undefined') return; // no callbacks for this event
        for(var i = 0; i < chain.length; i++){
            chain[i]( message )
        }
    }
};

// $(document).ready(function() {
//     setInterval ( 'wsConnect()', 1000 );
// });

function wsConnect() {
    socket = new FancyWebSocket('ws://localhost:12345/');
    //     socket = new FancyWebSocket('ws://82.160.41.159:12345/');
    socket.bind('turn', function(data){
        changeTurn(data.playerId, data.color);
    });
    socket.bind('move', function(data){
        changeArmyPosition(data.x, data.y, data.armyId, turn.color);
    });
    socket.bind('add', function(data){
        getAddArmy(data.armyId);
    });
    socket.bind('delete', function(data){
        deleteArmy('army'+data.armyId, data.color);
    });
    socket.bind('castleOwner', function(data){
        console.log(data);
        castleOwner(data.castleId, data.color);
    });
}

function wsCastleOwner(castleId, color) {
    console.log(castleId);
    console.log(color);
    socket.send(
        'castleOwner',
        {
            castleId:castleId,
            color:color
        }
        );
}

function wsTurn(playerId, color) {
    socket.send(
        'turn',
        {
            playerId:playerId,
            color:color
        }
        );
}

function wsPing() {
    socket.send(
        'ping',
        {
            playerId:my.playerId

        }
        );
}

function wsArmyMove(x, y, armyId) {
    socket.send(
        'move',
        {
            x:x,
            y:y,
            armyId:armyId
        }
        );
}

function wsArmyAdd(armyId) {
    socket.send(
        'add',
        {
            armyId:armyId
        }
        );
}

function wsArmyDelete(armyId, color) {
    socket.send(
        'delete',
        {
            armyId:armyId,
            color:color
        }
        );
}
