humhub.module('translation', function(module, require, $) {

    var Widget = require('ui.widget').Widget;
    var client = require('client');

    var Form = Widget.extend();

    var stateHideTranslated = false;

    Form.prototype.init = function() {
        this.updateEmptyFilterState();
    };

    Form.prototype.selectOptions = function(evt) {
        var $form = $('#translation-editor-form');

        var that = this;

        // client unloadForm available in HumHub 1.5.3
        if(!client.unloadForm || client.unloadForm($form)) {
            this.options.widgetReloadUrl = this.appendToUrl(this.options.loadUrl, evt.$trigger.is('select[name="file"]'));
            this.reload().then(function() {
                that.updateEmptyFilterState();
            });
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

    Form.prototype.toggleEmptyTranslationFilter = function() {
        stateHideTranslated = !stateHideTranslated;
        this.updateEmptyFilterState();
    };

    Form.prototype.updateEmptyFilterState = function() {
        this.$.find('.translation.translated').each(function() {
            var $row =  $(this).closest('.row');
            if(stateHideTranslated) {
                $row.hide();
                $('#toggle-empty-filter').find('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');

            } else {
                $row.show();
                $('#toggle-empty-filter').find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
            }
        });
    };

    Form.prototype.copyOriginal = function (evt) {
        evt.$trigger.closest('.item').find('textarea')
            .val(evt.$trigger.closest('.elem').find('.pre').text());
    }

    Form.prototype.copyParent = function (evt) {
        const input = evt.$trigger.closest('.elem').find('textarea');
        input.val(input.attr('placeholder'));
    }

    module.export({
        Form: Form
    })
});
