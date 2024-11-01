jQuery( document ).ready( function ( e ) {

    window.addEventListener('message',function(event) {
        if(event.origin==='https://bigbutton.inditip.com' && event.data && event.data.action==='height') 
        {
            var bbHt = event.data.height;
            var iframeId = event.data.id;
            jQuery('#' + iframeId).css('height',String(bbHt) + 'px');
        }
    });

});
