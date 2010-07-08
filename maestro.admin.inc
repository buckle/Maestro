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

  $mi = new MaestroInterface($tid);

  return $mi->displayPage();
}

function maestro_handle_ajax_request($task_type, $template_data_id, $op) {
  $ti = new $task_type($template_data_id);
  $ti->$op();
}

function maestro_handle_structure_ajax_request(){
  global $base_url;
  $op=check_plain($_POST['op']);
  $cntr=check_plain($_POST['cntr']);
  $id=check_plain($_POST['id']);

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
      $record = new stdClass();
      $record ->variable_name = $name;
      $record ->variable_value = $value;
      $record ->template_id = $id;
      drupal_write_record('maestro_template_variables', $record);
      $status = 0;
      if($record->id > 0){
        $status = 1;
      }
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $id ));

      $arr=array('status' => $status, 'data' => $data);
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
      $query = db_delete('maestro_template_variables');
      $query->condition('id',$id,'=');
      $query->execute();
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $tid ));
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'editvariable':
      $tid=check_plain($_POST['tid']);
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $tid, 'edit_var' => $id));
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'showvariables':
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $id ));
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