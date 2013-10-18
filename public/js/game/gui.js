var Gui = {
    armyBox: {'close': 0},
    chatBox: {'close': 0},
    playerBox: {'close': 0},
    timerBox: {'close': 0},
    doKey: function (event) {
        var key = event.keyCode || event.charCode;
        switch (key) {
            case 27: //ESC
                Message.remove();
                break;
            case 68: //d
                Message.disbandArmy();
                break;
            case 69: //t
                Message.nextTurn();
                break;
            case 70: //f
                fortifyArmy();
                break;
            case 78: //n
                findNextArmy();
                break;
            case 82: //r
                wsSearchRuins();
                break;
            case 83: //s
                skipArmy();
                break;
            default:
                console.log(key);
        }
    },
    prepareButtons: function () {
        zoomPad = $(".zoomPad");
        board = $("#board");

        $('#exit').click(function () {
            window.location = '/' + lang + '/index';
        });

        $('#send').click(function () {
            wsChat();
        });
        $('#msg').keypress(function (e) {
            if (e.which == 13) {
                wsChat();
            }
        });
        $('#nextTurn').click(function () {
            Message.nextTurn();
        });
        $('#surrender').click(function () {
            Message.surrender()
        });
        $('#nextArmy').click(function () {
            findNextArmy()
        });
        $('#skipArmy').click(function () {
            skipArmy()
        });
        $('#quitArmy').click(function () {
            fortifyArmy()
        });
        $('#splitArmy').click(function () {
            if (selectedArmy) {
                Message.splitArmy()
            }
        });
        $('#armyStatus').click(function () {
            if (selectedArmy) {
                Message.armyStatus()
            }
        });
        $('#disbandArmy').click(function () {
            Message.disbandArmy()
        });
        $('#unselectArmy').click(function () {
            if (selectedArmy) {
                unselectArmy();
            }
        });
        $('#searchRuins').click(function () {
            wsSearchRuins()
        });
        $('#showArtifacts').click(function () {
            Message.showArtifacts();
        });
        $('#test').click(function () {
            test()
        });
        $('#nextTurn').addClass('buttonOff');
        $('#nextArmy').addClass('buttonOff');
        $('#skipArmy').addClass('buttonOff');
        $('#quitArmy').addClass('buttonOff');
        $('#splitArmy').addClass('buttonOff');
        $('#disbandArmy').addClass('buttonOff');
        $('#searchRuins').addClass('buttonOff');

        $('#timerBox #close').click(function () {
            var left = parseInt($('#timerBox').css('left'));
            var move = -220;

            if (Gui.timerBox['close']) {
                move = -move;
            }

            $('#timerBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.timerBox['close'] = !Gui.timerBox['close'];
            });
        });
        $('#playersBox #close').click(function () {
            var left = parseInt($('#playersBox').css('left'));
            var move = 220;

            if (Gui.playerBox['close']) {
                move = -move;
            }
            $('#playersBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.playerBox['close'] = !Gui.playerBox['close'];
            });
        });
        $('#chatBox #close').click(function () {
            var left = parseInt($('#chatBox').css('left'));
            var move = 490;

            if (Gui.chatBox['close']) {
                move = -move;
            }
            $('#chatBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.chatBox['close'] = !Gui.chatBox['close'];
            });
        });
        $('#armyBox #close').click(function () {
            var left = parseInt($('#armyBox').css('left'));
            var move = 220;

            if (Gui.armyBox['close']) {
                move = -move;
            }
            $('#armyBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.armyBox['close'] = !Gui.armyBox['close'];
            });
        });
    },
    adjust: function () {
        documentWidth = $(document).width();
        documentHeigh = $(document).height();
        $('.zoomWindow').css('height', documentHeigh + 'px');

        var left = documentWidth - 237;
        var chatLeft = documentWidth - 507;
        var chatTop = documentHeigh - 169;

//        console.log(parseInt($('#chatBox').css('height')));
//        console.log(parseInt($('#chatBox').css('height')) + parseInt($('#playersBox').css('height')) + parseInt($('#armyBox').css('height')));
//        console.log(documentHeigh);
//        console.log(chatTop);
//        if (documentHeigh < parseInt($('#chatBox').css('height')) + parseInt($('#playersBox').css('height')) + parseInt($('#armyBox').css('height')) + 100) {
//            chatLeft = chatLeft - parseInt($('#playersBox').css('width')) - 50;
//            chatTop = documentHeigh - parseInt($('#chatBox').css('height')) - 200;
//        }


        $('#chatBox').css({
            'left': chatLeft + 'px',
            'top': chatTop + 'px'
        });

        $('#goldBox').css({
            'left': documentWidth / 2 - parseInt($('#goldBox').css('width')) / 2 + 'px'
        });
        $('#playersBox').css({
            'left': left + 'px'
        });
        $('#armyBox').css({
            'left': left + 'px'
        });
//    $('#timerBox').css({
//        'left': left + 'px'
//    });

        var zoomPadLayoutHeight = parseInt($('#map').css('height'));

        $('.zoomPadLayout').css({
            width: parseInt($('#map').css('width')) + 20 + 'px',
            height: zoomPadLayoutHeight + 40 + 'px'
        });

        $('#terrain').css('top', zoomPadLayoutHeight + 5 + 'px');

        if (!zoomer) {
            zoomer = new zoom(documentWidth, documentHeigh);
        } else {
            zoomer.setSettings(parseInt($('.zoomWindow').css('width')), parseInt($('.zoomWindow').css('height')));
            zoomer.lens.setdimensions();
        }
    }

}