(function($) {
	'use strict';

	// User authentication
	hivepress.authUser = function(data) {
		$.ajax({
			url: hivepressAuthenticationData.apiURL + data.authenticator,
			method: 'POST',
			data: data,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('X-WP-Nonce', hivepressCoreData.apiNonce);
			},
			complete: function(xhr) {
				var response = xhr.responseJSON;
				if (response === null || response.hasOwnProperty('data')) {
					window.location.reload(true);
				}
			},
		});
	}

	// Facebook callback
	window.onFacebookAuth = function() {
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
				hivepress.authUser({
					'authenticator': 'facebook',
					'access_token': response.authResponse.accessToken,
				});
			}
		});
	}

	// Google callback - UPDATED for new Google Identity Services
	window.onGoogleAuth = function(credentialResponse) {
		// New API returns JWT token directly in credential property
		hivepress.authUser({
			'authenticator': 'google',
			'id_token': credentialResponse.credential,
		});
	}

})(jQuery);
