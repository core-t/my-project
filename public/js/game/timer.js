var timer = {
    timestamp: 0,
    start: function () {
        this.timestamp = Date.parse(gameStart).getTime()
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

            $('#timerBox #' + Turn.color + Turn.number + ' #hour').html(hours);
            $('#timerBox #' + Turn.color + Turn.number + ' #minute').html(minutes);
            $('#timerBox #' + Turn.color + Turn.number + ' #second').html(seconds);
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
        this.append(Turn.color, Turn.number);
        $('#timerScroll').animate({ scrollTop: $('#timerRows .row').length * 30 }, 1000);
    },
    append: function (color, number, date) {
        var difference = 0;
        if (isSet(date)) {
            var timestamp = Date.parse(date).getTime()
            difference = timestamp - this.timestamp - 3600000
            this.timestamp = timestamp

            var time = new Date(difference),
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
        }

        $('#timerRows')
            .append($('<div class="row">')
                .append($('<div class="left color">').html($('<img>').attr('src', Hero.getImage(color))))
                .append($('<div class="left nr">').html(number))
                .append(
                    $('<div class="left time" id="' + color + number + '">')
                        .append($('<div>').attr('id', 'second').html(seconds))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'minute').html(minutes))
                        .append($('<div>').html(':'))
                        .append($('<div>').attr('id', 'hour').html(hours))
                )
            );
    }
}
