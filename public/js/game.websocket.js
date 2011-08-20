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
                    var event = aToken.data.substr(0,1);
                    var data = aToken.data.split('.');
                    switch(event){
                        case 't':
                            getTurn();
                            break;
                        case 'c':
                            castleGet(data[1]);
                            break;
                        case 'a':
                            getAddArmy(data[1]);
                            break;
                        case 'm':
                            changeArmyPosition(data[1], data[2], data[3], turn.color);
                            break;
                        case 's':
                            getPlayerArmies(data[1]);
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

// log out the client from the jWebSocket server
function logout() {
    if( lWSC ) {
        lWSC.stopKeepAlive();
        var lRes = lWSC.close({
            timeout: 3000
        });
    }
}

// try to subscribe at a certain channel
function subscribeChannel() {
    var lAccessKey = 'access';
    var lRes = lWSC.channelSubscribe( channel, lAccessKey );
}

// try to authenticate against a channel to publish data
function auth() {
    var lAccessKey = 'access';
    var lSecretKey = 'secret';
    // use access key and secret key for this channel to authenticate
    // required to publish data only
    var lRes = lWSC.channelAuth( channel, lAccessKey, lSecretKey );
}

function connect(){
    if(lWSC.isOpened()){
//        lWSC.startKeepAlive({
//            interval: 3000
//        });
//        board.css('display','block');
        lock = false;
        startM();
    }else{
        login();
        simpleM('Sorry, server is disconnected.');
        setTimeout ( 'connect()', 5000 );
    }
}

function exitPage() {
    lWSC.stopKeepAlive();
    logout();
}
function wsCastleOwner(castleId, color) {
    lWSC.channelPublish(channel,'c.'+castleId+'.'+color);
}

function wsTurn() {
    lWSC.channelPublish(channel,'t');
}

function wsPlayerArmies(color){
    lWSC.channelPublish(channel,'s.'+color);
}

function wsArmyMove(x, y, armyId) {
    lWSC.channelPublish(channel,'m.'+x+'.'+y+'.'+armyId);
}

function wsArmyAdd(armyId) {
    lWSC.channelPublish(channel,'a.'+armyId);
}
