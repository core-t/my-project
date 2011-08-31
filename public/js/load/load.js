var lWSC = null;

function auth() {
    var lAccessKey = 'access';
    var lSecretKey = 'secret';
    var lRes = lWSC.channelAuth( channel, lAccessKey, lSecretKey );
}
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
                    if(color == data[0]){
                        return 0;
                    }
                    if(data[1] == 'p'){
                        update(data[0]);
                    }
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
function update(color){
    $.getJSON(urlUpdate+'/color/'+color, function(result) {
        var playersReady = 0;
        for(i in result) {
            if(result[i].ready) {
                playersReady++;
            }
            $('#'+result[i].color+'Id div.left').html(result[i].firstName+' '+result[i].lastName);
        }
        if(alivePlayers['length'] <= playersReady) {
            top.location = urlRedirect;
        }
    });
}
function refresh() {
    $.getJSON(urlRefresh, function(result) {
        if(lWSC.isOpened()){
            auth();
        }else{
            login();
        }
        var playersReady = 0;
        for(i in result) {
            if(result[i].ready) {
                playersReady++;
            }
            $('#'+result[i].color+'Id div.left').html(result[i].firstName+' '+result[i].lastName);
        }
        if(alivePlayers['length'] <= playersReady) {
            top.location = urlRedirect;
        }
    });
}
function init(){
    refresh();
}
$(document).ready(function() {
    lWSC = new jws.jWebSocketJSONClient();
    login();
    setTimeout('init()', 1500);
    setInterval ( 'refresh()', 5000 );
});

