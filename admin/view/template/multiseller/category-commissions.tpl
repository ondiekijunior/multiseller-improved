<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-product" id="ms-submit-button" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $this->url->link('multiseller/category-commission', 'token=' . $this->session->data['token']); ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $ms_transactions_heading; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div style="display: none" class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $ms_transactions_new; ?></h3>
      </div>
      <div class="panel-body">
        <form method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
			<table id="attribute-value" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <td class="text-left"><?php echo $ms_categories; ?></td>
                    <td class="text-right"><?php echo $ms_rate; ?></td>
                    <td></td>
                </tr>
            </thead>

            <tbody>
            <tr class="ffSample">
                <td>
                  <div id="product-category" class="well well-sm" style="height: 150px; overflow: auto;">
					<div class="form-group">
                <label class="col-sm-2 control-label" for="input-category"><span data-toggle="tooltip" title="<?php echo $help_category; ?>"><?php echo $entry_category; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="category" value="" placeholder="<?php echo $entry_category; ?>" id="input-category" class="form-control" />
                  <div id="product-category" class="well well-sm" style="height: 150px; overflow: auto;"></div>
                </div>
              </div>
				  </div>
                </td>

                <td>
                    <input type="text" name="category[0][flat]" value="" size="1" class="form-control" />
					<input type="text" name="category[0][pct]" value="" size="1" class="form-control" />
                </td>

                <td>
                    <button type="button" data-toggle="tooltip" title="<?php echo $ms_delete; ?>" class="ms-button-del btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                </td>
            </tr>

            <?php $attribute_value_row = 1; ?>
            <?php if (isset($category_commissions)) { ?>
            <?php foreach ($category_commissions as $entry) { ?>
            <tr>
                <td>
                 <div class="form-group">
                <label class="col-sm-2 control-label" for="input-category"><span data-toggle="tooltip" title="<?php echo $help_category; ?>"><?php echo $entry_category; ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="category" value="" placeholder="<?php echo $entry_category; ?>" id="input-category" class="form-control" />
                  <div id="product-category" class="well well-sm" style="height: 150px; overflow: auto;">
                    <?php foreach ($entry['categories'] as $product_category) { ?>
                    <div id="product-category<?php echo $product_category['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $product_category['name']; ?>
                      <input type="hidden" name="product_category[]" value="<?php echo $product_category['category_id']; ?>" />
                    </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
                </td>

                <td>
                    <input type="text" name="category[<?php echo $entry['rate_id']; ?>][flat]" value="<?php echo $entry['rate']['flat']; ?>" size="1" class="form-control" />
					<input type="text" name="category[<?php echo $entry['rate_id']; ?>][pct]" value="<?php echo $entry['rate']['flat']; ?>" size="1" class="form-control" />
                </td>

                <td>
                    <button type="button" data-toggle="tooltip" title="<?php echo $ms_delete; ?>" class="ms-button-del btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>
                </td>
            </tr>
            <?php $attribute_value_row++; ?>
            <?php } ?>
            <?php } ?>
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td class="text-left"><button type="button" data-toggle="tooltip" title="<?php echo $ms_add_attribute_value; ?>" class="ffClone btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
                </tr>
            </tfoot>
          </table>
		</form>
      </div>
	</div>
</div>

<script>
$("#ms-submit-button").click(function() {
	var button = $(this);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: 'index.php?route=multiseller/category-commissions/jxSave&token=<?php echo $token; ?>',
		data: $('#form').serialize(),
		beforeSend: function() {
			$('div.text-danger').remove();
            $('.alert-danger').hide().find('i').text('');
		},
		complete: function(jqXHR, textStatus) {
            button.show().prev('span.wait').remove();
            $('.alert-danger').hide().find('i').text('');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			$('.alert-danger').show().find('i').text(textStatus);
		},			
		success: function(jsonData) {
			if (!jQuery.isEmptyObject(jsonData.errors)) {
				for (error in jsonData.errors) {
					$('[name="'+error+'"]').after('<div class="text-danger">' + jsonData.errors[error] + '</div>');
				}
			} else {
				window.location = 'index.php?route=multiseller/category-commissions&token=<?php echo $token; ?>';
			}
		}
	});
});

// Category
$('input[name=\'category\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['category_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'category\']').val('');

		$('#product-category' + item['value']).remove();

		$('#product-category').append('<div id="product-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="product_category[]" value="' + item['value'] + '" /></div>');
	}
});

$('#product-category').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});
</script>
<?php echo $footer; ?> 