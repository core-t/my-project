var Gui = {
    lock: true,
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
                Army.deselect();
                break;
            case 67: //c
                Castle.show();
                break;
            case 68: //d
                Message.disband();
                break;
            case 69: //t
                Message.nextTurn();
                break;
            case 70: //f
                Army.fortify();
                break;
            case 78: //n
                Army.findNext();
                break;
            case 82: //r
                Websocket.ruin()
                break;
            case 83: //s
                Army.skip();
                break;
//            default:
//                console.log(key);
        }
    },
    prepareButtons: function () {
        zoomPad = $('.zoomPad');
        board = $('#board');
        coord = $('#coord');

        $('#gold').click(function () {
            Message.treasury()
        })

        $('#income').click(function () {
            Message.income()
        })

        $('#costs').click(function () {
            Message.upkeep()
        })

        $('#exit').click(function () {
            window.location = '/' + lang + '/index';
        });

        $('#show').click(function () {
            Sound.play('click');
            show = !show;
            if (show) {
                $(this).children().attr('src', '/img/game/show.png')
            } else {
                $(this).children().attr('src', '/img/game/show_off.png')
            }
        });

        $('#sound').click(function () {
            Sound.play('click');
            Sound.mute = !Sound.mute;
            if (Sound.mute) {
                $(this).children().attr('src', '/img/game/sound_off.png')
            } else {
                $(this).children().attr('src', '/img/game/sound_on.png')
            }
        });

        $('#surrender').click(function () {
            Message.surrender()
        });

        $('#statistics').click(function () {
            Websocket.statistics();
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

        $('#nextArmy').click(function () {
            Army.findNext();
        })
        ;
        $('#skipArmy').click(function () {
            Army.skip();
        });

        $('#quitArmy').click(function () {
            Army.fortify();
        });

        $('#splitArmy').click(function () {
            if (!Army.selected) {
                return;
            }

            Message.split();
        });

        $('#armyStatus').click(function () {
            if (!Army.selected) {
                return;
            }

            Message.armyStatus();
        });

        $('#disbandArmy').click(function () {
            Message.disband();
        });

        $('#deselectArmy').click(function () {
            if (!Army.selected) {
                return;
            }

            Army.deselect();
        });

        $('#searchRuins').click(function () {
            Websocket.ruin()
        });

        $('#heroResurrection').click(function () {
            Websocket.resurrection()
        });

        $('#razeCastle').click(function () {
            Message.raze();
        });

        $('#buildCastleDefense').click(function () {
            Message.build();
        });

        $('#showCastle').click(function () {
            Castle.show();
        });

        $('#showArtifacts').click(function () {
            Message.showArtifacts();
        });

        $('.zoomPadLayout .close').click(function () {
            var left = parseInt($('.zoomPadLayout').css('left'));
            var move = -220;
            Gui.zoomPadLayout['el'] = this;

            if (Gui.zoomPadLayout['close']) {
                move = -move;
            }

            Gui.zoomPadLayout['move'] = move;

            $('.zoomPadLayout').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.zoomPadLayout['close'] = !Gui.zoomPadLayout['close'];
                Gui.changeCloseArrowLR(Gui.zoomPadLayout['move'], Gui.zoomPadLayout['el']);
            });
        });
        $('#timerBox .close').click(function () {
            var left = parseInt($('#timerBox').css('left'));
            var move = -220;
            Gui.timerBox['el'] = this;

            if (Gui.timerBox['close']) {
                move = -move;
            }

            Gui.timerBox['move'] = move;

            $('#timerBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.timerBox['close'] = !Gui.timerBox['close'];
                Gui.changeCloseArrowLR(Gui.timerBox['move'], Gui.timerBox['el']);
            });
        });
        $('#playersBox .close').click(function () {
            var left = parseInt($('#playersBox').css('left'));
            var move = 220;
            Gui.playerBox['el'] = this;

            if (Gui.playerBox['close']) {
                move = -move;
            }

            Gui.playerBox['move'] = move;

            $('#playersBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.playerBox['close'] = !Gui.playerBox['close'];
                Gui.changeCloseArrowLR(Gui.playerBox['move'], Gui.playerBox['el']);
            });
        });
        $('#chatBox .close').click(function () {
            var left = parseInt($('#chatBox').css('left'));
            var move = 490;
            Gui.chatBox['el'] = this;

            if (Gui.chatBox['close']) {
                move = -move;
            }

            Gui.chatBox['move'] = move;

            $('#chatBox').animate({'left': left + move + 'px'}, 1000, function () {
                Gui.chatBox['close'] = !Gui.chatBox['close'];
                Gui.changeCloseArrowLR(Gui.chatBox['move'], Gui.chatBox['el']);
            });
        });
        $('#armyBox .close').click(function () {
            var left = parseInt($('#armyBox').css('left'));
            var move = 220;
            Gui.armyBox['el'] = this;

            if (Gui.armyBox['close']) {
                move = -move;
            }

            Gui.armyBox['move'] = move;

            $('#armyBox').animate({'left': left + Gui.armyBox['move'] + 'px'}, 1000, function () {
                Gui.armyBox['close'] = !Gui.armyBox['close'];
                Gui.changeCloseArrowLR(Gui.armyBox['move'], Gui.armyBox['el']);
            });
        });
    },
    changeCloseArrowLR: function (move, el) {
        if (move > 0) {
            $(el).html('&#x25C0');
        } else {
            $(el).html('&#x25B6');
        }
    },
    changeCloseArrowUD: function (move, el) {
        if (move > 0) {
            $(el).html('&#x25C1');
        } else {
            $(el).html('&#x25B7');
        }
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
            'left': documentWidth / 2 - $('#goldBox').outerWidth() / 2 + 'px'
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

        $('.message').css({
                'left': documentWidth / 2 - $('.message').outerWidth() / 2 + 'px'
            }
        )
        ;

        if (!zoomer) {
            zoomer = new zoom(documentWidth, documentHeight);
        } else {
            zoomer.setSettings(parseInt($('.zoomWindow').css('width')), parseInt($('.zoomWindow').css('height')));
            zoomer.lens.setdimensions();
        }
    }

}