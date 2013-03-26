// Social Reader JavaScript Functions

/*
 * Load Facebook's JavaScript SDK.
 */
( function() {
	var js, id = 'facebook-jssdk';

	if( document.getElementById( id ) )
		return;

	js = document.createElement( 'script' );
	js.id = id;
	js.async = true;
	js.src = "//connect.facebook.net/en_US/all.js";
	document.getElementsByTagName( 'head' )[ 0 ].appendChild( js );
}) ();


/*
 * Initialize the Facebook JS SDK, get the visitor's current Facebook
 * login status, and set up a listener for the subscribe event.
 */
jQuery( document ).ready( function() {
	social_reader_data = JSON.parse( social_reader_data );

	// Set options for the two states the social reader can have.
	social_reader_data.status = {
		on: 'http://plimages.blob.core.windows.net/images/social-reader/on.png',
		off: 'http://plimages.blob.core.windows.net/images/social-reader/off.png',
		on_text: 'Shared with Facebook friends.',
		off_text: 'Not shared with Facebook friends.'
	};

	FB.init({
		appId: social_reader_data.fb_app_id,
		status: true,
		cookie: true,
		xfbml: true
	});

	FB.getLoginStatus( function( response ) {
		if( response.status == 'connected' ) {
			social_reader_data.fb_user_id = response.authResponse.userID;
			jQuery( '#social-reader-fb-thumbnail' ).attr( 'src', 'https://graph.facebook.com/' + social_reader_data.fb_user_id + '/picture?type=square' );
			get_fb_sharing_option();
		}
		else {
			jQuery( '#social-reader-login-button' ).css( 'visibility', 'visible' );
		}
	});

	FB.Event.subscribe( 'auth.login', function( response ) {
		if( 'connected' == response.status ) {
			social_reader_data.fb_user_id = response.authResponse.userID;
			jQuery( '#social-reader-share-on' ).click();
			jQuery( '#social-reader-login-button' ).css( 'visibility', 'hidden' );
			jQuery( '#social-reader-fb-thumbnail' ).attr( 'src', 'https://graph.facebook.com/' + social_reader_data.fb_user_id + '/picture?type=square' );
			set_fb_sharing_option( 'true' );
		}
	});
});

function start_read_timer() {
	setTimeout( 'post_has_been_read()', social_reader_data.app_delay );
}

function get_fb_sharing_option() {
	jQuery.getJSON( 'https://src.personalliberty.com/FacebookSettingGet.ashx' +
		'?FacebookID=' + social_reader_data.fb_user_id +
		'&FieldName=socialreaderwillshare' +
		'&callback=?',
		function( response ) {
			if( 'True' == response )
				jQuery( '#social-reader-share-on' ).click();
			else
				jQuery( '#social-reader-share-off' ).click();
		}
	);
}

function set_fb_sharing_option( share ) {
	jQuery.getJSON( 'https://src.personalliberty.com/FacebookSettingPut.ashx' +
		'?FacebookID=' + social_reader_data.fb_user_id +
		'&FieldName=socialreaderwillshare' +
		'&ThisValue=' + share +
		'&callback=?'
	);
}

function post_has_been_read() {
	if( null == social_reader_data.fb_user_id )
		return;

	FB.api(
		'/me/pl-social-reader:read',
		'post',
		{ article: social_reader_data.article },
		function( response ) {
			if( response && ! response.error ) {
				response.id; // store this somewhere.
			}
	});
}

function display_social_reader() {
	jQuery( '#social-reader' ).show();
	jQuery( '#social-reader' ).animate({
		 height: '22px'
	}, 1000, function() {

	});
}

jQuery( document ).ready( function() {
	setTimeout( 'display_social_reader()', 1000 );
	
	jQuery( '#social-reader-settings' ).toggle( function() {
		if( null == social_reader_data.fb_user_id )
			return;

		jQuery( '#social-reader' ).animate({
			 height: '75px'
		}, 1000 ); }, function() {
		if( null == social_reader_data.fb_user_id )
			return;

		 jQuery( '#social-reader' ).animate({
			height: '22px'
		}, 1000 );
	});

	jQuery( '#social-reader-share-on' ).click( function() {
		jQuery( '#social-reader-status' ).attr( 'src', social_reader_data.status.on );
		jQuery( '#social-reader-state span' ).html( social_reader_data.status.on_text );
		jQuery( '#social-reader-share-on' ).css( 'background', '#9ff781' );
		jQuery( '#social-reader-share-off' ).css( 'background', '#eee' );
		
		set_fb_sharing_option( 'true' );
	});

	jQuery( '#social-reader-share-off' ).click( function() {
		jQuery( '#social-reader-status' ).attr( 'src', social_reader_data.status.off );
		jQuery( '#social-reader-state span' ).html( social_reader_data.status.off_text );
		jQuery( '#social-reader-share-on' ).css( 'background', '#eee' );
		jQuery( '#social-reader-share-off' ).css( 'background', '#f78181' );

		set_fb_sharing_option( 'false' );
	});
});
