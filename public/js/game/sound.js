var Sound = {
    play: function (name) {
        $('#' + name).get(0).play();
    },
    isPlaying: function (name) {
        return !$('#' + name).get(0).paused;
    }
}