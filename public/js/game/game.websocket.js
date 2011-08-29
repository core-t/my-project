var lWSC = null;

function login() {
    var lURL = jws.getDefaultServerURL();

    try {
        var lAccessKey = 'access';
        return lWSC.logon( lURL, "guest", "guest", {

            // OnOpen callback
            OnOpen: function( aEvent ) {
                $('#wsStatus').html('connected');
                var lRes = lWSC.channelSubscribe( channel, lAccessKey );
            },

            OnWelcome: function() {
            },

            // OnMessage callback
            OnMessage: function( aEvent, aToken ) {
                if( lWSC.isLoggedIn() ) {
                    $('#wsStatus').html('authenticated');
                } else {
                    $('#wsStatus').html('connected');
                }

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
                        case 't':
                            getTurnA();
                            break;
                        case 'p':
                            updatePlayers(color);
                            break;
                        case 'c':
                            getCastleA(data[2]);
                            break;
                        case 'C':
                            var msg = '';
                            for(i in data){
                                if(msg){
                                    msg += '.';
                                }
                                msg += data[i];
                            }
                            if(msg){
                                titleBlink('Incoming chat!');
                                chat(color,msg);
                            }
                            break;
                        case 'a':
                            getArmyA(data[2],data[3]);
                            break;
                        case 'm':
                            changeArmyPosition(data[2], data[3], data[4], turn.color);
                            break;
                        case 'A':
                            getPlayerArmiesA(data[2]);
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
                                    var color = tmp[1];
                                    tmp = tmp[0].split('|');
                                    for(i in tmp){
                                        enemyArmies[i] = players[color].armies['army'+tmp[i]];
                                    }
                                }
                            }
                            zoomer.lensSetCenter(army.x, army.y);
                            battleM(battle, army, enemyArmies);
                            break;
                        default:
                            console.log(aToken.data);
                            break;
                    }
                }
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

// try to subscribe at a certain channel
function subscribeChannel() {
    var lAccessKey = 'access';
    return lWSC.channelSubscribe( channel, lAccessKey );
}

// try to authenticate against a channel to publish data
function auth() {
    var lAccessKey = 'access';
    var lSecretKey = 'secret';
    return lWSC.channelAuth( channel, lAccessKey, lSecretKey );
}

function wsCastle(castleId) {
    lWSC.channelPublish(channel,my.color+'.c.'+castleId);
}

function wsTurn() {
    lWSC.channelPublish(channel,my.color+'.t');
}

function wsChat() {
    var msg = $('#msg').val();
    $('#msg').val('');
    if(msg){
        chat(my.color,msg);
        lWSC.channelPublish(channel,my.color+'.C.'+msg);
    }
}

function wsPing() {
    lWSC.channelPublish(channel,my.color+'.p');
    for(color in players){
        if(color == my.color){
            continue;
        }
        if(players[color].active){
            players[color].active--;
            $('.'+color+' .turn').css('background','url(../img/game/smile.png) center center no-repeat');
        }else{
            $('.'+color+' .turn').css('background','url(../img/game/ajax_wait.gif) center center no-repeat');
        }
    }
}

function wsPlayerArmies(color){
    lWSC.channelPublish(channel,my.color+'.A.'+color);
}

function wsArmyMove(x, y, armyId) {
    lWSC.channelPublish(channel,my.color+'.m.'+x+'.'+y+'.'+armyId);
}

function wsArmy(armyId, center) {
    if(center){
        lWSC.channelPublish(channel,my.color+'.a.'+armyId+'.1');
    }else{
        lWSC.channelPublish(channel,my.color+'.a.'+armyId+'.0');
    }
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