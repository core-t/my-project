function chat(color, msg, time) {
    var chatWindow = $('#chatWindow div').append('<br/>').append('<span style="color:' + color + '">' + color + ' (' + time + '): </span>' + msg);
    var scroll = 120 - chatWindow[0].scrollHeight;
    chatWindow.animate({
        'top': scroll
    }, 100);
    $('#msg').focus();
}

function renderChatHistory() {
    var chatWindow;
    var scroll;
    for (i in chatHistory) {
        var chatWindow = $('#chatWindow div').append('<br/>').append('<span style="color:' + chatHistory[i]['color'] + '">' + chatHistory[i]['color'] + ' (' + getISODateTime(chatHistory[i]['date']) + '): </span>' + chatHistory[i]['message']);
        var scroll = 120 - chatWindow[0].scrollHeight;
        chatWindow.css({'top': scroll});
    }

}