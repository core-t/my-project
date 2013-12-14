var myGames;

$().ready(function () {
    myGames = $('.table table');

    refresh();
    setInterval('refresh()', 5000);

    changeMap()

    $('#mapId').change(function () {
        changeMap();
        getNumberOfPlayersForm();
    });
});

function changeMap() {
    $('#map').attr('src', '/img/maps/' + $('#mapId').children(':selected').attr('value') + '.png');
}

function refresh() {
    $.getJSON("/' + lang + '/newajax/refresh", function (result) {

        myGames.html('');
        myGames.append(th);

        var j = 0;

        for (i in result) {
            j++;
            myGames.append(
                $('<tr>')
                    .addClass('gid' + result[i].gameId)
                    .append($('<td>').append($('<a>').html(result[i].name)).css('cursor', 'pointer'))
//                    .append($('<td>').append($('<a>').html(result[i].gameMaster)).css('cursor', 'pointer'))
//                    .append($('<td>').append($('<a>').html(result[i].playersingame)).css('cursor', 'pointer'))
                    .append($('<td>').append($('<a>').html(result[i].playersingame + '/' + result[i].numberOfPlayers)).css('cursor', 'pointer'))
                    .append($('<td>').append($('<a>').html(result[i].begin.split('.')[0])).css('cursor', 'pointer'))
                    .bind('click', { gameId: result[i].gameId }, makeUrl)
                    .mouseover(function () {
                        $(this).css('background', 'transparent url(/img/nav_bg.png) repeat')
                    })
                    .mouseleave(function () {
                        $(this).css('background', 'transparent')
                    })
            );
            $('#mygames td').mouseover(function () {
                $('#mygames td').css('cursor', 'pointer')
            });
        }
        if (j == 0) {
            $('#info').html(info);
        }
    });
}
function makeUrl(event) {
    top.location.replace('/' + lang + '/setup/index/gameId/' + event.data.gameId);
}

function getNumberOfPlayersForm() {
    var mapId = $('#mapId').val();
    $.getJSON('/' + lang + '/newajax/nop/mapId/' + mapId, function (result) {
        var html = $.parseHTML(result);
        $('#numberOfPlayers').html($(html[0][0]).html());
    });
}
