<?php
// $Id$

/**
 * @file
 * maestro.moderator.inc
 */

function maestro_outstanding_tasks($op='', $qid=0, $other=NULL) {
  global $base_url;

  $maestro_path = '/' . drupal_get_path('module', 'maestro');
  $maestro_url = $base_url . $maestro_path;

  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/outstanding_tasks.js');

  $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
  $m = $maestro->engine();

  switch ($op) {
  case 'reassign':
    $m->reassignTask($qid, $_POST['current_uid'], $_POST['reassign_uid']);
    break;

  case 'email':
    $m->sendTaskReminderNotifications($qid, $other);
    break;

  case 'trace':
    break;

  case 'delete':
    $m->deleteTask($qid);
    break;

  default:
    $maestro->engine()->setMode('admin');
    $q = $m->getQueue();

    $query = db_select('users', 'a');
    $query->fields('a', array('uid', 'name'));
    $query->condition('a.uid', 0, '>');
    $user_res = $query->execute();

    return theme('maestro_outstanding_tasks', array('maestro_url' => $maestro_url, 'maestro' => $m, 'queue' => $q, 'users' => $user_res));
    break;
  }

  drupal_goto('maestro/outstanding');
}

?>


