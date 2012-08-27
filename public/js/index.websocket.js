function login() {
    var lURL = jws.getServerURL(aSchema, aHost, aPort, aContext, aServlet);

    try {
        return lWSC.logon( lURL, "guest", "guest", {

            // OnOpen callback
            OnOpen: function( aEvent ) {
                $('#wsStatus').html('connecting...');
            },

            OnWelcome: function() {
            },

            // OnMessage callback
            OnMessage: function( aEvent, aToken ) {
                if( lWSC.isLoggedIn() ) {
                    if(!channelAuthorized){
                        channelAuthResponse = lWSC.channelAuth( channel, lAccessKey, lSecretKey);
                        if(channelAuthResponse.msg == 'Ok'){
                            channelAuthorized = true;
                            $('#wsStatus').html('authorized');
                        } else {
                            $('#wsStatus').html('authenticated');
                        }
                    }
                    if(channelAuthorized && !channelSubscribed){
                        channelSubscribed = lWSC.channelSubscribe( channel, lAccessKey);
                    }
                } else {
                    $('#wsStatus').html('connected');
                }
            },

            // OnClose callback
            OnClose: function( aEvent ) {
                $('#wsStatus').html('connection closed');
            }

        });
    } catch( ex ) {
        console.log( "Exception: " + ex.message );
    }
}

function loginTest() {
    var lURL = jws.getDefaultServerURL();

    try {
        var lAccessKey = 'access';
        var channel = 'ch230';
        return lWSC.logon( lURL, 'root', 'zaqwsx', {

            // OnOpen callback
            OnOpen: function( aEvent ) {
                $('#wsStatus').html('connected');
            },

            OnWelcome: function() {
                console.log('Welcome');
            },

            // OnMessage callback
            OnMessage: function( aEvent, aToken ) {
                if( lWSC.isLoggedIn() ) {
                    if(!channelCreated){
                        channelCreated = lWSC.channelCreate(channel, channel, {
                            isPrivate: true,
                            isSystem: false,
                            accessKey: 'access',
                            secretKey: 'secret'
                        });
                    }
                    if(!channelAuthorized){
                        channelAuthResponse = lWSC.channelAuth( channel, lAccessKey, 'secret');
                        if(channelAuthResponse.msg == 'Ok'){
                            channelAuthorized = true;
                            $('#wsStatus').html('authorized');
                        } else {
                            $('#wsStatus').html('authenticated');
                        }
                    }
                    if(channelCreated && channelAuthorized && !channelSubscribed){
                        channelSubscribed = lWSC.channelSubscribe( channel, lAccessKey);
                    }
                } else {
                    $('#wsStatus').html('connected');
                }
                console.log(aEvent);
                console.log(aToken);
            },

            // OnClose callback
            OnClose: function( aEvent ) {
                channelAuthorized = null;
                $('#wsStatus').html('connection closed');
            }

        });
    } catch( ex ) {
        console.log( "Exception: " + ex.message );
    }
}

