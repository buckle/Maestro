<?php
// $Id:

/**
 * @file
 * maestro.admin.inc
 */

include_once './' . drupal_get_path('module', 'maestro') . '/maestro_interface.class.php';
include_once './' . drupal_get_path('module', 'maestro') . '/maestro_task_interface.class.php';

function maestro_admin($tid=0, $operation='', $var=0) {
  global $base_url;
  $maestro_path = $base_url . '/' . drupal_get_path('module', 'maestro');
  drupal_add_css($maestro_path . '/css/maestro.css');
  drupal_add_js($maestro_path . '/js/ajax_support.js');

  return theme('maestro_workflow_list', array('tid' => $tid, 'operation' => $operation , 'edit_var' => $var));
}

//called from the hooks_menu
function maestro_edit_workflow($tid) {
  global $base_url;

  $maestro_path = '/' . drupal_get_path('module', 'maestro');

  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/wz_jsgraphics.js');
  drupal_add_js($base_url . $maestro_path . '/js/admin_template_editor.js');
  drupal_add_js($base_url . $maestro_path . '/js/jquery.contextmenu.js');
  drupal_add_js($base_url . $maestro_path . '/js/jquery.simplemodal.min.js');

  $mi = new MaestroInterface($tid);

  return $mi->displayPage();
}

function maestro_handle_ajax_request($task_type, $template_data_id, $template_id, $op) {
  $ti = new $task_type($template_data_id, $template_id);
  print json_encode($ti->$op());
  exit();
}

function maestro_handle_structure_ajax_request(){
  global $base_url;
  if(!user_access('maestro admin')) return false;
  $op=@check_plain($_POST['op']);
  $cntr=@check_plain($_POST['cntr']);
  $id=@check_plain($_POST['id']);

  switch($op){
    case 'savetemplate':
      $name=check_plain($_POST['templateName']);
      $use_project=intval(check_plain($_POST['useProject']));
      $app_group=intval(check_plain($_POST['appGroup']));

      $update=db_update('maestro_template')
      ->fields(array( 'template_name' => $name,
                      'use_project' => $use_project,
                      'app_group' => $app_group
      ))
      ->condition('id', $id)
      ->execute();
      $status="0";
      if($update>=0){
        $status="1";
      }
      $arr=array('id' => $id, 'cntr' => $cntr, 'status' => $status);
      echo json_encode($arr);
      break;

    case 'createvariable':
      $name=check_plain($_POST['newVariableName']);
      $value=check_plain($_POST['newVariableValue']);
      $recID=maestro_createTemplateVariable($id, $name, $value);
      $status = 0;
      if($recID > 0){
        $status = 1;
      }
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $id ));

      $arr=array('status' => $status, 'data' => $data, 'cntr' => $id );
      echo json_encode($arr);
      break;

    case 'updatevariable':
      $name=check_plain($_POST['name']);
      $value=check_plain($_POST['val']);
      $update=db_update('maestro_template_variables')
      ->fields(array( 'variable_name' => $name,
                      'variable_value' => $value
      ))
      ->condition('id', $id)
      ->execute();

      $query = db_select('maestro_template_variables', 'a');
      $query->fields('a',array('template_id'));
      $query->condition('a.id',$id,'=');
      $res=current($query->execute()->fetchAll());

      $status="0";
      if($update>=0){
        $status="1";
      }
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $res->template_id ));
      $arr=array('status' => $status, 'data' => $data, 'var_id' => $id);
      echo json_encode($arr);
      break;

    case 'deletevariable':
      $tid=check_plain($_POST['tid']);
      $ret=maestro_deleteTemplateVariable($id);
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $tid ));
      $status="0";
      if($ret){
        $status="1";
      }
      $arr=array('status' => $status, 'data' => $data, 'cntr' => $tid);
      echo json_encode($arr);
      break;

    case 'editvariable':
      $tid=check_plain($_POST['tid']);
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $tid, 'edit_var' => $id));
      $arr=array('status' => "1", 'data' => $data, 'cntr' => $tid);
      echo json_encode($arr);
      break;

    case 'showvariables':
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $id ));
      $arr=array('status' => "1", 'data' => $data, 'cntr' => $id);
      echo json_encode($arr);
      break;

    case 'createtemplate':
      $name=check_plain($_POST['name']);
      $recID=maestro_createNewTemplate($name);
      $status = 0;
      if($recID > 0){
        $status = 1;
      }
      $data=theme('maestro_workflow_list', array('tid' => 0, 'operation' => '' , 'edit_var' => 0));

      $arr=array('status' => $status, 'data' => $data);
      echo json_encode($arr);
      break;

    case 'createappgroup':
      $name=check_plain($_POST['name']);
      if($name != '')  $recID=maestro_createAppGroup($name);
      $status = 0;
      if($recID > 0){
        $status = 1;
      }
      $arr=array('status' => $status, 'data' => '');
      echo json_encode($arr);
      break;

    case 'deleteappgroup':
      maestro_deleteAppGroup($id);
      $data=maestro_createAppGroupDropDown('deleteAppGroup');
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'refreshappgroup':
      $which=check_plain($_POST['which']);
      $data=maestro_createAppGroupDropDown($which);
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'deletetemplate':
      $res=db_query("SELECT id FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec){
        $query = db_delete('maestro_template_assignment');
        $query->condition('template_data_id',$rec->id,'=');
        $query->execute();

        $query = db_delete('maestro_template_data_next_step');
        $query->condition('template_data_from',$rec->id,'=');
        $query->execute();

        $query = db_delete('maestro_template_data');
        $query->condition('id',$rec->id,'=');
        $query->execute();
      }

      $query = db_delete('maestro_template_variables');
      $query->condition('template_id',$id,'=');
      $query->execute();

      $query = db_delete('maestro_template');
      $query->condition('id',$id,'=');
      $query->execute();

      $data = theme('maestro_workflow_list', array('tid' => 0, 'operation' => '' , 'edit_var' => 0));
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'copytemplate':
      $query = db_select('maestro_template', 'a');
      $query->fields('a',array('template_name'));
      $query->condition('a.id',$id,'=');
      $name=current($query->execute()->fetchAll());
      $record = new stdClass();
      $record ->template_name = $name->template_name . " - COPY";
      drupal_write_record('maestro_template', $record);
      $newTID=$record->id;
      $taskDataArray=array();
      $res=db_query("SELECT * FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec) {
        $newrecord = new stdClass();
        $newrecord->template_id = $newTID;
        //$newrecord->logical_id = $rec->logical_id;
        $newrecord->task_class_name = $rec->task_class_name;
        $newrecord->is_interactive = $rec->is_interactive;
        $newrecord->task_data = $rec->task_data;
        $newrecord->handler = $rec->handler;
        $newrecord->first_task = $rec->first_task;
        $newrecord->taskname = $rec->taskname;
        $newrecord->assigned_by_variable = $rec->assigned_by_variable;
        $newrecord->argument_variable = $rec->argument_variable;
        $newrecord->if_argument_process = $rec->if_argument_process;
        $newrecord->operator = $rec->operator;
        $newrecord->if_value = $rec->if_value;
        $newrecord->regenerate = $rec->regenerate;
        $newrecord->regen_all_live_tasks = $rec->regen_all_live_tasks;
        $newrecord->is_dynamic_form = $rec->is_dynamic_form;
        $newrecord->dynamic_form_variable_id = $rec->dynamic_form_variable_id;
        $newrecord->is_dynamic_taskname = $rec->is_dynamic_taskname;
        $newrecord->dynamic_taskname_variable_id = $rec->dynamic_taskname_variable_id;
        $newrecord->function = $rec->function;
        $newrecord->form_id = $rec->form_id;
        $newrecord->field_id = $rec->field_id;
        $newrecord->var_value = $rec->var_value;
        $newrecord->inc_value = $rec->inc_value;
        $newrecord->var_to_set = $rec->var_to_set;
        $newrecord->optional_parm = $rec->optional_parm;
        $newrecord->reminder_interval = $rec->reminder_interval;
        $newrecord->reminder_interval_variable = $rec->reminder_interval_variable;
        $newrecord->subsequent_reminder_interval = $rec->subsequent_reminder_interval;
        $newrecord->last_updated = $rec->last_updated;
        $newrecord->pre_notify_subject = $rec->pre_notify_subject;
        $newrecord->post_notify_subject = $rec->post_notify_subject;
        $newrecord->reminder_subject = $rec->reminder_subject;
        $newrecord->pre_notify_message = $rec->pre_notify_message;
        $newrecord->post_notify_message = $rec->post_notify_message;
        $newrecord->reminder_message = $rec->reminder_message;
        $newrecord->num_reminders = $rec->num_reminders;
        $newrecord->escalate_variable_id = $rec->escalate_variable_id;
        $newrecord->offset_left = $rec->offset_left;
        $newrecord->offset_top = $rec->offset_top;
        $newrecord->surpress_first_notification = $rec->surpress_first_notification;
        drupal_write_record('maestro_template_data', $newrecord);
        $taskDataArray[$rec->id]= $newrecord->id;
      }

      //at this point, we have a list that points the old ID record to the new one.
      //now for each template data next step, we determine which record to go to/from
      $res=db_query("SELECT * FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec) {
        $nextstepres = db_query("SELECT * FROM {maestro_template_data_next_step} WHERE template_data_from = :id", array('id' => $rec->id));
        foreach($nextstepres as $nextstep) {
          $sql  = "INSERT INTO {maestro_template_data_next_step} (template_data_from, template_data_to, template_data_to_false) ";
          $sql .= "VALUES (:a1, :b1, :c1)";
          db_query($sql, array('a1' => $taskDataArray[$nextstep->template_data_from], 'b1' => $taskDataArray[$nextstep->template_data_to], 'c1' => $taskDataArray[$nextstep->template_data_to_false]));
        }

        $assignmentres = db_query("SELECT * FROM {maestro_template_assignment} WHERE template_data_id = :id", array('id' => $rec->id));
        foreach($assignmentres as $ares) {
          $sql  = "INSERT INTO {maestro_template_assignment} (template_data_id, uid, gid, process_variable, pre_notify_variable, post_notify_variable, reminder_notify_variable) ";
          $sql .= "VALUES (:a1, :b1, :c1, :d1, :e1, :f1, :g1)";
          db_query($sql, array( 'a1' => $taskDataArray[$ares->template_data_id],
                                'b1' => $ares->uid,
                                'c1' => $ares->gid,
                                'd1' => $ares->process_variable,
                                'e1' => $ares->pre_notify_variable,
                                'f1' => $ares->post_notify_variable,
                                'g1' => $ares->reminder_notify_variable
                                ));
        }

      }

      $res=db_query("SELECT * FROM {maestro_template_variables} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec) {
        $newrecord = new stdClass();
        $newrecord->template_id = $newTID;
        $newrecord->variable_name = $rec->variable_name;
        $newrecord->variable_value = $rec->variable_value;
        drupal_write_record('maestro_template_variables', $newrecord);
      }
      $data = theme('maestro_workflow_list', array('tid' => 0, 'operation' => '' , 'edit_var' => 0));
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

  }
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


function maestro_edit_properties($tid, $var=0){
  //$tid is the edited template
  return maestro_admin($tid, 'edit', $var);
}




/*
 * The following functions are here in support of the Maestro Structure page functionality.
 * These functions need to exist in order to run unit tests against their functionality.
 */

function maestro_createNewTemplate($name){
  if(!user_access('maestro admin')) return false;
  if(trim($name) != '') {
    $record = new stdClass();
    $record ->template_name = $name;
    drupal_write_record('maestro_template', $record);
    $newTemplateRecId = $record->id;
    $task=new MaestroTaskInterfaceStart(0, $newTemplateRecId);
    $task->create();
    $task=new MaestroTaskInterfaceEnd(0, $newTemplateRecId);
    $task->create();
    maestro_createTemplateVariable($newTemplateRecId, 'initiator', '');
    return $newTemplateRecId;
  }
  else {
    return FALSE;
  }
}

function maestro_createTemplateVariable($tid, $name, $value){
  if(!user_access('maestro admin')) return false;
  $record = new stdClass();
  $record ->variable_name = $name;
  $record ->variable_value = $value;
  $record ->template_id = $tid;
  drupal_write_record('maestro_template_variables', $record);
  return $record->id;
}

function maestro_deleteTemplateVariable($id){
  if(!user_access('maestro admin')) return false;
  $query = db_select('maestro_template_variables', 'a');
  $query->fields('a',array('variable_name'));
  $query->condition('a.id', $id, '=');
  $name=current($query->execute()->fetchAll());
  if($name->variable_name != 'initiator') {
    $query = db_delete('maestro_template_variables');
    $query->condition('id', $id, '=');
    $query->execute();
    return true;
  }
  else {
    return false;
  }

}

function maestro_createAppGroup($name){
  if(!user_access('maestro admin')) return false;
  $record = new stdClass();
  $record ->app_group = $name;
  drupal_write_record('maestro_app_groups', $record);
  return $record->id;
}

function maestro_deleteAppGroup($id){
  if(!user_access('maestro admin')) return false;
  $query = db_delete('maestro_app_groups');
  $query->condition('id', $id, '=');
  $query->execute();
}