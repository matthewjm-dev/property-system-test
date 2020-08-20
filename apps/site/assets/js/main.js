// Admin Main
jQuery(function ($) {
    'use strict';

    init_pagination();

    function init_pagination() {
        console.log('init_pagination');
        $('body').find('#pagination a').ipsAjaxButton({
            before: function(methods) {
                console.log('methods', methods);
                methods.add_loader($('.property-list'));
            },
            complete_override: function () {
                history.pushState(null, null, 'page/');
                console.log('pagination success', $(this));
                init_pagination();
            }
        });
    }

});
