'use strict';

hivepress.todo = function() {
	FB.getLoginStatus(function(response) {
		if (response.status === 'connected') {
			todo3({
				'provider': 'facebook',
				'access_token': response.authResponse.accessToken,
			});
		}
	});
}

function todo2(user) {
	todo3({
		'provider': 'google',
		'id_token': user.getAuthResponse().id_token,
	});
}

function todo3(request) {
	jQuery.ajax({
		url: hpCoreFrontendData.apiURL+'/users/login/' + request.provider,
		method: 'POST',
		data: request,
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
