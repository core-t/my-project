function zoom(zoomWidth, zoomHeight) {
    var el = $('#game');
    var obj = this;
    var settings = {
        zoomWidth: zoomWidth,
        zoomHeight: zoomHeight
    };
    largeimageloaded = false; //tell us if large image is loaded
    el.scale = {};
    el.mousepos = {};
    el.mouseDown = false;
    var smallimage = new Smallimage();
    var lens = new Lens();
    var largeimage = new Largeimage();

    $.extend(obj, {
        init: function () {
            //drag option
            $(".zoomPad", el).mousedown(function () {
                el.mouseDown = true;
            });
            $(".zoomPad", el).mouseup(function () {
                el.mouseDown = false;
            });
            document.body.ondragstart = function () {
                return false;
            };
            $(".zoomPad", el).bind('mouseenter mouseover', function (event) {
                smallimage.fetchdata();
            });
            $(".zoomPad", el).bind('mousedown', function (e) {
                if (e.pageX > smallimage.pos.r || e.pageX < smallimage.pos.l || e.pageY < smallimage.pos.t || e.pageY > smallimage.pos.b) {
                    return false;
                }
                if (largeimageloaded && el.mouseDown) {
                    lens.setposition(e);
                }
            });
            $(".zoomPad", el).bind('mousemove', function (e) {

                //prevent fast mouse mevements not to fire the mouseout event
                if (e.pageX > smallimage.pos.r || e.pageX < smallimage.pos.l || e.pageY < smallimage.pos.t || e.pageY > smallimage.pos.b) {
                    return false;
                }

                if (!$('.zoomWindow', el).is(':visible')) {
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
        var image = $('#map');
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
        this.setdimensions = function () {
            this.node.w = (parseInt((settings.zoomWidth) / el.scale.x) > smallimage.w ) ? smallimage.w : (parseInt(settings.zoomWidth / el.scale.x));
            this.node.h = (parseInt((settings.zoomHeight) / el.scale.y) > smallimage.h ) ? smallimage.h : (parseInt(settings.zoomHeight / el.scale.y));
            this.node.top = (smallimage.oh - this.node.h - 2) / 2;
            this.node.left = (smallimage.ow - this.node.w - 2) / 2;
        };
        this.setcenter = function (x, y) {
            //            console.log(el);
            //            console.log(x);
            //            console.log(y);
            this.node.top = (parseInt(y) - 340)/20;
            //            console.log(this.node.top);
            this.node.left = (parseInt(x) - 380)/20;
            //            console.log(this.node.left);
            if(this.node.top > 103){
                this.node.top = 103;
            }else if(this.node.top < -1){
                this.node.top = -1;
            }
            if(this.node.left > 179){
                this.node.left = 179;
            }else if(this.node.left < -1){
                this.node.left = -1;
            }
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
                return el.mousepos.x - (lens.w) / 2 < smallimage.pos.l;
            }

            function overright(lens) {
                return el.mousepos.x + (lens.w) / 2 > smallimage.pos.r;

            }

            function overtop(lens) {
                return el.mousepos.y - (lens.h) / 2 < smallimage.pos.t;
            }

            function overbottom(lens) {
                return el.mousepos.y + (lens.h) / 2 > smallimage.pos.b;
            }

            lensleft = el.mousepos.x + smallimage.bleft - smallimage.pos.l - (this.node.w + 2) / 2;
            lenstop = el.mousepos.y + smallimage.btop - smallimage.pos.t - (this.node.h + 2) / 2;
            if (overleft(this.node)) {
                lensleft = smallimage.bleft - 1;
            } else if (overright(this.node)) {
                lensleft = smallimage.w + smallimage.bleft - this.node.w - 1;
            }
            if (overtop(this.node)) {
                lenstop = smallimage.btop - 1;
            } else if (overbottom(this.node)) {
                lenstop = smallimage.h + smallimage.btop - this.node.h - 1;
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
            this.node.css({
                position:'absolute',
                border: '0px',
                display: 'block',
                left: '-5000px',
                top: '0px'
            });
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
            lens.setdimensions();
            lens.show();
            //             lens.setcenter(settings.zoomPupX, settings.zoomPupY);
            largeimageloaded = true;
        };
        this.setposition = function () {
            var left = -el.scale.x * (lens.getoffset().left - smallimage.bleft + 1);
            var top = -el.scale.y * (lens.getoffset().top - smallimage.btop + 1);
            //            this.node.animate({
            //                left: left + 'px',
            //                top: top + 'px'
            //            },300);
            this.node.css({
                'left': left + 'px',
                'top': top + 'px'
            });
        };
        return this;
    }
    this.lensSetCenter = function(wi, hi) {
        lens.setcenter(wi, hi);
    };
}
