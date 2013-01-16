
function login() {
    var lURL = jws.getServerURL(aSchema, aHost, aPort, aContext, aServlet);

    try {
        return lWSC.logon( lURL, "guest", "guest", {

            // OnOpen callback
            OnOpen: function( aEvent ) {
                $('#wsStatus').html('connected');
            },

            OnWelcome: function() {
            },

            // OnMessage callback
            OnMessage: function( aEvent, aToken ) {
                if( lWSC.isLoggedIn() ) {
                    if(typeof aToken.data != 'undefined'){
                        var data = aToken.data.split('.');
                        var color = data[0];
                        var event = data[1];
                        if(color == my.color){
                            return 0;
                        }
                        delete data[0];
                        delete data[1];
                        switch(event){
                            case 'p':
                                updatePlayers(color);
                                break;
                            case 'c':
                                getCastleA(data[2]);
                                break;
                            case 'T':
                                changeEnemyTower(data[2], color);
                                break;
                            case 'r':
                                console.log(data[2]);
                                console.log(data[3]);
                                ruinUpdate(data[2],data[3]);
                                break;
                            case 'm':
                                changeArmyPosition(data[2], data[3], data[4], turn.color);
                                break;
                            case 'b':
                                var battle = '';
                                var enemyArmies = new Array();
                                var tmp = data[2].split(',');
                                for(i in tmp){
                                    if(tmp[i].substr(0,1) == 's'){
                                        if(battle){
                                            battle += ',';
                                        }
                                        battle += '{"soldierId":"'+tmp[i].substr(1)+'"}';
                                    }else if(tmp[i].substr(0,1) == 'h'){
                                        if(battle){
                                            battle += ',';
                                        }
                                        battle += '{"heroId":"'+tmp[i].substr(1)+'"}';
                                    }
                                }
                                battle = jQuery.parseJSON('['+battle+']');
                                tmp = data[3].split(',');
                                var army = players[tmp[0]].armies['army'+tmp[1]];
                                if(typeof data[4] != 'undefined'){
                                    if(data[4] == 'n'){
                                        enemyArmies[0] = getNeutralCastleGarrison();
                                    }else{
                                        tmp = data[4].split(',');
                                        color = tmp[1];
                                        tmp = tmp[0].split('|');
                                        for(i in tmp){
                                            enemyArmies[i] = players[color].armies['army'+tmp[i]];
                                        }
                                    }
                                }
                                zoomer.lensSetCenter(army.x*40, army.y*40);
                                battleM(battle, army, enemyArmies);
                                break;
                            default:
                                console.log(aToken.data);
                                break;
                        }
                    }
                    $('#wsStatus').html('connected');
                }
                return 0;
            },

            // OnClose callback
            OnClose: function( aEvent ) {
                $('#wsStatus').html('connection closed');
                console.log('connection closed');
            }

        });
    } catch( ex ) {
        console.log( "Exception: " + ex.message );
    }
}

$(document).ready(function() {
    ws.onmessage = function(e) {
        var edata=$.parseJSON( e.data );

        if(typeof edata['type'] != 'undefined'){

            switch(edata.type){

                case 'move':
                    walk(edata.data, edata.color);
                    break;

                case 'army':
                    if(typeof edata.data.armyId != 'undefined') {
                        if(typeof players[edata.data.color].armies['army' + edata.data.armyId] != 'undefined'){
                            armyFields(players[edata.data.color].armies['army' + edata.data.armyId]);
                        }
                        players[edata.data.color].armies['army' + edata.data.armyId] = new army(edata.data, edata.data.color);
                        if(edata.data.center == 1){
                            removeM();
                            zoomer.lensSetCenter(players[edata.data.color].armies['army' + edata.data.armyId].x*40, players[edata.data.color].armies['army' + edata.data.armyId].y*40);
                        }
                    }
                    break;

                case 'ruin':
                    ruinUpdate(edata.data.ruinId, edata.data.empty);
                    break;

                case 'armies':
                    for(i in edata.data){
                        players[edata.color].armies[i] = new army(edata.data[i], edata.color);
                    }
                    break;

                case 'open':
                    webSocketOpen(edata.wssuid);
                    break;

                case 'chat':
                    if(edata.msg){
                        titleBlink('Incoming chat!');
                        chat(edata.color,edata.msg,makeTime());
                    }
                    break;

                case 'turn':
                    unselectArmy();
                    if(edata.data['lost']){
                        lostM();
                    }else{
                        //                        if(!data[3]){
                        //                            unset(data[3]);
                        //                        }
                        changeTurn(edata.data['color'], edata.data['nr']);
                        computerA();
                    }
                    break;

                case 'fightNeutralCastle':
                    var enemyArmies = {
                        0: getNeutralCastleGarrison()
                    };

                    if(edata.data.victory) {
                        players[edata.color].armies['army'+edata.data.armyId] = new army(edata.data, edata.color);
                        if(edata.color==my.color){
                            newX = players[edata.color].armies['army'+edata.data.armyId].x;
                            newY = players[edata.color].armies['army'+edata.data.armyId].y;
                        }
                        castleOwner(edata.data.castleId, edata.color);
                    } else {
                        deleteArmy('army' + edata.data.armyId, edata.color);
                    }
                    handleParentArmy();
                    battleM(edata.data.battle, players[edata.color].armies['army'+edata.data.armyId], enemyArmies);
                    if(edata.color==my.color){
                        unlock();
                    }
                    break;

                case 'fightEnemyCastle':
                    var enemyArmies = getEnemyCastleGarrison(edata.data.castleId);
                    if(edata.data.victory) {
                        players[edata.color].armies['army'+edata.data.armyId] = new army(edata.data, edata.color);
                        if(edata.color==my.color){
                            newX = players[edata.color].armies['army'+edata.data.armyId].x;
                            newY = players[edata.color].armies['army'+edata.data.armyId].y;
                        }
                        for(i in enemyArmies) {
                            deleteArmy('army' + enemyArmies[i].armyId, enemyArmies[i].color);
                        }
                        castleOwner(edata.data.castleId, edata.color);
                    } else {
                        deleteArmy('army' + edata.data.armyId, edata.color);
                    }
                    handleParentArmy();
                    battleM(edata.data.battle, players[edata.color].armies['army'+edata.data.armyId], enemyArmies);
                    if(edata.color==my.color){
                        unlock();
                    }
                    break;

                case 'fightEnemy':
                    console.log(edata);
                    if(edata.data.victory) {
                        players[edata.color].armies['army'+edata.data.armyId] = new army(edata.data, edata.color);
                        if(edata.color==my.color){
                            newX = players[edata.color].armies['army'+edata.data.armyId].x;
                            newY = players[edata.color].armies['army'+edata.data.armyId].y;
                        }
                        deleteArmyByPosition(x, y, edata.enemyArmy.color);
                    } else {
                        deleteArmy('army' + edata.data.armyId, edata.color, 1);
                    }
                    handleParentArmy();
                    battleM(edata.data.battle, players[edata.color].armies['army'+edata.data.armyId], {
                        0:edata.enemyArmy
                    });
                    if(edata.color==my.color){
                        unselectEnemyArmy();
                        unlock();
                    }
                    break;

                default:
                    console.log(edata);

            }
        }
    };

});

function wsCastle(castleId) {
    lWSC.channelPublish(channel,my.color+'.c.'+castleId);
}

function wsNextTurn() {
    var token = {
        type: 'turn',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsChat() {
    var msg = $('#msg').val();
    $('#msg').val('');
    if(msg){
        chat(my.color,msg,makeTime());

        var token = {
            type: 'chat',
            data: msg,
            gameId: gameId,
            playerId: my.id,
            color: my.color,
            accessKey: lAccessKey
        };

        ws.send(JSON.stringify(token));
    }
}

function wsPlayerArmies(color){
    var token = {
        type: 'armies',
        data:{
            color:color
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsArmyMove(x, y, armyId) {
    var token = {
        type: 'move',
        data:{
            x: x,
            y: y,
            armyId: armyId
        },
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };

    ws.send(JSON.stringify(token));
}

function wsArmy(armyId, center) {
    var token = {
        type: 'army',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey
    };
    if(center){
        token['data']={
            armyId: armyId,
            center: 1
        };
    }else{
        token['data']={
            armyId: armyId,
            center: 0
        };
    }
    ws.send(JSON.stringify(token));
}

function wsFightNeutralCastle(armyId, x, y, castleId){
    var token = {
        type: 'fightNeutralCastle',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightEnemyCastle(armyId, x, y, castleId){
    var token = {
        type: 'fightEnemyCastle',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            castleId:castleId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsFightEnemy(armyId, x, y, enemyArmyId){
    var token = {
        type: 'fightEnemy',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            armyId:armyId,
            x:x,
            y:y,
            enemyArmyId:enemyArmyId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsRuin(ruinId){
    var token = {
        type: 'ruin',
        gameId: gameId,
        playerId: my.id,
        color: my.color,
        accessKey: lAccessKey,
        data: {
            ruinId:ruinId
        }
    };

    ws.send(JSON.stringify(token));
}

function wsBattle(battle,army,armies){
    var data = my.color+'.b.';
    var tmp = '';
    for(i in battle){
        if(typeof battle[i].soldierId != 'undefined'){
            if(tmp){
                tmp += ',';
            }
            tmp += 's'+battle[i].soldierId;
        }
        if(typeof battle[i].heroId != 'undefined'){
            if(tmp){
                tmp += ',';
            }
            tmp += 'h'+battle[i].heroId;
        }
    }
    data += tmp+'.'+army.color+','+army.armyId;
    tmp = '';
    if(typeof armies != 'undefined'){
        if(typeof armies[0] != 'undefined' && typeof armies[0].color != 'undefined' && armies[0].color == 'neutral'){
            tmp = 'n';
        }else{
            for(i in armies){
                if(tmp){
                    tmp += '|';
                }
                tmp += armies[i].armyId;
            }
            if(tmp){
                tmp += ','+armies[i].color;
            }
        }
    }
    if(tmp){
        data += '.'+tmp;
    }
    lWSC.channelPublish(channel,data);
}