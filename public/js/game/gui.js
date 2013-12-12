var Gui = {
    lock: true,
    armyBox: {'close': 0},
    chatBox: {'close': 0},
    playerBox: {'close': 0},
    timerBox: {'close': 0},
    mapBox: {'close': 0},
    speed: 200,
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
            case 69: //e
                Message.nextTurn();
                break;
            case 70: //f
                Army.fortify();
                break;
            case 78: //n
                Army.findNext();
                break;
            case 79: //o
                $('.message .go').click()
                break;
            case 82: //r
                Websocket.ruin()
                break;
            case 83: //s
                Army.skip();
                break;
//            default:
//                console.log(key)
        }
    },
    prepareButtons: function () {
        map = $('#map');
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
            Message.resurrection()
        })

        $('#heroHire').click(function () {
            Message.hire()
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

        $('#mapBox .close').click(function () {
            var left = parseInt($('#mapBox').css('left'));
            var move = -220;
            Gui.mapBox['el'] = this;

            if (Gui.mapBox['close']) {
                move = -move;
            }

            Gui.mapBox['move'] = move;

            $('#mapBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
                Gui.mapBox['close'] = !Gui.mapBox['close'];
                Gui.changeCloseArrowLR(Gui.mapBox['move'], Gui.mapBox['el']);
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

            $('#timerBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
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

            $('#playersBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
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

            $('#chatBox').animate({'left': left + move + 'px'}, Gui.speed, function () {
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

            $('#armyBox').animate({'left': left + Gui.armyBox['move'] + 'px'}, Gui.speed, function () {
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
        this.mapBox.close = 0;
        gameWidth = $(document).width()
        gameHeight = $(window).height()

        var minWidth = parseInt($('#mapBox').css('width')) + parseInt($('#playersBox').css('width')) + 321
        var minHeight = parseInt($('#playersBox').css('height')) + parseInt($('#armyBox').css('height'))

        if (gameWidth < minWidth) {
            gameWidth = parseInt($('#game').css('min-width'))
        }
        if (gameHeight < minHeight) {
            gameHeight = parseInt($('#game').css('min-height'))
        }

        $('#game').css('height', gameHeight + 'px');

        var numberOfHumanPlayers = 0
        for (sn in players) {
            if (!players[sn].computer) {
                numberOfHumanPlayers++
            }
        }

        if (numberOfHumanPlayers > 1) {
            var chatLeft = gameWidth - 507;
            var chatTop = gameHeight - 169;

            $('#chatBox').css({
                'left': chatLeft + 'px',
                'top': chatTop + 'px'
            });
        } else {
            $('#chatBox').css({display: 'none'});
        }

        $('#goldBox').css({
            'left': gameWidth / 2 - $('#goldBox').outerWidth() / 2 + 'px'
        });

        var left = gameWidth - 237;

        $('#playersBox').css({
            'left': left + 'px'
        });
        $('#armyBox').css({
            top: parseInt($('#playersBox').css('height')) + 19 + 'px',
            'left': left + 'px'
        });

        var mapBoxHeight = parseInt($('#mapImage').css('height'));

        $('#mapBox').css({
            width: parseInt($('#mapImage').css('width')) + 20 + 'px',
            height: mapBoxHeight + 40 + 'px'
        });

        $('#terrain').css('top', mapBoxHeight + 19 + 'px');

        $('#mapBox .close').css({
            left: parseInt($('#mapImage').css('width')) + 16 + 'px'
        });

        $('#timerBox').css({
            top: mapBoxHeight + 52 + 'px'
        });

        Message.adjust()

        if (!zoomer) {
            zoomer = new zoom(gameWidth, gameHeight)
        } else {
            zoomer.setSettings(gameWidth, gameHeight)
            zoomer.lens.setdimensions();
        }
    }

}