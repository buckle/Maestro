<?php
// $Id:

/**
 * @file
 * maestro.admin.inc
 */


function maestro_admin() {
  return theme('maestro_workflow_list', array());
}

function maestro_edit_workflow($tid) {
  global $base_url;

  $maestro_path = '/' . drupal_get_path('module', 'maestro');

  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/wz_jsgraphics.js');
  //drupal_add_js($base_url . $maestro_path . '/js/workflowadmin.js');
  drupal_add_js($base_url . $maestro_path . '/js/admin_template_editor.js');

  include_once './' . drupal_get_path('module', 'maestro') . '/maestro_interface.class.php';
  include_once './' . drupal_get_path('module', 'maestro') . '/maestro_task_interface.class.php';

  $mi = new MaestroInterface($tid);

  return $mi->displayPage();
}

function maestro_handle_ajax_request($task_type, $op, $properties=NULL) {
  $ti = new $task_type();
  $ti->$op($properties);
}


function maestro_configure() {
  return drupal_get_form('maestro_admin_form');
}

function maestro_admin_form() {

  $form['maestro_batch_script_location'] = array(
    '#type' => 'textfield',
    '#title' => t('Maestro batch script physical file location'),
    '#default_value' => variable_get('maestro_batch_script_location', drupal_get_path('module','maestro') . "/batch/"),
    '#size' => 100,
    '#description' => t('This is the physical directory where the batch scripts are stored.
          It is highly recommended that batch scripts be placed in a directory outside of web root.'),
    '#required' => TRUE,
  );


  return system_settings_form($form);


}