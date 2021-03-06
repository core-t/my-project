function zoom(gameWidth, gameHeight) {
    var el = $('body');
    var obj = this;
    var settings = {
        gameWidth: gameWidth,
        gameHeight: gameHeight
    };

    largeimageloaded = false; //tell us if large image is loaded
    el.scale = {};
    el.mousepos = {};
    el.mouseDown = false;
    var smallimage = new Smallimage();
    var lens = new Lens();
    this.lens = lens;
    var largeimage = new Largeimage();

    $.extend(obj, {
        init: function () {
            //drag option
            $("#map", el).mousedown(function () {
                el.mouseDown = true;
            });
            $("#map", el).mouseup(function () {
                el.mouseDown = false;
            });
            document.body.ondragstart = function () {
                return false;
            };
            $("#map", el).bind('mouseenter mouseover', function (event) {
                smallimage.fetchdata();
            });
            $("#map", el).bind('mousedown', function (e) {
                if (e.pageX > smallimage.pos.r || e.pageX < smallimage.pos.l || e.pageY < smallimage.pos.t || e.pageY > smallimage.pos.b) {
                    return false;
                }
                if (largeimageloaded && el.mouseDown) {
                    lens.setposition(e);
                }
            });
            $("#map", el).bind('mousemove', function (e) {

                //prevent fast mouse mevements not to fire the mouseout event
//                if (e.pageX > smallimage.pos.r || e.pageX < smallimage.pos.l || e.pageY < smallimage.pos.t || e.pageY > smallimage.pos.b) {
//                    return false;
//                }

                if (!$('#game', el).is(':visible')) {
                    obj.activate(e);
                }
                if (el.mouseDown) {
                    lens.setposition(e);
                }
            });
            largeimage.loadimage();
        },
        load: function () {
            largeimage.loadimage();
        },
        activate: function (e) {
            //show lens and zoomWindow
            lens.show();
        }
    });
    /*========================================================,
     |   Smallimage
     |---------------------------------------------------------:
     |   Base image into the anchor element
     `========================================================*/

    function Smallimage() {
        var $obj = this;
        var image = $('#mapImage');
        this.node = image[0];
        this.findborder = function () {
            var bordertop = 0;
            bordertop = image.css('border-top-width');
            btop = '';
            var borderleft = 0;
            borderleft = image.css('border-left-width');
            bleft = '';
            if (bordertop) {
                for (i = 0; i < 3; i++) {
                    var x = [];
                    x = bordertop.substr(i, 1);
                    if (isNaN(x) == false) {
                        btop = btop + '' + bordertop.substr(i, 1);
                    } else {
                        break;
                    }
                }
            }
            if (borderleft) {
                for (i = 0; i < 3; i++) {
                    if (!isNaN(borderleft.substr(i, 1))) {
                        bleft = bleft + borderleft.substr(i, 1)
                    } else {
                        break;
                    }
                }
            }
            $obj.btop = (btop.length > 0) ? eval(btop) : 0;
            $obj.bleft = (bleft.length > 0) ? eval(bleft) : 0;
        };
        this.fetchdata = function () {
            $obj.findborder();
            $obj.w = image.width();
            $obj.h = image.height();
            $obj.ow = image.outerWidth();
            $obj.oh = image.outerHeight();
            $obj.pos = image.offset();
            $obj.pos.l = image.offset().left + $obj.bleft;
            $obj.pos.t = image.offset().top + $obj.btop;
            $obj.pos.r = $obj.w + $obj.pos.l;
            $obj.pos.b = $obj.h + $obj.pos.t;
            $obj.rightlimit = image.offset().left + $obj.ow;
            $obj.bottomlimit = image.offset().top + $obj.oh;

        };
        this.node.onload = function () {
            $obj.fetchdata();
            obj.init();
        };
        return $obj;
    };

    /*========================================================,
     |   Lens
     |---------------------------------------------------------:
     |   Lens over the image
     `========================================================*/

    function Lens() {
        var $obj = this;
        this.node = $('.zoomPup');
        this.setdimensions = function (width, hidth) {
            if (width && hidth) {
                var gameWidth = width;
                var gameHeight = hidth;
            } else {
                var gameWidth = settings.gameWidth;
                var gameHeight = settings.gameHeight;
            }

            this.node.w = (parseInt((gameWidth) / el.scale.x) > smallimage.w ) ? smallimage.w : (parseInt(gameWidth / el.scale.x));
            this.node.h = (parseInt((gameHeight) / el.scale.y) > smallimage.h ) ? smallimage.h : (parseInt(gameHeight / el.scale.y));
            this.node.css({
                'width': this.node.w,
                'height': this.node.h
            });
            this.node.top = (smallimage.oh - this.node.h - 2) / 2;
            this.node.left = (smallimage.ow - this.node.w - 2) / 2;
        };
        this.setcenter = function (x, y) {
            if (!my.turn && !show) {
                return;
            }

            this.node.top = parseInt((parseInt(y) - settings.gameHeight / 2) / el.scale.y);
            this.node.left = parseInt((parseInt(x) - settings.gameWidth / 2) / el.scale.x);
            this.node.css({
                top: this.node.top,
                left: this.node.left
            });
            largeimage.setposition();
        };
        this.setposition = function (e) {
            el.mousepos.x = e.pageX;
            el.mousepos.y = e.pageY;
            var lensleft = 0;
            var lenstop = 0;

            function overleft(lens) {
                return el.mousepos.x < smallimage.pos.l;
            }

            function overright(lens) {
                return el.mousepos.x > smallimage.pos.r;

            }

            function overtop(lens) {
                return el.mousepos.y < smallimage.pos.t;
            }

            function overbottom(lens) {
                return el.mousepos.y > smallimage.pos.b;
            }

            lensleft = el.mousepos.x + smallimage.bleft - smallimage.pos.l - (this.node.w + 2) / 2;
            lenstop = el.mousepos.y + smallimage.btop - smallimage.pos.t - (this.node.h + 2) / 2;
            if (overleft(this.node)) {
                lensleft = smallimage.bleft - this.node.w / 2;
            } else if (overright(this.node)) {
                lensleft = smallimage.w + smallimage.bleft - this.node.w / 2;
            }
            if (overtop(this.node)) {
                lenstop = smallimage.btop - this.node.h / 2;
            } else if (overbottom(this.node)) {
                lenstop = smallimage.h + smallimage.btop - this.node.h / 2;
            }

            this.node.left = lensleft;
            this.node.top = lenstop;
            this.node.css({
                'left': lensleft + 'px',
                'top': lenstop + 'px'
            });
            largeimage.setposition();
        };
        this.show = function () {
            this.node.show();
        };
        this.getoffset = function () {
            var o = {};
            o.left = $obj.node.left;
            o.top = $obj.node.top;
            return o;
        };
        return this;
    };
    /*========================================================,
     |   LargeImage
     |---------------------------------------------------------:
     |   The large detailed image
     `========================================================*/

    function Largeimage() {
        var $obj = this;
        this.node = $('#board');
        $obj.scale = {};
        this.loadimage = function () {
            $obj.w = this.node.width();
            $obj.h = this.node.height();
            $obj.pos = this.node.offset();
            $obj.pos.l = this.node.offset().left;
            $obj.pos.t = this.node.offset().top;
            $obj.pos.r = $obj.w + $obj.pos.l;
            $obj.pos.b = $obj.h + $obj.pos.t;
            $obj.scale.x = ($obj.w / smallimage.w);
            $obj.scale.y = ($obj.h / smallimage.h);
            el.scale = $obj.scale;
            //setting lens dimensions;
            lens.setdimensions(0, 0);
            lens.show();

            this.node
                .mousedown(function (e) {
                    if (!Gui.lock) {
                        switch (e.which) {
                            case 1:
                                if (Army.selected) {
                                    var path = AStar.cursorPosition(e.pageX, e.pageY, 1);

                                    if (Army.selected.x == AStar.x && Army.selected.y == AStar.y) {
                                        return;
                                    }

                                    Websocket.move(AStar.x, AStar.y);
                                } else {
                                    // grabbing the map
                                    var pageX = e.pageX;
                                    var pageY = e.pageY;

                                    var left = parseInt(largeimage.node.css('left'));
                                    var top = parseInt(largeimage.node.css('top'));

                                    var centerPageX = settings.gameWidth / 2;
                                    var centerPageY = settings.gameHeight / 2;

                                    $obj.node.mousemove(function (e) {
                                        lens.setcenter((centerPageX + (pageX - e.pageX)) - left, (centerPageY + (pageY - e.pageY)) - top);
                                    });
                                }
                                break;
                            case 2:
//                        alert('Middle mouse button pressed');
                                break;
                            case 3:
                                if (Army.selected) {
                                    var destination = AStar.getDestinationXY(e.pageX, e.pageY);

                                    if (Army.selected.x == destination.x && Army.selected.y == destination.y) {
                                        return;
                                    }

                                    Army.deselect();
                                }
                                break;
                            default:
                                alert('You have a strange mouse');
                        }
                    }
                })
                .mousemove(function (e) {
                    if (!Gui.lock) {
                        AStar.cursorPosition(e.pageX, e.pageY);
                    }
                })
                .mouseleave(function () {
                    $('.path').remove();
                    $obj.node
                        .unbind('mousemove')
                        .mousemove(function (e) {
                            if (!Gui.lock) {
                                AStar.cursorPosition(e.pageX, e.pageY);
                            }
                        });
                })
                .mouseup(function () {
                    $obj.node
                        .unbind('mousemove')
                        .mousemove(function (e) {
                            if (!Gui.lock) {
                                AStar.cursorPosition(e.pageX, e.pageY);
                            }
                        });
                });

            largeimageloaded = true;
        };
        this.setposition = function () {
            var left = -el.scale.x * (lens.getoffset().left - smallimage.bleft + 1);
            var top = -el.scale.y * (lens.getoffset().top - smallimage.btop + 1);
            this.node.css({
                'left': left + 'px',
                'top': top + 'px'
            });
        };
        return this;
    }

    this.lensSetCenter = function (wi, hi) {
        lens.setcenter(wi, hi);
    };

    this.setSettings = function (gameWidth, gameHeight) {
        settings = {
            gameWidth: gameWidth,
            gameHeight: gameHeight
        };
    }

    this.setCenterIfOutOfScreen = function (x, y) {
        var top = parseInt(parseInt(y) / el.scale.y);
        var lensTop = parseInt($('.zoomPup').css('top'));
        var lensHeight = parseInt($('.zoomPup').css('height'));

        var maxTop = lensTop + lensHeight - 10;
        var minTop = lensTop + 10;

        var left = parseInt((parseInt(x)) / el.scale.x);
        var lensLeft = parseInt($('.zoomPup').css('left'));
        var lensWidth = parseInt($('.zoomPup').css('width'));

        var maxLeft = lensLeft + lensWidth - 20;
        var minLeft = lensLeft + 20;

        if ((top >= maxTop) || (top <= minTop)) {
            lens.setcenter(x, y);
        } else if ((left >= maxLeft) || (left <= minLeft)) {
            lens.setcenter(x, y);
        }
    };
}
