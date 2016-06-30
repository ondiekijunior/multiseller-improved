<?php echo $header; ?>
<div class="container">
	<ul class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
		<?php } ?>
	</ul>

	<?php if (isset($success) && ($success)) { ?>
		<div class="alert alert-success"><?php echo $success; ?></div>
  	<?php } ?>

	<div class="row"><?php echo $column_left; ?>
		<?php if ($column_left && $column_right) { ?>
		<?php $class = 'col-sm-6'; ?>
		<?php } elseif ($column_left || $column_right) { ?>
		<?php $class = 'col-sm-9'; ?>
		<?php } else { ?>
		<?php $class = 'col-sm-12'; ?>
		<?php } ?>
		<div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>
			<h1><?php echo $ms_account_sellersetting_breadcrumbs; ?></h1>

			<form id="ms-sellersettings" class="ms-form form-horizontal">
				<fieldset>
					<legend><?php echo $ms_seller_address; ?></legend>
					<input type="hidden" name="seller_id" value="<?php echo $seller_id ;?>">

					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_full_name; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_full_name]"
								   value="<?php echo $settings['slr_full_name']; ?>"
								   placeholder="<?php echo $ms_seller_full_name; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_address1; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_address_line1]"
								   value="<?php echo $settings['slr_address_line1']; ?>"
								   placeholder="<?php echo $ms_seller_address1_placeholder ;?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_address2; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_address_line2]"
								   value="<?php echo $settings['slr_address_line2']; ?>"
								   placeholder="<?php echo $ms_seller_address2_placeholder ;?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_city; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_city]"
								   value="<?php echo $settings['slr_city']; ?>"
								   placeholder="<?php echo $ms_seller_city; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_state; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_state]"
								   value="<?php echo $settings['slr_state']; ?>"
								   placeholder="<?php echo $ms_seller_state ;?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_zip; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_zip]"
								   value="<?php echo $settings['slr_zip']; ?>"
								   placeholder="<?php echo $ms_seller_zip ;?>">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_country; ?></label>

						<div class="col-sm-10">
							<select class="form-control" name="settings[slr_country]">
								<?php foreach($countries as $country) :?>
								<?php if($settings['slr_country'] == $country['country_id']) :?>
								<option value="<?php echo $country['country_id'] ;?>"
										selected><?php echo $country['name'];?></option>
								<?php else :?>
								<option value="<?php echo $country['country_id'] ;?>"><?php echo $country['name'] ;?></option>
								<?php endif ;?>
								<?php endforeach ;?>
							</select>
						</div>
					</div>
				</fieldset>

				<fieldset class="control-inline">
					<legend><?php echo $ms_seller_information; ?></legend>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_website; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_website]"
								   value="<?php echo $settings['slr_website']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_company; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_company]"
								   value="<?php echo $settings['slr_company']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_phone; ?></label>

						<div class="col-sm-10">
							<input type="text" class="form-control" name="settings[slr_phone]"
								   value="<?php echo $settings['slr_phone']; ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><?php echo $ms_seller_logo; ?></label>

						<div class="col-sm-10">
							<div class="buttons">
								<a name="ms-file-selleravatar" id="ms-file-selleravatar" class="btn btn-primary"><span><?php echo $ms_button_select_image; ?></span></a>
							</div>

							<p class="ms-note"><?php echo $ms_account_sellerinfo_logo_note; ?></p>

							<p class="error" id="error_sellerinfo_avatar"></p>

							<div id="sellerinfo_avatar_files">
								<?php if (!empty($settings['slr_logo'])) { ?>
								<div class="ms-image">
									<img src="<?php echo $settings['slr_thumb']; ?>"/>
									<span class="ms-remove"></span>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</fieldset>

				<div class="buttons">
					<div class="pull-right">
						<a class="btn btn-primary" id="ms-submit-button"><span><?php echo $ms_button_save; ?></span></a>
					</div>
				</div>
			</form>
			<?php echo $content_bottom; ?></div>
		<?php echo $column_right; ?></div>
</div>
<script>
	$("#ms-submit-button").click(function (e) {
		var data = $('#ms-sellersettings').serialize();
		$.ajax({
			url: 'index.php?route=seller/account-setting/jxsavesellerinfo',
			data: data,
			dataType: 'json',
			type: 'post',
			success: function (jsonData) {
				if (!jQuery.isEmptyObject(jsonData.errors)) {
					$('.error').text('');

					for (error in jsonData.errors) {
						if ($('#error_' + error).length > 0) {
							$('#error_' + error).text(jsonData.errors[error]);
							$('#error_' + error).parents('.form-group').addClass('has-error');
						} else if ($('[name="settings[' + error + ']"]').length > 0) {
							$('[name="settings[' + error + ']"]').parents('.form-group').addClass('has-error');
							$('[name="settings[' + error + ']"]').parents('div:first').append('<p class="error" id="error_' + error + '">' + jsonData.errors[error] + '</p>');
						} else $(".warning.main").append("<p>" + jsonData.errors[error] + "</p>").show();
					}
				} else {
					window.location.reload();
				}
			},
			error: function (error) {
				console.log(error);
			}
		});
	});
</script>
<?php $timestamp = time(); ?>
<script>
	var msGlobals = {
		timestamp: '<?php echo $timestamp; ?>',
		session_id: '<?php echo session_id(); ?>',
		uploadError: '<?php echo htmlspecialchars($ms_error_file_upload_error, ENT_QUOTES, "UTF-8"); ?>',
		formError: '<?php echo htmlspecialchars($ms_error_form_submit_error, ENT_QUOTES, "UTF-8"); ?>',
	};
</script>
<?php echo $footer; ?>