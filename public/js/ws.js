var FancyWebSocket = function(url){
  var conn = new WebSocket(url);

  var callbacks = {};

  this.bind = function(event_name, callback){
    callbacks[event_name] = callbacks[event_name] || [];
    callbacks[event_name].push(callback);
    return this;// chainable
  };

  this.send = function(event_name, event_data){
    var payload = JSON.stringify({event:event_name, data: event_data});
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
      $('#log').html('DISCONNECTED!');
      dispatch('close',null)}
  conn.onopen = function(){
      $('#log').html('CONNECTED');
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
