(function($) {
	'use strict';

	// User authentication
	hivepress.authUser = function(data) {
		$.ajax({
			url: hpCoreFrontendData.apiURL + '/auth/' + data.provider,
			method: 'POST',
			data: data,
			beforeSend: function(xhr) {
				xhr.setRequestHeader('X-WP-Nonce', hpCoreFrontendData.apiNonce);
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
					'provider': 'facebook',
					'access_token': response.authResponse.accessToken,
				});
			}
		});
	}

	// Google callback
	window.onGoogleAuth = function(user) {
		hivepress.authUser({
			'provider': 'google',
			'id_token': user.getAuthResponse().id_token,
		});
	}
})(jQuery);
