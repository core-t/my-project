// *** PLAYERS ***

var Players = {
    circle_center_x: 110,
    circle_center_y: 110,
    ctx: null,
    init: function () {
        var canvas = $('#playersCanvas')[0];
        this.ctx = canvas.getContext('2d');
    },
    draw: function () {
        var r_length = 100;
        var r_angle = Math.PI * 2 / Object.size(players);

        var i = 0;
        for (shortName in players) {
            this.ctx.beginPath();

            var r_start_angle = i * r_angle;
            var r_end_angle = r_start_angle + r_angle;

            var x = this.circle_center_x + Math.cos(r_start_angle) * r_length;
            var y = this.circle_center_y + Math.sin(r_start_angle) * r_length;

            this.ctx.moveTo(this.circle_center_x, this.circle_center_y);
            this.ctx.lineTo(x, y);
            this.ctx.arc(this.circle_center_x, this.circle_center_y, r_length, r_start_angle, r_end_angle, false);
            this.ctx.lineTo(this.circle_center_x, this.circle_center_y);
            this.ctx.fillStyle = players[shortName].backgroundColor;
            this.ctx.fill();
            i++;
        }


    },
    turn: function () {
        this.ctx.beginPath();
        this.ctx.arc(this.circle_center_x, this.circle_center_y, 50, 0, Math.PI * 2, true);
        this.ctx.fillStyle = players[turn.color].backgroundColor;
        this.ctx.fill();

        $('#turnNumber').css('color', players[turn.color].textColor).html(turn.nr);
    },
    rotate: function () {
        this.ctx.rotate(20 * Math.PI / 180);
    }
}

Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};