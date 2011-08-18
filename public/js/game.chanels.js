    var eLog = null,
        eKeepAlive = null,
        eMessage = null,
        eChannelId = null,
        eChannelName = null,
        eAccessKey = null,
        eSecretKey = null,
        eIsPrivate = null,
        eIsSystem = null,
        eChannelSel = null;

    function log( aString ) {
        console.log(aString);
        eLog.html(aString);
    }

    var lWSC = null;

    function login() {
        var lURL = jws.getDefaultServerURL();

        log( "Login to " + lURL + " ..." );
        try {
            var lRes = lWSC.logon( lURL, "guest", "guest", {

                // OnOpen callback
                OnOpen: function( aEvent ) {
                    log( "jWebSocket connection established." );
                    $('#wsStatus').html('connected');
                },

                OnWelcome: function() {
//                     getChannels();
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
                    log( "jWebSocket connection closed." );
                    $('#wsStatus').html('connection closed');
                }

            });
        } catch( ex ) {
            log( "Exception: " + ex.message );
        }
    }

    // log out the client from the jWebSocket server
    function logout() {
        if( lWSC ) {
            lWSC.stopKeepAlive();
            log( "Disconnecting..." );
            var lRes = lWSC.close({ timeout: 3000 });
            log( lWSC.resultToString( lRes ) );
        }
    }

    // try to create a new channel on the server
    // on success the OnChannelCreated event is fired
//    function createChannel() {
//        var lChannelId = '001';
//        var lChannelName = 'game1';
//        var lIsPrivate = true;
//        var lIsSystem = false;
//        var lAccessKey = 'akey';
//        var lSecretKey = 'skey';
//        log( "Creating channel '" + lChannelId + "'..." );
//        var lRes = lWSC.channelCreate(
//            lChannelId,
//            lChannelName,
//            {   isPrivate: lIsPrivate,
//                isSystem: lIsSystem,
//                accessKey: lAccessKey,
//                secretKey: lSecretKey
//            }
//        );
//        log( lWSC.resultToString( lRes ) );
//    }

    // try to subscribe at a certain channel
    function subscribeChannel() {
        var lAccessKey = 'access';
        var lRes = lWSC.channelSubscribe( channel, lAccessKey );
        log( lWSC.resultToString( lRes ) );
    }

    // try to authenticate against a channel to publish data
    function auth() {
        var lAccessKey = 'access';
        var lSecretKey = 'secret';
        log( "Authenticating against channel '" + channel + "'..." );
        // use access key and secret key for this channel to authenticate
        // required to publish data only
        var lRes = lWSC.channelAuth( channel, lAccessKey, lSecretKey );
        log( lWSC.resultToString( lRes ) );
    }

    function initWS() {
        eLog = $( "#log" );

        if( window.WebSocket ) {
            lWSC = new jws.jWebSocketJSONClient();
            login();
        } else {
            var lMsg = jws.MSG_WS_NOT_SUPPORTED;
            alert( lMsg );
            log( lMsg );
        }
        lWSC.startKeepAlive({ interval: 3000 });
    }

    function exitPage() {
        lWSC.stopKeepAlive();
        logout();
    }
