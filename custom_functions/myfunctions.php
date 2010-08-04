<?php
// $Id:

/**
 * @file
 * myfunctions.php
 */

function maestro_showmessage($op,&$task,$parms) {
  global $base_url;

  $retval = new stdClass();
  $retval->html = '';
  $retval->retcode = FALSE;
  $retval->engineop = '';

  switch ($op) {
    case 'display':
      $retval->html = '<div style="text-align:center;margin:5px;padding:10px;border:1px solid #CCC;font-size:14pt;">';
      $retval->html .= $parms['message'];
      $retval->html .= '<div style="float:right;padding-right;">';
      $retval->html .= '<form style="margin:0px;">';
      $retval->html .= '<div style="font-size:10pt;"><input maestro="complete" type="button" value="Complete Task"></div>';
      $retval->html .= '</form>';
      $retval->html .= '</div>';
      $retval->retcode = TRUE;
      $retval->engineop = '';
      break;
    case 'complete':
      $retval->retcode = TRUE;
      $retval->engineop = 'completetask';
      break;
  }

  return $retval;

}


function maestro_basicformtest($op,&$task,$parms) {
  global $base_url;

  $retval = new stdClass();
  $retval->html = '';
  $retval->retcode = FALSE;
  $retval->engineop = '';  // Optionally set the engineop value if you want to complete the task

  switch ($op) {
    case 'display':
      $data = $task->getTempData();
      $retval->html = '<div style="margin:5px;padding:10px;border:1px solid #CCC;">';
      $retval->html .= '<form style="margin:0px;">';
      $retval->html .= '<div style="float:left;width:75%;">';
      $retval->html .= '<div>This is a basic form test which would display some message here</div>';
      $retval->html .= '<p><label>What is your name: </label><input type="text" name="name" value="'.$data['name'] .'"></p>';
      $retval->html .= '<p><label>What is your company name: </label><input type="text" name="company" value="'.$data['company'] .'"></p>';
      $retval->html .= '</div>';
      $retval->html .= '<div style="float:right;width:25%;white-space:nowrap">';
      $retval->html .= '<span style="float:right;"><input maestro="complete" type="button" value="Complete Task"></span>';
      $retval->html .= '<span style="float:right;"><input maestro="save" type="button" value="Save"></span>';
      $retval->html .= '</div>';
      $retval->html .= '</form>';
      $retval->html .= '<div style="clear:both;"></div>';
      $retval->html .= '</div>';
      $retval->retcode = TRUE;
      $retval->engineop = '';
      break;
    case 'complete':
      $retval->retcode = TRUE;
      $retval->engineop = 'completetask';
      break;
    case 'save':
      $data['name'] = $_POST['name'];
      $data['company'] = $_POST['company'];
      $task->saveTempData($data);
      $retval->retcode = TRUE;
      break;
  }

  return $retval;

}


function maestro_editChangeRequest($op,&$task,$parms) {
  global $base_url;

  $nid = db_query("SELECT nid FROM {maestro_project_content} WHERE process_id = :pid AND content_type = :type",
     array(':pid' => $task->_properties->process_id, ':type' => $parms['content_type']))->fetchField();

  $node = node_load($nid);
  $retval = new stdClass();
  $retval->html = '';
  $retval->retcode = FALSE;
  $retval->engineop = '';  // Optionally set the engineop value if you want to complete the task

  switch ($op) {
    case 'display':
      $data = $task->getTempData();
      $radio1opt = '';
      $radio2opt = '';
      if ($data['reviewstatus'] == 'accept') {
        $radio2opt = 'CHECKED=checked';
      } elseif ($data['reviewstatus'] == 'reject') {
        $radio1opt = 'CHECKED=checked';
      }
      $retval->html = '<div style="margin:5px;padding:10px;border:1px solid #CCC;">';
      $retval->html .= '<form style="margin:0px;">';
      $retval->html .= '<div style="float:left;width:75%;">';
      $retval->html .= '<div>You have a task to Review and Edit the Change Request titled <a href="'.$base_url.'/node/'.$nid.'/edit/maestro/edit/'. $task->_properties->queue_id .'/">'.$node->title .'</a>.</div>';
      $retval->html .= '</div>';
      $retval->html .= '<div style="float:right;width:25%;white-space:nowrap">';
      $retval->html .= '<span style="float:right;padding-left:5px;"><input maestro="complete" type="button" value="Complete Task"></span>';
      $retval->html .= '<span style="float:right;"><input maestro="update" type="button" value="Update"></span>';
      $retval->html .= '</div>';
      $retval->html .= '<div style="padding-top:20px;">Do you accept this document?&nbsp;';
      $retval->html .= '<input type="radio" name="reviewstatus" value="no" '.$radio1opt .'>No';
      $retval->html .= '<span style="padding-left:10px;"><input type="radio" name="reviewstatus" value="yes" '.$radio2opt .'>Yes</span>';
      $retval->html .= '</div>';
      $retval->html .= '</form>';
      $retval->html .= '<div style="clear:both;"></div>';
      $retval->html .= '</div>';
      $retval->retcode = TRUE;
      $retval->engineop = '';
      break;
    case 'complete':
      $data = $task->getTempData();
      if (empty($data['reviewstatus']) AND !isset($_POST['reviewstatus'])) {
        $retval->retcode = FALSE;
      } elseif ($data['reviewstatus'] == 'accept' OR $_POST['reviewstatus'] == 'yes') {
        $data['reviewstatus'] = 'accept';
        $task->saveTempData($data);
        $retval->retcode = TRUE;
        $retval->status = MaestroTaskStatusCodes::STATUS_COMPLETE;
        $retval->engineop = 'completetask';
      } elseif ($data['reviewstatus'] == 'reject' OR $_POST['reviewstatus'] == 'no') {
        $data['reviewstatus'] = 'reject';
        $task->saveTempData($data);
        $retval->retcode = TRUE;
        $retval->status = MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE;
        $retval->engineop = 'completetask';
      } else {
        $retval->retcode = FALSE;
      }
      break;
    case 'update':
      if($_POST['reviewstatus'] == 'yes') {
        $data['reviewstatus'] = 'accept';
        $task->saveTempData($data);
      } elseif ($_POST['reviewstatus'] == 'no') {
        $data['reviewstatus'] = 'reject';
        $task->saveTempData($data);
      }
      $retval->retcode = TRUE;
      break;
  }

  return $retval;

}


function maestro_reviewContentType($op,&$task,$parms) {
  global $base_url;
 
  $id = db_select('maestro_projects','a')
    ->fields('a',array('id'))
    ->condition('process_id', $task->_properties->process_id, '=')
    ->execute()->fetchField();

  $nid = db_query("SELECT nid FROM {maestro_project_content} WHERE project_id = :pid AND content_type = :type",
     array(':pid' => $id, ':type' => $parms['content_type']))->fetchField();

  $node = node_load($nid);
  if ($node === FALSE) {
    $retval->retcode = FALSE;
    return $retval;
  }
  $retval = new stdClass();
  $retval->html = '';
  $retval->retcode = FALSE;
  $retval->engineop = '';  // Optionally set the engineop value if you want to complete the task

  switch ($op) {
    case 'display':
      $data = $task->getTempData();
      $radio1opt = '';
      $radio2opt = '';
      if ($data['reviewstatus'] == 'accept') {
        $radio2opt = 'CHECKED=checked';
      } elseif ($data['reviewstatus'] == 'reject') {
        $radio1opt = 'CHECKED=checked';
      }
      $retval->html = '<div style="margin:5px;padding:10px;border:1px solid #CCC;">';
      $retval->html .= '<form style="margin:0px;">';
      $retval->html .= '<div style="float:left;width:75%;">';
      $retval->html .= '<div>You have a task to Review and Edit the Change Request titled <a href="'.$base_url.'/node/'.$nid.'/edit/maestro/edit/'. $task->_properties->queue_id .'/">'.$node->title .'</a>.</div>';
      $retval->html .= '</div>';
      $retval->html .= '<div style="float:right;width:25%;white-space:nowrap">';
      $retval->html .= '<span style="float:right;padding-left:5px;"><input maestro="complete" type="button" value="Complete Task"></span>';
      $retval->html .= '<span style="float:right;"><input maestro="update" type="button" value="Update"></span>';
      $retval->html .= '</div>';
      $retval->html .= '<div style="padding-top:20px;">Do you accept this document?&nbsp;';
      $retval->html .= '<input type="radio" name="reviewstatus" value="no" '.$radio1opt .'>No';
      $retval->html .= '<span style="padding-left:10px;"><input type="radio" name="reviewstatus" value="yes" '.$radio2opt .'>Yes</span>';
      $retval->html .= '</div>';
      $retval->html .= '</form>';
      $retval->html .= '<div style="clear:both;"></div>';
      $retval->html .= '</div>';
      $retval->retcode = TRUE;
      $retval->engineop = '';
      break;
    case 'complete':
      $data = $task->getTempData();
      if (empty($data['reviewstatus']) AND !isset($_POST['reviewstatus'])) {
        $retval->retcode = FALSE;
      } elseif ($data['reviewstatus'] == 'accept' OR $_POST['reviewstatus'] == 'yes') {
        $data['reviewstatus'] = 'accept';
        $task->saveTempData($data);
        $retval->retcode = TRUE;
        $retval->status = MaestroTaskStatusCodes::STATUS_COMPLETE;
        $retval->engineop = 'completetask';
      } elseif ($data['reviewstatus'] == 'reject' OR $_POST['reviewstatus'] == 'no') {
        $data['reviewstatus'] = 'reject';
        $task->saveTempData($data);
        $retval->retcode = TRUE;
        $retval->status = MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE;
        $retval->engineop = 'completetask';
      } else {
        $retval->retcode = FALSE;
      }
      break;
    case 'update':
      if($_POST['reviewstatus'] == 'yes') {
        $data['reviewstatus'] = 'accept';
        $task->saveTempData($data);
      } elseif ($_POST['reviewstatus'] == 'no') {
        $data['reviewstatus'] = 'reject';
        $task->saveTempData($data);
      }
      $retval->retcode = TRUE;
      break;
  }

  return $retval;

}



?>
