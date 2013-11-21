var Sound = {
    mute: false,
    play: function (name) {
        if (this.mute) {
            return;
        }
        $('#' + name).get(0).play();
    },
    isPlaying: function (name) {
        return !$('#' + name).get(0).paused;
    }
}