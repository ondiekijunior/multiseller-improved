$(document).ready(function () {
	new MSUploader(
		{
			browse_button: 'ms-file-selleravatar',
			init: {
				FilesAdded: function (up, files) {
					$('#error_sellerinfo_avatar').html('');
					up.start();
				},

				Error: function (up, args) {
					$('#error_sellerinfo_avatar').append(msGlobals.uploadError).hide().fadeIn(2000);
					console.log('[error] ', args);
				}
			}
		},
		{
			paramsId: 'seller_profile',
			url: 'seller/account-setting/jxUploadSellerLogo',
			fileUploadedCb: function (data) {

				if (!$.isEmptyObject(data.files)) {
					for (var i = 0; i < data.files.length; i++) {
						$("#sellerinfo_avatar_files").html(
							'<div class="ms-image">' +
							'<input type="hidden" value="' + data.files[i].name + '" name="settings[slr_logo]" />' +
							'<img src="' + data.files[i].thumb + '" />' +
							'<span class="ms-remove"></span>' +
							'</div>').children(':last').hide().fadeIn(2000);
					}

					data.cancel = true;
				}
			},
			dataErrorsCb: function (data) {
				var errorText = '';
				for (var i = 0; i < data.errors.length; i++) {
					errorText += data.errors[i] + '<br />';
				}
				$('#error_sellerinfo_avatar').append(errorText).hide().fadeIn(2000);
			}
		});

		$("#sellerinfo_avatar_files, #sellerinfo_banner_files").delegate(".ms-remove", "click", function () {
			$(this).parent().remove();
		});
});