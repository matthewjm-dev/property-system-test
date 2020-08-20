// Site Forms
jQuery(function ($) {
    'use strict';

    // Init Ajax Forms
    $('#property_form').ipsAjaxForm({});
});


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
                history.pushState(null, null, $(this).attr('href'));
                init_pagination();
            }
        });
    }

});
