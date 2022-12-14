(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.stream || (joms.popup.stream = {});
    joms.popup.stream.showComments = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.stream.showComments;
    });

})( window, joms.jQuery, function( window, $ ) {

var popup, elem, id, type;

function render( _popup, _id, _type ) {
    var data;

    if ( elem ) elem.off();
    popup = _popup;
    id = _id;
    type = _type;

    data = [ id ];
    if ( type ) {
        data.push( type );
    }

    joms.ajax({
        func: 'system,ajaxStreamShowComments',
        data: data,
        callback: function( json ) {
            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            initVideoPlayers();
            joms.parseEmoji();
        }
    });
}

function initVideoPlayers() {
    var initialized = '.joms-js--initialized',
        cssVideos = '.joms-js--video',
        videos = $('.joms-comment__body,.joms-js--inbox').find( cssVideos ).not( initialized ).addClass( initialized.substr(1) );

    if ( !videos.length ) {
        return;
    }

    joms.loadCSS( joms.ASSETS_URL + 'vendors/mediaelement/mediaelementplayer.min.css' );
    videos.on( 'click.joms-video', cssVideos + '-play', function() {
        var $el = $( this ).closest( cssVideos );
        joms.util.video.play( $el, $el.data() );
    });

    if ( joms.ios ) {
        setTimeout(function() {
            videos.find( cssVideos + '-play' ).click();
        }, 2000 );
    }
}

function buildHtml( json ) {
    var isEmpty = true,
        fragment;

    json || (json = {});

    fragment = $( $.trim( json.html || '' ) );
    if ( fragment.children().length ) {
        isEmpty = false;
    }

    return [
        '<div class="joms-popup joms-popup--whiteblock joms-popup--rounded joms-popup--80pc">',
        '<button class="mfp-close joms-hide"></button>',
        '<div class="joms-comment">', ( isEmpty ? window.joms_lang.COM_COMMUNITY_NO_COMMENTS_YET : json.html ), '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function( id, type ) {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp, id, type );
    });
};

});
