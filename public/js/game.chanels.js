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
//                    console.log(" Client-Id: " + lWSC.getId() + " "+ ( jws.browserSupportsNativeWebSockets ? "(native)" : "(flashbridge)" ));
                    if(typeof aToken.event != 'undefined'){
                        var data = aToken.data;
                        switch(aToken.event){
                            case 'move':
                                changeArmyPosition(data.x, data.y, data.armyId, turn.color);
                                break;
                            case 'add':
                                getAddArmy(data.armyId);
                                break;
                            case 'turn':
                                changeTurn(data.playerId, data.color);
                                break;
                            case 'delete':
                                deleteArmy('army'+data.armyId, data.color);
                                break;
                            case 'castle':
                                castleGet(data.castleId);
                                break;
                            case 'armies':
                                getPlayerArmies(data.color);
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
        var lChannel = 'publicA';
        var lAccessKey = 'access';
        log( "Subscribing at channel '" + lChannel + "'..." );
        var lRes = lWSC.channelSubscribe( lChannel, lAccessKey );
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

    // try to publish data on a certain channel
    function publish(data) {
        log( "Publishing to channel '" + channel + "'..." );
        var lRes = lWSC.channelPublish( channel, data );
        log( lWSC.resultToString( lRes ) );
    }

    // try to obtain all available channels on the server
    function getChannels() {
        log( "Trying to obtain channels..." );
        var lRes = lWSC.channelGetIds();
        log( lWSC.resultToString( lRes ) );
    }

    // try to obtain all subscribers for a certain channel
    function getSubscribers() {
        var lAccessKey = 'aaa';
        log( "Trying to obtain subscribers for channel '" + channel + "'..." );
        var lRes = lWSC.channelGetSubscribers(channel, lAccessKey);
        log( lWSC.resultToString( lRes ) );
    }

    // try to obtain all channels the client has subscribed to
    function getSubscriptions() {
        log( "Trying to obtain subscriptions for client..." );
        var lRes = lWSC.channelGetSubscriptions();
        log( lWSC.resultToString( lRes ) );
    }

    function toggleKeepAlive() {
        if( eKeepAlive.checked ) {
            lWSC.startKeepAlive({ interval: 3000 });
        } else {
            lWSC.stopKeepAlive();
        }
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
