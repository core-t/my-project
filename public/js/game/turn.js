// *** TURN ***

var Turn = {
    number: null,
    color: null,
    init: function () {
        for (i in turnHistory) {
            timer.append(turnHistory[i].shortName, turnHistory[i].number, turnHistory[i].date.substr(0, 19))
        }
        timer.scroll()
        this.number = turnHistory[i].number;
        this.color = turnHistory[i].shortName;
    },
    on: function () {
        makeMyCursorUnlock();
        Army.skippedArmies = {};
        my.turn = true;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        Castle.showFirst();
        Message.turn();
        titleBlink('Your turn!');
        if (!Hero.findMy()) {
            $('#heroResurrection').removeClass('buttonOff')
        }
    },
    off: function () {
        my.turn = false;
        Army.deselect();
        $('#nextTurn').addClass('buttonOff');
        $('#nextArmy').addClass('buttonOff');
        makeMyCursorLock();
    },
    change: function (color, nr) {
        if (!color) {
            console.log('Turn "color" not set');
            return;
        }

        Turn.color = color;

        if (isSet(nr)) {
            Turn.number = nr;
        }

        Players.turn();

        timer.update();

        if (Turn.color == my.color) {
            Turn.on();
            Websocket.startMyTurn();
            return;
        } else {
            Turn.off();
            return;
        }
    }
}
