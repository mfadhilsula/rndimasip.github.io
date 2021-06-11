$(function() {
//	$('input[name="inst_date[]"]').datepicker({"dateFormat": 'yy-mm-dd'});
	$('input[name="inst_date[]"]').datepick({dateFormat: 'yyyy-mm-dd', yearRange: 'c-500:c+20',
		renderer: $.datepick.themeRollerRenderer, pickerClass: 'ui-datepicker'});
	
	$('input[name="instantiation_id_source[]"]').autocomplete({
		source: site_url + "autocomplete/values?table=instantiation_identifier&column=instantiation_source",
		minLength: 1,
		delay: 300,
		enable: true,
		cacheLength: 1


	});
	$("#language").autocomplete({
		source: site_url + "autocomplete/values?table=language_lookup&column=Print_Name",
		minLength: 0,
		delay: 100,
		enable: true,
		cacheLength: 3


	});
	$('input[name="annotation_type[]"]').autocomplete({
		source: site_url + "autocomplete/values?table=instantiation_annotations&column=annotation_type",
		minLength: 1,
		delay: 100,
		enable: true,
		cacheLength: 1
	});
	$('input[name="relation_identifier[]"]').autocomplete({
		source: site_url + "autocomplete/values?table=instantiation_relations&column=relation_identifier",
		minLength: 1,
		delay: 100,
		enable: true,
		cacheLength: 1
	});
	$('input[name="relation_source[]"]').autocomplete({
		source: site_url + "autocomplete/values?table=relation_types&column=relation_type_source",
		minLength: 1,
		delay: 100,
		enable: true,
		cacheLength: 1
	});
});
function removeElement(elementID, type) {
	$(elementID).delay(200).fadeOut(1000);
	$(elementID).animate({
		"opacity": "0",
	}, {
		"complete": function() {
			$(elementID).remove();
			if ($('.remove_' + type).length == 0) {
				$('#add_' + type).html(' ADD ' + type.replace(/_/g, " ").toUpperCase());
			}
			else {
				$('#add_' + type).html(' ADD ANOTHER ' + type.replace(/_/g, " ").toUpperCase());
			}
		}
	});
}
function addElement(elementID, type) {
	var number = 10 + Math.floor(Math.random() * 100);
	common = less = false;
	if (elementID == '#main_instantiation_id') {
		append = '';
		if (disable == 1) {
			append = '<div><p>Instantiation ID: <span class="label_star"> *</span> </p><p><input type="text" id="instantiation_id_identifier_' + number + '" name="instantiation_id_identifier[]" value="" />' +
			'<span class="help-block" style="display:none;">Instantiation ID is required.</span></p></div>';
		}

		html = '<div id="remove_instantiation_id_' + number + '" class="remove_instantiation_id"><div class="edit_form_div ins_edit_div">' +
		'<input type="hidden" id="instantiation_id_identifier_' + number + '" name="instantiation_id_identifier_id[]" value="" />' + append +
		'<div><p>Instantiation ID Source: <span class="label_star"> *</span> </p><p>' +
		'<input type="text" id="instantiation_id_source_' + number + '" name="instantiation_id_source[]" value="" />' +
		'<span class="help-block" style="display:none;">Instantiation ID Source is required.</span>' +
		'</p></div></div>' +
		'<div class="remove_element" onclick="removeElement(\'#remove_instantiation_id_' + number + '\', \'instantiation_id\');"><img src="/images/remove-item.png" /></div>' +
		'<div class="clearfix" style="margin-bottom: 10px;"></div></div>';


		$(elementID).append(html);
		$('input[name="instantiation_id_source[]"]').autocomplete({
			source: site_url + "autocomplete/values?table=instantiation_identifier&column=instantiation_source",
			minLength: 1,
			delay: 300,
			enable: true,
			cacheLength: 1


		});
	}
	else if (elementID == '#main_dimension') {
		html = '<div id="remove_dimension_' + number + '" class="remove_dimension"><div class="edit_form_div ins_edit_div"><div><p>Dimension:</p>' +
		'<p><input type="text" id="dimension_' + number + '" name="asset_dimension[]" value="" /></p>' +
		'</div><div><p>Unit of measure:</p><p>' +
		'<input type="text" id="dimension_unit_' + number + '" name="dimension_unit[]" value="" /></p></div></div>' +
		'<div class="remove_element" onclick="removeElement(\'#remove_dimension_' + number + '\', \'dimension\');"><img src="/images/remove-item.png" /></div>' +
		'<div class="clearfix" style="margin-bottom: 10px;"></div></div>';
		$(elementID).append(html);
	}
	else if (elementID == '#main_generation') {
		generationTypes = '<option value="">Select</option>';

		for (cnt in pbcoreGeneration)
		{
			if (pbcoreGeneration[cnt]['display_value'] == 1 && common == false) {
				common = true;
				generationTypes += '<optgroup label="Commonly Used">Commonly Used</optgroup>';
			}
			else if (pbcoreGeneration[cnt]['display_value'] == 2 && less == false) {
				less = true;
				generationTypes += '<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>';
			}
			generationTypes += '<option value= "' + pbcoreGeneration[cnt]['value'] + '">' + pbcoreGeneration[cnt]['value'] + '</option>';
		}
		html = '<div id="remove_generation_' + number + '" class="remove_generation"><div class="edit_form_div ins_edit_div"><div><p>Generation:</p></div>' +
		'<div><p><select id="generation_' + number + '" name="generation[]">' + generationTypes + '</select></p></div></div>' +
		'<div class="remove_element" onclick="removeElement(\'#remove_generation_' + number + '\', \'generation\');"><img src="/images/remove-item.png" /></div>' +
		'<div class="clearfix" style="margin-bottom: 10px;"></div></div>';
		$(elementID).append(html);
	}
	else if (elementID == '#main_annotation') {
		html = '<div id="remove_annotation_' + number + '" class="remove_annotation"><div class="edit_form_div ins_edit_div"><div><p>Annotation:</p>' +
		'<p><input type="text" id="annotation_' + number + '" name="annotation[]" value="" /></p></div>' +
		'<div><p>Annotation Type:</p><p><input type="text" id="annotation_type_' + number + '" name="annotation_type[]" value="" />' +
		'</p></div></div>' +
		'<div class="remove_element" onclick="removeElement(\'#remove_annotation_' + number + '\', \'annotation\');"><img src="/images/remove-item.png" /></div>' +
		'<div class="clearfix" style="margin-bottom: 10px;"></div></div>';
		$(elementID).append(html);
		$('input[name="annotation_type[]"]').autocomplete({
			source: site_url + "autocomplete/values?table=instantiation_annotations&column=annotation_type",
			minLength: 1,
			delay: 100,
			enable: true,
			cacheLength: 1
		});
	}
	else if (elementID == '#main_relation') {
		relationType = '<option value="">Select</option>';

		for (cnt in pbcoreRelationTypes)
		{
			if (pbcoreRelationTypes[cnt]['display_value'] == 1 && common == false) {
				common = true;
				relationType += '<optgroup label="Commonly Used">Commonly Used</optgroup>';
			}
			else if (pbcoreRelationTypes[cnt]['display_value'] == 2 && less == false) {
				less = true;
				relationType += '<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>';
			}
			relationType += '<option value= "' + pbcoreRelationTypes[cnt]['value'] + '">' + pbcoreRelationTypes[cnt]['value'] + '</option>';
		}
		html = '<div id="remove_relation_' + number + '" class="remove_relation"><div class="edit_form_div ins_edit_div"><div><p>Relation:</p>' +
		'<p><input type="text" id="relation_' + number + '" name="relation[]" value="" /></p></div>' +
		'<div><p> Relation Type:</p><p><select id="relation_type_' + number + '" name="relation_type[]">' + relationType + '</select>' +
		'</p></div><div><p> Relation Source:</p><p>' +
		'<input type="text" id="relation_source_' + number + '" name="relation_source[]" value="" />' +
		'</p></div><div><p> Relation Ref:</p><p>' +
		'<input type="text" id="relation_ref_' + number + '" name="relation_ref[]" value="" />' +
		'<span class="help-block">Must be a valid URI/URL (e.g. http://www.example.com)</span></p></div></div>' +
		'<div class="remove_element" onclick="removeElement(\'#remove_relation_' + number + '\', \'relation\');"><img src="/images/remove-item.png" /></div>' +
		'<div class="clearfix" style="margin-bottom: 10px;"></div></div>';
		$(elementID).append(html);
		$('input[name="relation_identifier[]"]').autocomplete({
			source: site_url + "autocomplete/values?table=instantiation_relations&column=relation_identifier",
			minLength: 1,
			delay: 100,
			enable: true,
			cacheLength: 1
		});
		$('input[name="relation_source[]"]').autocomplete({
			source: site_url + "autocomplete/values?table=relation_types&column=relation_type_source",
			minLength: 1,
			delay: 100,
			enable: true,
			cacheLength: 1
		});
	}
	else if (elementID == '#main_instantiation_date') {
		dateTypes = '<option value="">Select</option>';

		for (cnt in pbcoreDateTypes)
		{
			if (pbcoreDateTypes[cnt]['display_value'] == 1 && common == false) {
				common = true;
				dateTypes += '<optgroup label="Commonly Used">Commonly Used</optgroup>';
			}
			else if (pbcoreDateTypes[cnt]['display_value'] == 2 && less == false) {
				less = true;
				dateTypes += '<optgroup label="Less Commonly Used">Less Commonly Used</optgroup>';
			}
			dateTypes += '<option value= "' + pbcoreDateTypes[cnt]['value'] + '">' + pbcoreDateTypes[cnt]['value'] + '</option>';
		}
		html = '<div id="remove_instantiation_date_' + number + '" class="remove_instantiation_date"><div class="edit_form_div ins_edit_div">' +
		'<div><p>Instantiation ID:</p><p><input readonly="readonly" type="text" id="inst_date_' + number + '" name="inst_date[]" value="" /></p>' +
		'</div><div><p>Instantiation ID Source:</p><p><select id="inst_date_type_' + number + '" name="inst_date_type[]">' + dateTypes + '</select>' +
		'</p></div></div><div class="remove_element" onclick="removeElement(\'#remove_instantiation_date_' + number + '\', \'instantiation_date\');"><img src="/images/remove-item.png" /></div>' +
		'<div class="clearfix" style="margin-bottom: 10px;"></div></div>';
		$(elementID).append(html);
//		$('input[name="inst_date[]"]').datepicker({"dateFormat": 'yy-mm-dd'});
$('input[name="inst_date[]"]').datepick({dateFormat: 'yyyy-mm-dd', yearRange: 'c-500:c+20',
		renderer: $.datepick.themeRollerRenderer, pickerClass: 'ui-datepicker'});
	}

	if ($('.remove_' + type).length == 0) {
		$('#add_' + type).html(' ADD ' + type.replace(/_/g, " ").toUpperCase());
	}
	else {
		$('#add_' + type).html(' ADD ANOTHER ' + type.replace(/_/g, " ").toUpperCase());
	}
}

function validateForm() {
	var isValid = true;
	var identifer = new Array('instantiation_id_identifier', 'instantiation_id_source');
	var time = new Array('time_start', 'projected_duration');


	for (cnt in identifer) {


		$('input[name="' + identifer[cnt] + '[]"]').each(function() {
			if (isValid && $(this).length > 0) {
				if ($(this).val() == '') {
					isValid = false;
					$(this).nextAll('.help-block').show();
					$(this).parent().parent().addClass('error-div');
					$('body').animate({
						scrollTop: $(this).parent().parent().offset().top - 100
					}, 'slow');
				}
				else {

					$(this).nextAll('.help-block').hide();
					$(this).parent().parent().removeClass('error-div');

				}
			}
		});
	}
	if (isValid) {
		if ($('#physical_format').val() == '') {
			isValid = false;
			$('#physical_format_error').show();
			$('body').animate({
				scrollTop: $('#physical_format_error').offset().top - 100
			}, 'slow');
		}
		else {
			$('#physical_format_error').hide();
		}

	}
	if (isValid) {
		if ($('#media_type').val() == '') {
			$('#media_type_error').show();
			isValid = false;
			$('body').animate({
				scrollTop: $('#media_type_error').offset().top - 100
			}, 'slow');
		}
		else {
			$('#media_type_error').hide();
		}

	}

	if (isValid) {
		var location = new Array('location');
		for (cnt in location) {
			if ($('#' + location[cnt]).length > 0) {
				if ($('#' + location[cnt]).val() == '') {
					isValid = false;
					$('#' + location[cnt] + '_error').show();
					$('body').animate({
						scrollTop: $('#' + location[cnt]).offset().top - 100
					}, 'slow');
					break;
				}
				else {
					$('#' + location[cnt] + '_error').hide();
				}
			}
		}
	}
	if (isValid) {
		var is_numeric = new Array('frame_rate', 'width', 'height');
		for (cnt in is_numeric) {
			if ($('#' + is_numeric[cnt]).length > 0) {
				if ($('#' + is_numeric[cnt]).val() != '') {
					if (isNaN($('#' + is_numeric[cnt]).val())) {
						isValid = false;
						$('#' + is_numeric[cnt] + '_error').show();
						$('body').animate({
							scrollTop: $('#' + is_numeric[cnt]).offset().top - 100
						}, 'slow');
						break;
					}
					else {
						$('#' + is_numeric[cnt] + '_error').hide();
					}
				}
			}
		}
	}
	if (isValid) {
		for (cnt in time) {
			if ($('#' + time[cnt]).length > 0) {
				value = $('#' + time[cnt]).val();
				if (value != '') {
					duration = value.split(':');
					if (duration.length == 3) {
						if (isNaN(duration[0]) || isNaN(duration[1]) || isNaN(duration[2])) {
							isValid = false;
							$('#' + time[cnt] + '_error').show();
							$('body').animate({
								scrollTop: $('#' + time[cnt]).offset().top - 100
							}, 'slow');
							break;

						}
						else {
							if (duration[1] > 59 || duration[2] > 59) {
								isValid = false;
								$('#' + time[cnt] + '_error').show();
								$('body').animate({
									scrollTop: $('#' + time[cnt]).offset().top - 100
								}, 'slow');
								break;
							}
							else {
								$('#' + time[cnt] + '_error').hide();
							}
						}

					}
					else {
						isValid = false;
						$('#' + time[cnt] + '_error').show();
						$('body').animate({
							scrollTop: $('#' + time[cnt]).offset().top - 100
						}, 'slow');
						break;

					}

				}
			}
		}
	}
	if (isValid) {
		$('input[name="relation_ref[]"]').each(function() {
			var urlregex = new RegExp(
			"^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.){1}([0-9A-Za-z]+\.)");
			if ($(this).val() != '') {
				if (!urlregex.test($(this).val())) {

					$('body').animate({
						scrollTop: $(this).parent().parent().offset().top - 100

					}, 'slow');
					$(this).parent().parent().addClass('error-div');
					isValid = false;
					return false;

				}
				else {
					$(this).parent().parent().removeClass('error-div');
				}
			}

		});
	}
	if (isValid)
		$('#edit_instantiation_form').submit();

}