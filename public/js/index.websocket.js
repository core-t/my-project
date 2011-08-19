var lWSC = null;

function login() {
    var lURL = jws.getDefaultServerURL();

    try {
        var lAccessKey = 'access';
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

function exitPage() {
    lWSC.stopKeepAlive();
    logout();
}
