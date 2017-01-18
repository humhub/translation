$(document).on('ready', function() {
    search();
    formAction();
    onEvents();
});

$(document).on('pjax:complete', function () {
    search();
    formAction();
    onEvents();
});

function onEvents() {
    $('#pjax').off('pjax:complete');
    $('#pjax').on('pjax:complete', function () {
        $('#pjax').hideLoading();
    });

    $('#pjax').off('pjax:send');
    $('#pjax').on('pjax:send', function () {
        $('#pjax').showLoading();
    });
}

function formAction(){

    $('#form').off('submit');
    $('#form').on('submit', function (e) {
        $(this).attr('action', $(this).attr('action') +
            '?moduleId=' + $('select[name="moduleId"]').val() +
            '&language=' + $('select[name="language"]').val() +
            '&file='     + $('select[name="file"]').val()
        );
        return true;
    });
}

function search(){

    $('input[name="search"]').off('keyup');
    $('input[name="search"]').on('keyup', function(){
        var inputValue = this.value;
        $("#words").find('.row').each(function(indx, el){
            el = $(el).find('div[class="elem"]');
            var expr = new RegExp(escapeRegExp(inputValue), 'i');
            el.show();
            if (inputValue  != '' && !expr.test(el.text())){
                el.hide();
            }
        });
    });

    $('input[name="search"]').keypress(function(e){
        if ( e.which == 13 ) {
            e.preventDefault();
        }
    });
}

function selectOptions(){
    $('input[name="saveForm"]').val(0);
    $("#submitPjax").click();
}

function escapeRegExp(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}