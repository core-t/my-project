var timer = {
    timeStart: 0,
    timestamp: 0,
    start: function () {
        timer.timeStart = Math.round((new Date()).getTime() / 1000);
        $('#timerRows')
            .append($('<div class="row">')
                .append($('<div class="left color">').html($('<img>').attr('src', Hero.getImage(turn.color))))
                .append($('<div class="left nr">').html(turn.nr))
                .append(
                    $('<div class="left time" id="time">')
                        .append($('<div>').attr('id', 'second'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'minute'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'hour'))
                )
            );
        $('#timerScroll').css('height', Players.length * 30 + 'px');
        timer.countdown();
    },
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
        $('#timerRows')
            .append($('<div class="row">')
                .append($('<div class="left color">').html($('<img>').attr('src', Hero.getImage(turn.color))))
                .append($('<div class="left nr">').html(turn.nr))
                .append(
                    $('<div class="left time" id="time">')
                        .append($('<div>').attr('id', 'second'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'minute'))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'hour'))
                )
            );
        $('#timerScroll').animate({ scrollTop: $('#timerRows .row').length * 30 }, 1000);
    }
}
