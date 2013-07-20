var mygames;

function refresh() {
    $.getJSON("/' + lang + '/newajax/refresh", function (result) {

        mygames.html('');
        mygames.append(th);
        for (i in result) {
            mygames.append(
                $('<tr>')
                    .addClass('gid' + result[i].gameId)
                    .append($('<td>').append($('<a>').html(result[i].gameMaster)).css('cursor', 'pointer'))
                    .append($('<td>').append($('<a>').html(result[i].playersingame)).css('cursor', 'pointer'))
                    .append($('<td>').append($('<a>').html(result[i].numberOfPlayers)).css('cursor', 'pointer'))
                    .append($('<td>').append($('<a>').html(result[i].begin.split('.')[0])).css('cursor', 'pointer'))
                    .bind('click', { gameId: result[i].gameId }, makeUrl)
                    .mouseover(function () {
                        $(this).css('background', 'transparent url(../img/nav_bg.png) repeat')
                    })
                    .mouseleave(function () {
                        $(this).css('background', 'transparent')
                    })
            );
            $('#mygames td').mouseover(function () {
                $('#mygames td').css('cursor', 'pointer')
            });
        }
    });
}
function makeUrl(event) {
    top.location.replace('/' + lang + '/gamesetup/index/gameId/' + event.data.gameId);
}

$().ready(function () {
    mygames = $('.table table');

    refresh();
    setInterval('refresh()', 5000);
});
