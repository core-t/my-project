var timer = {
    height: 32,
    timestamp: 0,
    difference: 0,
    start: function () {
        this.timestamp = Date.parse(gameStart).getTime()
        $('#timerScroll').css('height', Players.length * this.height + 'px');
        timer.countdown();
    },
    countdown: function () {
        var difference = (new Date()).getTime() - this.timestamp;

        if (this.difference != difference) {
            this.difference = difference;

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

            $('#timerBox #' + Turn.color + Turn.number + ' #hour').html(hours);
            $('#timerBox #' + Turn.color + Turn.number + ' #minute').html(minutes);
            $('#timerBox #' + Turn.color + Turn.number + ' #second').html(seconds);
        }

        setTimeout(function () {
            timer.countdown()
        }, 10);
    },
    update: function () {
        this.timestamp = (new Date()).getTime();
//        $('#timerBox #second').attr('id', 'second' + $('#timerBox #second').html())
//        $('#timerBox #minute').attr('id', 'minute' + $('#timerBox #minute').html())
//        $('#timerBox #hour').attr('id', 'hour' + $('#timerBox #hour').html())
        this.append(Turn.color, Turn.number)
        this.scroll()
    },
    append: function (color, number, date) {
        var difference = 0;
        var hours = 0,
            minutes = 0,
            seconds = 0;

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
    },
    scroll: function () {
        $('#timerScroll').animate({ scrollTop: $('#timerRows .row').length * this.height }, 1000)
    }
}
