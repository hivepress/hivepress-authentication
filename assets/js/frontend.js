hivepress.todo = function() {
	(function($) {
		'use strict';

		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
				$.ajax({
					url: 'http://localhost/hivepress/wp-json/hivepress/v1/users/login/facebook',
					method: 'POST',
					data: {
						'access_token': response.authResponse.accessToken,
					},
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
		});
	})(jQuery);
}
