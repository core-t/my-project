function chat(color,msg,time){
    var chatWindow = $('#chatWindow div').append('<br/>').append(color+' ('+time+'): '+msg);
    var scroll = 120 - chatWindow[0].scrollHeight;
    chatWindow.animate({
        'top':scroll
    },100);
    $('#msg').focus();
}

function renderChatHistory(){
    for(i in chatHistory){
        var chatWindow = $('#chatWindow div').append('<br/>').append(chatHistory[i]['color']+' ('+getISODateTime(chatHistory[i]['date'])+'): '+chatHistory[i]['message']);
        var scroll = 120 - chatWindow[0].scrollHeight;
        chatWindow.animate({
            'top':scroll
        },100);
    }
}