jQuery(function($) {

    doScrollUp();

    $( window ).scroll( function() {
        doScrollUp();
    } );

    function doScrollUp() {
        var amountScrolled = $( window ).scrollTop();
        var $scrollUp = $( '#scroll-up' );

        $scrollUp.stop();
        if ( amountScrolled > 0 ) {
            $scrollUp.fadeIn();
        } else {
            $scrollUp.fadeOut();
        }
    }

    $( '#scroll-up' ).on( 'click', function() {
        $( 'html, body' ).animate( { scrollTop: 0 }, 400 );
    } );

});
