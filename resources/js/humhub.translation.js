humhub.module('translation', function(module, require, $) {

    var Widget = require('ui.widget').Widget;
    var client = require('client');

    var Form = Widget.extend();

    Form.prototype.selectOptions = function(evt) {
        var $form = $('#translation-editor-form');

        // client unloadForm available in HumHub 1.5.3
        if(!client.unloadForm || client.unloadForm($form)) {
            this.options.widgetReloadUrl = this.appendToUrl(this.options.loadUrl, evt.$trigger.is('select[name="file"]'));
            this.reload();
            if(window.history) {
                window.history.replaceState(null, null, this.options.widgetReloadUrl );
            }
        }
    };

    Form.prototype.search = function(evt) {

        // Prevent form submission on enter
        if (evt.originalEvent.which === 13) {
            evt.originalEvent.preventDefault();
            return;
        }

        if(this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        this.searchTimeout = setTimeout(function() {
            var inputValue =  evt.$trigger.val();
            $("#words").find('.row').each(function(indx, el){
                var $row = $(this);
                if (inputValue  && !new RegExp(escapeRegExp(inputValue), 'i').test($row.text())){
                    $row.hide();
                } else {
                    $row.show();
                }
            });
        },10)

    };

    function escapeRegExp(str) {
        return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
    }

    Form.prototype.appendToUrl = function(url, isFile) {
        var params = 'moduleId='+this.$.find('select[name="moduleId"]').val()
            +'&language='+this.$.find('select[name="language"]').val();

        if(isFile) {
            params += '&file='+this.$.find('select[name="file"]').val()
        }

        return (url.indexOf('?') !== -1)
            ? url + '&' + params
            : url + '?' + params;
    };

    module.export({
        Form: Form
    })
});