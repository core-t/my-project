var timer = {
    timeStart: 0,
    timestamp: 0,
    start: function () {
        this.setTimeStart();
        $('#timerRows')
            .append($('<div class="row">')
                .append($('<div class="left color">').html($('<img>').attr('src', Hero.getImage(Turn.shortName))))
                .append($('<div class="left nr">').html(Turn.number))
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
        var timestamp = (new Date()).getTime() - timer.timeStart;

        if (timer.timestamp != timestamp) {
            timer.timestamp = timestamp;

            var time = new Date(timestamp),
                hours = time.getHours(),
                minutes = time.getMinutes(),
                seconds = time.getSeconds();


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
        this.setTimeStart();
        $('#timerBox #second').attr('id', 'second' + $('#timerBox #second').html())
        $('#timerBox #minute').attr('id', 'minute' + $('#timerBox #minute').html())
        $('#timerBox #hour').attr('id', 'hour' + $('#timerBox #hour').html())
        $('#timerRows')
            .append($('<div class="row">')
                .append($('<div class="left color">').html($('<img>').attr('src', Hero.getImage(Turn.shortName))))
                .append($('<div class="left nr">').html(Turn.number))
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
    },
    setTimeStart: function () {
        this.timeStart = (new Date()).getTime() + 3600000;
    }
}
