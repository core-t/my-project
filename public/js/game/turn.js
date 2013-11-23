// *** TURN ***

var Turn = {
    number: null,
    color: null,
    init: function () {
        for (i in turnHistory) {

        }

        this.number = turnHistory[i].number;
        this.color = turnHistory[i].shortName;
    },
    on: function () {
        makeMyCursorUnlock();
        Army.skippedArmies = new Array();
        my.turn = true;
        $('#nextTurn').removeClass('buttonOff');
        $('#nextArmy').removeClass('buttonOff');
        Castle.showFirst();
        Message.turn();
        titleBlink('Your turn!');
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
