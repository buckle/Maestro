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
  drupal_add_js($base_url . $maestro_path . '/js/moderator.js');

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

function maestro_trace($tracking_id=0, $process_id=0, $queue_id=0) {
  global $base_url;

  $maestro_path = '/' . drupal_get_path('module', 'maestro');
  $maestro_url = $base_url . $maestro_path;

  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/moderator.js');

  $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
  $m = $maestro->engine();

  if ($process_id == 0 && $queue_id > 0) {
    $res = db_select('maestro_queue', 'a')
      ->fields('a', array('process_id'))
      ->condition('a.id', $queue_id, '=')
      ->execute();
    $process_id = current($res->fetchAll())->process_id;
  }

  if ($tracking_id == 0) {
    $res = db_select('maestro_process', 'a')
      ->fields('a', array('tracking_id'))
      ->condition('a.id', $process_id, '=')
      ->execute();
    $tracking_id = current($res->fetchAll())->tracking_id;
  }

  $properties = new stdClass();
  $properties->queue_id = $queue_id;
  $properties->process_id = $process_id;
  $properties->tracking_id = $tracking_id;


  $query = db_select('maestro_process_variables', 'a');
  $query->fields('a', array('variable_value'));
  $query->fields('b', array('variable_name'));
  $query->leftJoin('maestro_template_variables', 'b', 'a.template_variable_id=b.id');
  $query->condition('a.process_id', $process_id, '=');
  $pv_res = $query->execute();

  $trace = $m->getQueueHistory($process_id);

  return theme('maestro_trace', array('maestro_url' => $maestro_url, 'trace' => $trace, 'properties' => $properties, 'pv_res' => $pv_res));
}

?>


