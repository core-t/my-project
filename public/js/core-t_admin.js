$().ready(function(){
    $('#main td').each(function(){
        var url = $($(this).children()[1]).attr('href');
        $(this).click(function(){
            window.location.href = url;
        });
    });

});
