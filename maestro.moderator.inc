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

  if ($process_id > 0) {
    $res = db_select('maestro_process', 'a')
      ->fields('a', array('tracking_id'))
      ->condition('a.id', $process_id, '=')
      ->execute();
    $tracking_id = current($res->fetchAll())->tracking_id;
  }

  $initiating_pid = $m->getParentProcessId($process_id);

  $properties = new stdClass();
  $properties->queue_id = $queue_id;
  $properties->process_id = $process_id;
  $properties->tracking_id = $tracking_id;
  $properties->initiating_pid = $initiating_pid;


  $query = db_select('maestro_process', 'a');
  $query->fields('b', array('variable_value', 'process_id', 'id'));
  $query->fields('c', array('variable_name'));
  $query->leftJoin('maestro_process_variables', 'b', 'a.id=b.process_id');
  $query->leftJoin('maestro_template_variables', 'c', 'b.template_variable_id=c.id');
  $query->condition('a.initiating_pid', $initiating_pid, '=');
  $query->orderBy('a.id', 'ASC');
  $query->orderBy('c.id', 'ASC');
  $pv_res = $query->execute();

  $query = db_select('maestro_process', 'a');
  $query->fields('a', array('id'));
  $query->fields('b', array('template_name'));
  $query->leftJoin('maestro_template', 'b', 'a.template_id=b.id');
  $query->condition('a.initiating_pid', $initiating_pid, '=');
  $proc_res = $query->execute();

  $refl = new ReflectionClass('MaestroTaskStatusCodes');
  $task_status_code_array = $refl->getConstants();
  $statuses = array();
  foreach ($task_status_code_array as $var => $value) {
    $statuses[$value] = ucwords(strtolower(str_replace('_', ' ', substr($var, 7))));
  }

  $wf_res = $m->getRelatedWorkflows($tracking_id);
  $trace = $m->getQueueHistory($initiating_pid);

  return theme('maestro_trace', array('maestro_url' => $maestro_url, 'trace' => $trace, 'properties' => $properties, 'pv_res' => $pv_res, 'proc_res' => $proc_res, 'wf_res' => $wf_res, 'statuses' => $statuses));
}

?>


