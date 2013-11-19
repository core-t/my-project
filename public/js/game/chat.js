function chat(color, msg, time) {
    if (color != my.color) {
        titleBlink('Incoming chat!');
    }

    var chatWindow = $('#chatWindow div').append('<br/>').append('<span style="color:' + mapPlayersColors[color].textColor + ';background:' + mapPlayersColors[color].backgroundColor + '">' + mapPlayersColors[color].longName + ' (' + time + '):</span> ' + msg);

    $('#chatWindow').animate({ scrollTop: $('#chatWindow div')[0].scrollHeight }, 'fast');

    $('#msg').focus();
}

function renderChatHistory() {
    for (i in chatHistory) {
        var chatWindow = $('#chatWindow div').append('<br/>').append('<span style="color:' + mapPlayersColors[chatHistory[i]['color']].textColor + ';background:' + mapPlayersColors[chatHistory[i]['color']].backgroundColor + '">' + mapPlayersColors[chatHistory[i]['color']].longName + ' (' + getISODateTime(chatHistory[i]['date']) + '):</span> ' + chatHistory[i]['message']);
    }
    $('#chatWindow').animate({ scrollTop: $('#chatWindow div')[0].scrollHeight }, 1000);
}