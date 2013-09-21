// *** PLAYERS ***

var Players = {
    circle_center_x: 110,
    circle_center_y: 110,
    draw: function () {
        var canvas = $('#playersCanvas')[0];
        var ctx = canvas.getContext('2d');

        var r_length = 100;
        var r_angle = Math.PI * 2 / Object.size(players);

        var i = 0;
        for (shortName in players) {
            ctx.beginPath();

            var r_start_angle = i * r_angle;
            var r_end_angle = r_start_angle + r_angle;

            var x = this.circle_center_x + Math.cos(r_start_angle) * r_length;
            var y = this.circle_center_y + Math.sin(r_start_angle) * r_length;

            ctx.moveTo(this.circle_center_x, this.circle_center_y);
            ctx.lineTo(x, y);
            ctx.arc(this.circle_center_x, this.circle_center_y, r_length, r_start_angle, r_end_angle, false);
            ctx.lineTo(this.circle_center_x, this.circle_center_y);
            ctx.fillStyle = players[shortName].backgroundColor;
            ctx.fill();
            i++;
        }


    },
    turn: function () {
        var canvas = $('#playersCanvas')[0];
        var ctx = canvas.getContext('2d');

        ctx.beginPath();
        ctx.arc(this.circle_center_x, this.circle_center_y, 50, 0, Math.PI * 2, true);
        ctx.fillStyle = players[turn.color].backgroundColor;
        ctx.fill();

        $('#turnNumber').css('color', players[turn.color].textColor).html(turn.nr);
    }
}

Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};