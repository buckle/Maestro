var maestro_structure_cntr=0;

function maestro_saveTemplateName(id, cntr) {
	var frmID = "#maestro_template_save_" + cntr;
	dataString = jQuery(frmID).serialize();
	dataString += "&id=" + id;
	dataString += "&cntr=" + cntr;
	dataString += "&op=savetemplate";
	maestro_structure_cntr = cntr;
	jQuery('#maestro_updating_' + cntr).addClass('maestro_working');
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_saveTemplateNameComplete,
		error : maestro_saveTemplateNameError,
		data : dataString
	});
}

function maestro_saveTemplateNameError(XMLHttpRequest, textStatus, errorThrown) {
	jQuery('#maestro_updating_' + maestro_structure_cntr).removeClass(
			'maestro_working');
	// error somewhere along the way. Probably an error in the JSON/jQuery combo.
	var error = Drupal
			.t('There has been an error.  Please try your save again.');
	jQuery('#maestro_error_message').html(error);
}

function maestro_saveTemplateNameComplete(data) {
	jQuery('#maestro_updating_' + maestro_structure_cntr).removeClass(
			'maestro_working');
	if (data.status == "0") { // query failed
		var error = Drupal
				.t('There has been an error saving your template.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	} else {
		jQuery('#maestro_error_message').html('');
	}

}

function maestro_CreateVariable(id, cntr) {
	var frmID = "#frmVariableAdd_" + cntr;
	dataString = jQuery(frmID).serialize();
	dataString += "&id=" + id;
	dataString += "&cntr=" + cntr;
	dataString += "&op=createvariable";
	maestro_structure_cntr = cntr;
	jQuery('#maestro_variable_updating_' + cntr).addClass('maestro_working');
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_saveNewVariableComplete,
		error : maestro_saveNewVariableError,
		data : dataString
	});
}

function maestro_saveNewVariableError(XMLHttpRequest, textStatus, errorThrown) {
	jQuery('#maestro_variable_updating_' + maestro_structure_cntr).removeClass(
			'maestro_working');
}

function maestro_saveNewVariableComplete(data) {
	jQuery("#newVariableName").attr("value", "");
	jQuery("#newVariableValue").attr("value", "");
	jQuery('#maestro_variable_updating_' + maestro_structure_cntr).removeClass(
			'maestro_working');
	if (data.status == "1") {
		jQuery('#ajaxReplaceTemplateVars').html(data.data);
	} else {
		var error = Drupal
				.t('There has been an error saving your template variable.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
}

function maestro_CancelTemplateVariable(id) {
	dataString = "";
	dataString += "id=" + id;
	dataString += "&op=showvariables";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_saveNewVariableComplete,
		error : maestro_saveNewVariableError,
		data : dataString
	});
}
function maestro_OpenCloseCreateVariable(cntr) {
	jQuery('#variableAdd_' + cntr).toggle();
}

function maestro_saveTemplateVariable(tid, var_id) {
	var name = jQuery('#editVarName_' + var_id).attr("value");
	var val = jQuery('#editVarValue_' + var_id).attr("value");

	dataString = "";
	dataString += "id=" + var_id;
	dataString += "&name=" + name;
	dataString += "&val=" + val;
	dataString += "&op=updatevariable";
	jQuery('#maestro_updating_variable_' + var_id).addClass('maestro_working');
	maestro_structure_cntr = var_id;
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_updateTemplateVariableComplete,
		error : maestro_updateVariableError,
		data : dataString
	});
}

function maestro_updateTemplateVariableComplete(data) {
	jQuery('#maestro_updating_variable_' + data.var_id).removeClass(
			'maestro_working');
	if (data.status == "1") {
		jQuery('#ajaxReplaceTemplateVars').html(data.data);
	} else {
		var error = Drupal
				.t('There has been an error saving your template variable.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
}

function maestro_updateVariableError(XMLHttpRequest, textStatus, errorThrown) {
	jQuery('#maestro_updating_variable_' + maestro_structure_cntr).removeClass(
			'maestro_working');
}

function maestro_deleteTemplateVariable(tid, var_id) {
	var x = confirm(Drupal.t('Delete this variable?'));
	if (x) {
		dataString = "";
		dataString += "id=" + var_id;
		dataString += "&tid=" + tid;
		dataString += "&op=deletevariable";
		jQuery.ajax( {
			type : 'POST',
			cache : false,
			url : ajax_url,
			dataType : "json",
			success : maestro_updateTemplateVariableComplete,
			error : maestro_updateVariableError,
			data : dataString
		});
	} else {
		return false;
	}
}

function maestro_editTemplateVariable(tid, var_id) {
	dataString = "";
	dataString += "id=" + var_id;
	dataString += "&tid=" + tid;
	dataString += "&op=editvariable";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_updateTemplateVariableComplete,
		error : maestro_updateVariableError,
		data : dataString
	});
}
