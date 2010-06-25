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
  include_once './' . drupal_get_path('module', 'maestro') . '/maestro_interface.class.php';
  $mi = new MaestroInterface($tid);
  return $mi->displayPage();
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