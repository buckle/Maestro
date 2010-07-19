<?php
// $Id:

/**
 * @file
 * myfunctions.php
 */

function maestro_showmessage($op,$properties,$msg) {
  global $base_url;

  $retval = new stdClass();
  $retval->html = '';
  $retval->retcode = FALSE;
  $retval->engineop = '';

  switch ($op) {
    case 'display':
      $retval->html = '<div style="text-align:center;margin:5px;padding:10px;border:1px solid #CCC;font-size:14pt;">';
      $retval->html .= $msg;
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


function maestro_basicformtest($op,$properties,$msg) {
  global $base_url;

  $retval = new stdClass();
  $retval->html = '';
  $retval->retcode = FALSE;
  $retval->engineop = '';

  switch ($op) {
    case 'display':
      $retval->html = '<div style="margin:5px;padding:10px;border:1px solid #CCC;">';
      $retval->html .= '<form style="margin:0px;">';
      $retval->html .= '<div style="float:left;width:75%;">';
      $retval->html .= '<div>This is a basic form test which would some message here</div>';
      $retval->html .= '<p><label>What is your name: </label><input type="text" name="name" value=""></p>';
      $retval->html .= '<p><label>What is your company name: </label><input type="text" name="company" value=""></p>';
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
      $retval->retcode = TRUE;
      drupal_set_message('Save Task Data Method');
      break;
  }

  return $retval;

}



?>
