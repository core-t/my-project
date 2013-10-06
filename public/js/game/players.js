// *** PLAYERS ***

var Players = {
    circle_center_x: 90,
    circle_center_y: 90,
    canvas: null,
    ctx: null,
    length: 0,
    init: function () {
        this.canvas = $('#playersCanvas');
        this.ctx = this.canvas[0].getContext('2d');
        this.length = Object.size(players);
    },
    draw: function () {
        var r_length = 100;
        var r_angle = Math.PI * 2 / this.length;

        var i = 0;
        for (shortName in players) {
//            $('#playersBox').append($('<img>').attr({
//                    'src': Hero.getImage(shortName),
//                    'id': 'aaa'+shortName
//                }
//            ));


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

//            var img = document.getElementById('aaa'+shortName);
//console.log(img);
//            var img = new Image;
//            if (players[shortName].computer) {
//                img.src = '../img/game/computer.png';
//            } else {
//                img.src = '../' + Hero.getImage(shortName);
//            }
//            img.onload = function () {
//            this.ctx.drawImage(img, 0,0);
//            }
//            img.src = '/img/game/computer.png';
            i++;
//            break;
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