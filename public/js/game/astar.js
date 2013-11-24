// *** A* ***

var AStar = {
    myCastleId: {},
    x: 0,
    y: 0,
    cursorPosition: function (x, y, force) {
        var offset = $('.zoomWindow').offset();
        var X = x - 20 - parseInt(board.css('left')) - offset.left;
        var Y = y - 20 - parseInt(board.css('top')) - offset.top;
        var destX = Math.round(X / 40);
        var destY = Math.round(Y / 40);

        var castleId = 0;
        if (castleId = Castle.get(destX, destY)) {
            coord.html(castles[castleId].name);
        } else {
//            coord.html(destX + ' - ' + destY + ' ' + terrain[fields[destY][destX]].name);
            coord.html(terrain[fields[destY][destX]].name);
        }

        if (Army.selected) {
            this.myCastleId = {};
            if (AStar.x == destX && AStar.y == destY && force != 1) {
                return null;
            }
            $('.path').remove();
            AStar.x = destX;
            AStar.y = destY;
            var startX = Army.selected.x;
            var startY = Army.selected.y;
            var open = {};
            var close = {};
            var start = new node(startX, startY, destX, destY, 0);
            open[startX + '_' + startY] = start;

            if (castleId) {
                castleId = Castle.getMy(startX, startY);
                this.myCastleId[castleId] = true;
            }

            this.aStar(close, open, destX, destY, 1);

            return this.showPath(close, destX + '_' + destY);
        }
        return null;
    },
    getPath: function (close, key) {
        var path = new Array();
        var i = 0;
        while (isSet(close[key].parent)) {
            path[path.length] = close[key];
            key = close[key].parent.x + '_' + close[key].parent.y;
        }
        path = path.reverse();

        for (k in path) {
            if (path[k].tt == 'c') {
                var castleId = Castle.getMy(path[k].x, path[k].y);
                if (this.myCastleId[castleId]) {
                    i++;
                } else {
                    this.myCastleId[castleId] = true;
                }
            }
            path[k].F -= i;
            path[k].G -= i;
        }

        return path;
    },
    showPath: function (close, key) {
        if (notSet(close[key])) {
            return 0;
        }

        var path = this.getPath(close, key);

        var className = 'path1';

        for (i in path) {
            var pathX = path[i].x * 40;
            var pathY = path[i].y * 40;

            if (Army.selected.moves < path[i].G) {
                if (className == 'path1') {
                    className = 'path2';
                }
//                if (notSet(set)) {
//                    var set = {'x': pathX, 'y': pathY};
//                }
            }

            board.append(
                $('<div>')
                    .addClass('path ' + className)
                    .css({
                        left: pathX + 'px',
                        top: pathY + 'px'
                    })
                    .html(path[i].G)
            );
        }

        return path;
//        var set = this.walking(path);
//
//        if (notSet(set)) {
//            return;
//        } else {
//            AStar.x = set.x;
//            AStar.y = set.y;
//        }
    },
//    walking: function (path) {
//        var className = 'path1';
//
//        for (i in path) {
//            var pX = path[i].x * 40;
//            var pY = path[i].y * 40;
//
//            if (Army.selected.moves < path[i].G) {
//                className = 'path2';
//                if (notSet(set)) {
//                    var set = {'x': pX, 'y': pY};
//                }
//            }
//
//            board.append(
//                $('<div>')
//                    .addClass('path ' + className)
//                    .css({
//                        left: pX + 'px',
//                        top: pY + 'px'
//                    })
//                    .html(path[i].G)
//            );
//        }
//        return set;
//    },
    aStar: function (close, open, destX, destY, nr) {
        nr++;
        if (nr > 7000) {
            nr--;
//            console.log('>' + nr);
            return;
        }
        var f = this.findSmallestF(open);
        var x = open[f].x;
        var y = open[f].y;
        close[f] = open[f];
        if (x == destX && y == destY) {
            return;
        }
        delete open[f];
        this.addOpen(x, y, close, open, destX, destY);
        if (!this.isNotEmpty(open)) {
            return;
        }
        this.aStar(close, open, destX, destY, nr);
    },
    addOpen: function (x, y, close, open, destX, destY) {
        var startX = x - 1;
        var startY = y - 1;
        var endX = x + 1;
        var endY = y + 1;
        var i, j = 0;
        var g;

        for (i = startX; i <= endX; i++) {
            for (j = startY; j <= endY; j++) {

                if (x == i && y == j) {
                    continue;
                }

                var key = i + '_' + j;

                if (isSet(close[key]) && close[key].x == i && close[key].y == j) {
                    continue;
                }

                if (notSet(fields[j]) || notSet(fields[j][i])) {
                    continue;
                }

                var terrainType = fields[j][i];

                if (terrainType == 'e') {
                    continue;
                }

                if (terrainType == 'f' || terrainType == 's' || terrainType == 'm') {
                    g = terrain[terrainType][Army.selected.movementType];
                    for (key in Army.selected.soldiers) {
                        var tmpG = units[Army.selected.soldiers[key].unitId][terrainType];
                        console.log(units[Army.selected.soldiers[key].unitId]);
                        if (tmpG > g) {
                            g = tmpG;
                        }
                    }

                } else {
                    g = terrain[terrainType][Army.selected.movementType];
                }

                g = Army.selected.terrain[terrainType];

                if (g > 6) {
                    continue;
                }
                if (isSet(open[key])) {
                    this.calculatePath(x + '_' + y, open, close, g, key);
                } else {
                    var parent = {
                        'x': x,
                        'y': y
                    };
                    g += close[x + '_' + y].G;
                    open[key] = new node(i, j, destX, destY, g, parent, terrainType);
                }
            }
        }
    },
    isNotEmpty: function (obj) {
        for (key in obj) {
            if (obj.hasOwnProperty(key)) {
                return true;
            }
        }
        return false;
    },
    findSmallestF: function (open) {
        var i;
        var f;
        for (i in open) {
            pX = open[i].x * 40;
            pY = open[i].y * 40;
            if (notSet(open[f])) {
                f = i;
            }
            if (open[i].F < open[f].F) {
                f = i;
            }
        }
        return f;
    },
    calculatePath: function (kA, open, close, g, key) {
        if (open[key].G > (g + close[kA].G)) {
            open[key].parent = {
                'x': close[kA].x,
                'y': close[kA].y
            };
            open[key].G = g + close[kA].G;
            open[key].F = open[key].G + open[key].H;
        }
    },
    calculateH: function (x, y, destX, destY) {
        return Math.sqrt(Math.pow(destX - x, 2) + Math.pow(y - destY, 2))
    }
}

function node(x, y, destX, destY, g, parent, tt) {
    this.x = x;
    this.y = y;
    this.G = g;
    this.H = AStar.calculateH(this.x, this.y, destX, destY);
    this.F = this.H + this.G;
    this.parent = parent;
    this.tt = tt;
}

