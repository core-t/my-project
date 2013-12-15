$(document).ready(function () {

    Login.adjust()

    $(window).resize(function () {
        Login.adjust()
    })
})

var Login = {
    adjust: function () {
        var height = $(window).height()

        var top = (height - $('#page').height()) / 2
        if (top < 0) {
            top = 0
        }
        console.log(top)
        $('#page').css({
            top: top + 'px'
        })
    }
}