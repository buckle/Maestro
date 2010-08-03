<?php
// $Id$

/**
 * @file
 * maestro.moderator.inc
 */

function maestro_outstanding_tasks() {
  global $base_url;

  $maestro_path = '/' . drupal_get_path('module', 'maestro');
  $maestro_url = $base_url . '/' . drupal_get_path('module', 'maestro');

  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');

  $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
  $maestro->engine()->setMode('admin');
  $q = $maestro->engine()->getQueue();

  return theme('maestro_outstanding_tasks', array('maestro_url' => $maestro_url, 'maestro' => $maestro->engine(), 'queue' => $q));
}

?>


