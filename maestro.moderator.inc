<?php
// $Id$

/**
 * @file
 * maestro.moderator.inc
 */

function maestro_outstanding_tasks() {
  $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
  $maestro->engine()->setMode('admin');
  $q = $maestro->engine()->getQueue();

  return theme('maestro_outstanding_tasks', array('maestro' => $maestro->engine(), 'queue' => $q));
}

?>


