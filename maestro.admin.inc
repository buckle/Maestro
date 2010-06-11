<?php
// $Id:

/**
 * @file
 * maestro.admin.inc
 */


function maestro_admin() {
  global $base_url;
echo $base_url . '/' . drupal_get_path('module', 'maestro') . '/maestro.css';
  drupal_add_css($base_url . '/' . drupal_get_path('module', 'maestro') . '/maestro.css');

  return theme('maestro_workflow_list', array());
}

function maestro_edit_workflow($id) {
  return theme('maestro_workflow_edit', array('id' => $id));
}

