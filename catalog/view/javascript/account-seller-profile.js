$(function() {
	$("#ms-submit-button").click(function() {
		$('.success').remove();
		var button = $(this);
		var id = $(this).attr('id');

        if (msGlobals.config_enable_rte == 1) {
			for (var instance in CKEDITOR.instances) {
				CKEDITOR.instances[instance].updateElement();
			}
        }

		$.ajax({
			type: "POST",
			dataType: "json",
			url: $('base').attr('href') + 'index.php?route=seller/account-profile/jxsavesellerinfo',
			data: $("form#ms-sellerinfo").serialize(),
			beforeSend: function() {
				button.hide();
				$('p.error').remove();
			},
			complete: function(jqXHR, textStatus) {
				if (textStatus != 'success') {
					button.show().prev('span.wait').remove();
					$(".warning.main").text(msGlobals.formError).show();
					window.scrollTo(0,0);
				}
			},
			success: function(jsonData) {
				if (!jQuery.isEmptyObject(jsonData.errors)) {
                    $('#ms-submit-button').show().prev('span.wait').remove();
                    $('.error').text('');

                    for (error in jsonData.errors) {
                        if ($('#error_' + error).length > 0) {
                            $('#error_' + error).text(jsonData.errors[error]);
                            $('#error_' + error).parents('.form-group').addClass('has-error');
                        } else if ($('[name="'+error+'"]').length > 0) {
                            $('[name="' + error + '"]').parents('.form-group').addClass('has-error');
                            $('[name="' + error + '"]').parents('div:first').append('<p class="error">' + jsonData.errors[error] + '</p>');
                        } else $(".warning.main").append("<p>" + jsonData.errors[error] + "</p>").show();
                    }
                    window.scrollTo(0,0);
				} else if (!jQuery.isEmptyObject(jsonData.data) && jsonData.data.amount) {
					$(".ms-payment-form form input[name='custom']").val(jsonData.data.custom);
					$(".ms-payment-form form input[name='amount']").val(jsonData.data.amount);
					$(".ms-payment-form form").submit();
				} else {
					window.location = jsonData.redirect;
				}
	       	}
		});
	});
	
	$("#sellerinfo_avatar_files, #sellerinfo_banner_files").delegate(".ms-remove", "click", function() {
		$(this).parent().remove();
	});

	new MSUploader(
		{
			browse_button: 'ms-file-selleravatar',
			init : {
				FilesAdded: function (up, files) {
					$('#error_sellerinfo_avatar').html('');
					up.start();
				},

				Error: function(up, args) {
					$('#error_sellerinfo_avatar').append(msGlobals.uploadError).hide().fadeIn(2000);
					console.log('[error] ', args);
				}
			}
		},
		{
			paramsId: 'seller_profile',
			url: 'seller/account-profile/jxUploadSellerAvatar',
			fileUploadedCb: function (data) {

				if (!$.isEmptyObject(data.files)) {
					for (var i = 0; i < data.files.length; i++) {
						$("#sellerinfo_avatar_files").html(
							'<div class="ms-image">' +
							'<input type="hidden" value="'+data.files[i].name+'" name="seller[avatar_name]" />' +
							'<img src="'+data.files[i].thumb+'" />' +
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

	new MSUploader(
		{
			browse_button: 'ms-file-sellerbanner',
			init : {
				FilesAdded: function(up, files) {
					$('#error_sellerinfo_banner').html('');
					up.start();
				},

				Error: function(up, args) {
					$('#error_sellerinfo_banner').append(msGlobals.uploadError).hide().fadeIn(2000);
					console.log('[error] ', args);
				}
			}
		},
		{
			paramsId: 'seller_profile',
			url: 'seller/account-profile/jxUploadSellerAvatar',
			fileUploadedCb: function (data) {

				if (!$.isEmptyObject(data.files)) {
					for (var i = 0; i < data.files.length; i++) {
						$("#sellerinfo_banner_files").html(
							'<div class="ms-image">' +
							'<input type="hidden" value="'+data.files[i].name+'" name="seller[banner_name]" />' +
							'<img src="'+data.files[i].thumb+'" />' +
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
				$('#error_sellerinfo_banner').append(errorText).hide().fadeIn(2000);
			}
		});


	if (msGlobals.config_enable_rte == 1) {
		$('.ckeditor').each(function () {
			CKEDITOR.replace(this);
        });
	}

    $("select[name='seller[country]']").on('change', function() {
        $.ajax({
            url: 'index.php?route=account/account/country&country_id=' + this.value,
            dataType: 'json',
            beforeSend: function() {
               $("select[name='seller[country]']").after(' <i class="fa fa-circle-o-notch fa-spin"></i>');
            },
            complete: function() {
                $('.fa-spin').remove();
            },
            success: function(json) {
                html = '<option value="">' + msGlobals.zoneSelectError + '</option>';

                if (json['zone']) {
                    for (i = 0; i < json['zone'].length; i++) {
                        html += '<option value="' + json['zone'][i]['zone_id'] + '"';

                        if (json['zone'][i]['zone_id'] == msGlobals.zone_id) {
                            html += ' selected="selected"';
                        }

                    html += '>' + json['zone'][i]['name'] + '</option>';
                }
                } else {
                    html += '<option value="0" selected="selected">' + msGlobals.zoneNotSelectedError + '</option>';
                }

                $("select[name='seller[zone]']").html(html);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }).trigger('change');
});
