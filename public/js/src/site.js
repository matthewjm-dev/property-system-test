// Site Forms
jQuery(function ($) {
    'use strict';

    // Init Ajax Forms
    $('#contact_form_submissions').ipsAjaxForm({
        success: function (message) {
            $(this).addClass('success').empty();
        }
    });
});


// Admin Main
jQuery(function ($) {
    'use strict';

});
