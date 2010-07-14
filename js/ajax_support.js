var maestro_structure_cntr=0;

function maestro_saveTemplateName(id, cntr) {
	maestro_hideErrorBar();
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
	maestro_showErrorBar();
	jQuery('#maestro_updating_' + maestro_structure_cntr).removeClass('maestro_working');
	var error = Drupal.t('There has been an error.  Please try your save again.');
	jQuery('#maestro_error_message').html(error);
}

function maestro_saveTemplateNameComplete(data) {
	jQuery('#maestro_updating_' + maestro_structure_cntr).removeClass('maestro_working');
	if (data.status == "0") { // query failed
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error saving your template.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	} else {
		maestro_hideErrorBar();
		jQuery('#maestro_error_message').html('');
	}

}

function maestro_CreateVariable(id, cntr) {
	maestro_hideErrorBar();
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
	maestro_showErrorBar();
	jQuery('#maestro_variable_updating_' + maestro_structure_cntr).removeClass(
			'maestro_working');
}

function maestro_saveNewVariableComplete(data) {
	jQuery("#newVariableName").attr("value", "");
	jQuery("#newVariableValue").attr("value", "");
	jQuery('#maestro_variable_updating_' + maestro_structure_cntr).removeClass('maestro_working');
	if (data.status == "1") {
		maestro_hideErrorBar();
		jQuery('#ajaxReplaceTemplateVars_' + data.cntr).html(data.data);
	} else {
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error saving your template variable.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
}

function maestro_CancelTemplateVariable(id) {
	maestro_hideErrorBar();
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
	maestro_hideErrorBar();
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
		maestro_hideErrorBar();
		jQuery('#ajaxReplaceTemplateVars_' + data.cntr).html(data.data);
	} else {
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error saving your template variable.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
}

function maestro_updateVariableError(XMLHttpRequest, textStatus, errorThrown) {
	maestro_showErrorBar();
	jQuery('#maestro_updating_variable_' + maestro_structure_cntr).removeClass(
			'maestro_working');
}

function maestro_deleteTemplateVariable(tid, var_id, cntr) {
	maestro_hideErrorBar();
	var x = confirm(Drupal.t('Delete this variable?'));
	if (x) {
		dataString = "";
		dataString += "id=" + var_id;
		dataString += "&tid=" + tid;
		dataString += "&cntr=" + cntr;
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
	maestro_hideErrorBar();
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

function maestro_CreateTemplate() {
	maestro_hideErrorBar();
	jQuery('#maestro_new_template_updating').addClass('maestro_working');
	var name = jQuery('#newTemplateName').attr("value");
	dataString = "";
	dataString += "name=" + name;
	dataString += "&op=createtemplate";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_createTemplateComplete,
		error : maestro_createTemplateError,
		data : dataString
	});
}

function maestro_createTemplateComplete(data) {
	jQuery('#maestro_new_template_updating').removeClass('maestro_working');
	if (data.status == "1") {
		maestro_hideErrorBar();
		jQuery('#maestro_template_admin').html(data.data);
	} else {
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error saving your template.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
}

function maestro_createTemplateError(XMLHttpRequest, textStatus, errorThrown) {
	jQuery('#maestro_new_template_updating').removeClass('maestro_working');
}


function maestro_CreateAppgroup() {
	maestro_hideErrorBar();
	jQuery('#maestro_new_appgroup_updating').addClass('maestro_working');
	var name = jQuery('#appGroupName').attr("value");
	dataString = "";
	dataString += "name=" + name;
	dataString += "&op=createappgroup";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_createAppgroupComplete,
		error : maestro_createAppgroupError,
		data : dataString
	});
}

function maestro_createAppgroupComplete(data) {
	jQuery('#maestro_new_appgroup_updating').removeClass('maestro_working');
	jQuery('#appGroupName').attr("value","");
	if (data.status == "0") {
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error saving your App Group.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
	else {
		maestro_hideErrorBar();
		maestro_refreshAppGroup('deleteAppGroup');
	}
}

function maestro_createAppgroupError(XMLHttpRequest, textStatus, errorThrown) {
	jQuery('#maestro_new_appgroup_updating').removeClass('maestro_working');
	
}

function maestro_refreshAppGroup(which) {
	maestro_hideErrorBar();
	dataString = "";
	dataString += "id=" + name;
	dataString += "&which=" + which;
	dataString += "&op=refreshappgroup";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_deleteAppgroupComplete,
		data : dataString
	});
}

function maestro_DeleteAppgroup() {
	maestro_hideErrorBar();
	jQuery('#maestro_del_appgroup_updating').addClass('maestro_working');
	var name = jQuery('#deleteAppGroup').attr("value");
	dataString = "";
	dataString += "id=" + name;
	dataString += "&op=deleteappgroup";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_deleteAppgroupComplete,
		error : maestro_createAppgroupError,
		data : dataString
	});
}


function maestro_deleteAppgroupComplete(data) {
	jQuery('#maestro_del_appgroup_updating').removeClass('maestro_working');
	if (data.status == "1") {
		maestro_hideErrorBar();
		jQuery('#replaceDeleteAppGroup').html(data.data);
	} else {
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error deleting your app gropu.  Please try your delete again.');
		jQuery('#maestro_error_message').html(error);
		
	}
}

function maestro_deleteTemplate(tid) {
	maestro_hideErrorBar();
	var x = confirm(Drupal.t('Delete this template?'));
	if (x) {
		dataString = "";
		dataString += "id=" + tid;
		dataString += "&op=deletetemplate";
		jQuery.ajax( {
			type : 'POST',
			cache : false,
			url : ajax_url,
			dataType : "json",
			success : maestro_deleteTemplateComplete,
			data : dataString
		});
	} else {
		return false;
	}
}

function maestro_deleteTemplateComplete(data) {
	if (data.status == "1") {
		maestro_hideErrorBar();
		jQuery('#maestro_template_admin').html(data.data);
	} else {
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error deleting your template.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
}


function maestro_copyTemplate(tid) {
	maestro_hideErrorBar();
	dataString = "";
	dataString += "id=" + tid;
	dataString += "&op=copytemplate";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : maestro_copyTemplateComplete,
		data : dataString
	});
}


function maestro_copyTemplateComplete(data) {
	if (data.status == "1") {
		maestro_hideErrorBar();
		jQuery('#maestro_template_admin').html(data.data);
	} else {
		maestro_showErrorBar();
		var error = Drupal.t('There has been an error copying your template.  Please try your save again.');
		jQuery('#maestro_error_message').html(error);
	}
}


function maestro_showErrorBar() {
	jQuery('#maestro_error_row').removeClass('maestro_hide_error_bar');
	jQuery('#maestro_error_row').addClass('maestro_show_error_bar');
}
function maestro_hideErrorBar() {
	var error = '';
	jQuery('#maestro_error_message').html(error);
	jQuery('#maestro_error_row').removeClass('maestro_show_error_bar');
	jQuery('#maestro_error_row').addClass('maestro_hide_error_bar');
}

