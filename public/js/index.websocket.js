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

