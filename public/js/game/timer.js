var timer = {
    timeStart: 0,
    timestamp: 0,
    start: function () {
        timer.timeStart = Math.round((new Date()).getTime() / 1000);
//        timer.clear();
        $('#timerBox table')
            .append($('<tr>')
                .append($('<td>').html(turn.color))
                .append($('<td>').html(turn.nr))
                .append(
                    $('<td id="time">')
                        .append($('<div>').attr('id', 'second'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'minute'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'hour'))
                )
            )
        timer.countdown();
    },
//    clear: function () {
//        var count = $('#timerBox table').children().length;
//        if (count > 1) {
//            $('#timerBox table').last().remove();
//        }
//    },
    countdown: function () {
        var timestamp = Math.round((new Date()).getTime() / 1000) - timer.timeStart;

        if (timer.timestamp != timestamp) {
            timer.timestamp = timestamp;

            var seconds = Math.round(timestamp % 60);
            var minutes = Math.round(timestamp / 60);
            var hours = Math.round(minutes / 60);


            if (seconds < 10) {
                seconds = '0' + seconds;
            }

            if (minutes < 10) {
                minutes = '0' + minutes;
            }

            if (hours < 10) {
                hours = '0' + hours;
            }

            $('#timerBox #time #hour').html(hours);
            $('#timerBox #time #minute').html(minutes);
            $('#timerBox #time #second').html(seconds);
        }

        setTimeout(function () {
            timer.countdown()
        }, 10);
    },
    update: function () {
        timer.timeStart = Math.round((new Date()).getTime() / 1000);
        $('#timerBox #second').attr('id', 'second' + $('#timerBox #second').html())
        $('#timerBox #minute').attr('id', 'minute' + $('#timerBox #minute').html())
        $('#timerBox #hour').attr('id', 'hour' + $('#timerBox #hour').html())
        $('#timerBox table')
            .append($('<tr>')
                .append($('<td>').html($('<img>').attr('src', Hero.getImage(turn.color))))
                .append($('<td>').html(turn.nr))
                .append(
                    $('<td id="time">')
                        .append($('<div>').attr('id', 'second'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'minute'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'hour'))
                )
            )

        var count = $('#timerBox tr').length - 1;

        if (count > Players.length) {
            var i = 0;
            $('#timerBox tr').each(function () {
                i++;
                if (i == 2) {
                    this.remove();
                }
            });
        }
    }
}
