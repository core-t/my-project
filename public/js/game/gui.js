var Gui = {
    armyBox: {'close': 0},
    chatBox: {'close': 0},
    playerBox: {'close': 0},
    timerBox: {'close': 0},
    zoomPadLayout: {'close': 0},
    doKey: function (event) {
        if ($(event.target).attr('id') == 'msg') {
            return;
        }
        var key = event.keyCode || event.charCode;
        switch (key) {
            case 27: //ESC
                Message.remove();
                unselectArmy();
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
                Websocket.searchRuins()
                break;
            case 83: //s
                skipArmy();
                break;
//            default:
//                console.log(key);
        }
    },
    prepareButtons: function () {
        zoomPad = $('.zoomPad');
        board = $('#board');
        coord = $('#coord');

        $('#exit').click(function () {
            window.location = '/' + lang + '/index';
        });

        $('#show').click(function () {
            show = !show;
            if (show) {
                $(this).css('background', 'url(\'/img/game/show.png\') no-repeat center center')
            } else {
                $(this).css('background', 'url(\'/img/game/show_off.png\') no-repeat center center')
            }
        });

        $('#send').click(function () {
            Websocket.chat();
        });
        $('#msg').keypress(function (e) {
            if (e.which == 13) {
                Websocket.chat();
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
            Websocket.searchRuins()
        });
        $('#razeCastle').click(function () {
            Message.razeCastle();
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

        $('.zoomPadLayout .close').click(function () {
            var left = parseInt($('.zoomPadLayout').css('left'));
            var move = -220;

            if (Gui.zoomPadLayout['close']) {
                move = -move;
            }

            $('.zoomPadLayout').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.zoomPadLayout['close'] = !Gui.zoomPadLayout['close'];
            });
        });
        $('#timerBox .close').click(function () {
            var left = parseInt($('#timerBox').css('left'));
            var move = -220;

            if (Gui.timerBox['close']) {
                move = -move;
            }

            $('#timerBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.timerBox['close'] = !Gui.timerBox['close'];
            });
        });
        $('#playersBox .close').click(function () {
            var left = parseInt($('#playersBox').css('left'));
            var move = 220;

            if (Gui.playerBox['close']) {
                move = -move;
            }
            $('#playersBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.playerBox['close'] = !Gui.playerBox['close'];
            });
        });
        $('#chatBox .close').click(function () {
            var left = parseInt($('#chatBox').css('left'));
            var move = 490;

            if (Gui.chatBox['close']) {
                move = -move;
            }
            $('#chatBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.chatBox['close'] = !Gui.chatBox['close'];
            });
        });
        $('#armyBox .close').click(function () {
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
        this.armyBox.close = 0;
        this.chatBox.close = 0;
        this.playerBox.close = 0;
        this.timerBox.close = 0;
        this.zoomPadLayout.close = 0;
        documentWidth = $(document).width();
        documentHeight = $(window).height();//$(document).height();

        $('.zoomWindow').css('height', documentHeight + 'px');

        var left = documentWidth - 237;
        var chatLeft = documentWidth - 507;
        var chatTop = documentHeight - 169;

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

        var zoomPadLayoutHeight = parseInt($('#map').css('height'));

        $('.zoomPadLayout').css({
            width: parseInt($('#map').css('width')) + 20 + 'px',
            height: zoomPadLayoutHeight + 40 + 'px'
        });

        $('#terrain').css('top', zoomPadLayoutHeight + 19 + 'px');

        $('.zoomPadLayout .close').css({
            left: parseInt($('#map').css('width')) + 16 + 'px'
        });

        $('#timerBox').css({
            top: zoomPadLayoutHeight + 52 + 'px'
        });

        if (!zoomer) {
            zoomer = new zoom(documentWidth, documentHeight);
        } else {
            zoomer.setSettings(parseInt($('.zoomWindow').css('width')), parseInt($('.zoomWindow').css('height')));
            zoomer.lens.setdimensions();
        }
    }

}