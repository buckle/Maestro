<?php
// $Id:

/**
 * @file
 * maestro_task_interface.class.php
 */

abstract class MaestroTaskInterface {
  protected $_task_id;
  protected $_template_id;
  protected $_task_type;
  protected $_is_interactive;
  protected $_task_data;  //used when fetching task information
  protected $_task_assignment_data;  //used when fetching task process information

  function __construct($task_id=0, $template_id=0) {
    $this->_task_id = $task_id;
    $this->_task_type = '';

    if ($template_id == 0 && $task_id > 0) {
      $res = db_select('maestro_template_data', 'a');
      $res->fields('a', array('template_id'));
      $res->condition('a.id', $task_id, '=');
      $rec = current($res->execute()->fetchAll());
      $this->_template_id = $rec->template_id;
    }
    else {
      $this->_template_id = $template_id;
    }
    $this->_task_data = NULL;
    $this->_task_process_data = NULL;
  }

  protected function _fetch_task_information() {
    $res = db_select('maestro_template_data', 'a');
    $res->fields('a', array('taskname', 'task_data'));
    $res->condition('a.id', $this->_task_id, '=');
    $td_rec = current($res->execute()->fetchAll());
    $td_rec->task_data = unserialize($td_rec->task_data);

    $res2 = db_select('maestro_template_assignment', 'a');
    $res2->fields('a', array('uid', 'process_variable'));
    $res2->condition('a.template_data_id', $this->_task_id, '=');
    $ta_rec = current($res2->execute()->fetchAll());

    if ($ta_rec == '') {
      $ta_rec = new stdClass();
      $ta_rec->uid = 0;
      $ta_rec->process_variable = 0;
    }
    $this->_task_data = $td_rec;
    $this->_task_assignment_data = $ta_rec;
  }

  //create task will insert the shell record of the task, and then the child class will handle the edit.
  function create() {
    $rec = new stdClass();
    $rec->template_id = $this->_template_id;
    $rec->taskname = t('New Task');
    $rec->task_class_name = 'MaestroTaskType' . $this->_task_type;
    $rec->is_interactive = $this->_is_interactive;
    $rec->first_task = 0;
    $rec->offset_left = $_POST['offset_left'];
    $rec->offset_top = $_POST['offset_top'];
    drupal_write_record('maestro_template_data', $rec);
    $this->_task_id = $rec->id;

    $retval  = "<div id=\"task{$this->_task_id}\" class=\"MaestroTaskInterface{$this->_task_type} maestro_task_container\" onclick=\"draw_line_to(this);\" style=\"position: absolute; left: {$_POST['offset_left']}px; top: {$_POST['offset_top']}px;\">";
    $retval .= $this->display();
    $retval .= '</div>';
    $retval .= $this->getContextMenuHTML();
    return array('html' => $retval, 'js' => $this->getContextMenuJS());
  }

  //deletes the task
  function destroy() {
    $res = db_select('maestro_queue', 'a');
    $res->fields('a', array('id'));
    $res->condition('template_data_id', $this->_task_id, '=');
    $rec = current($res->execute()->fetchAll());

    if ((array_key_exists('confirm_delete', $_POST) && $_POST['confirm_delete'] == 1) || $rec == '') {
      db_query("DELETE FROM {maestro_template_data} WHERE id=:tdid", array(':tdid' => $this->_task_id));
      db_query("DELETE FROM {maestro_template_assignment} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));
      db_query("DELETE FROM {maestro_template_data_next_step} WHERE template_data_to=:tdid OR template_data_to_false=:tdid OR template_data_from=:tdid", array(':tdid' => $this->_task_id));
      db_query("DELETE FROM {maestro_queue} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));
      $retval = '';
      $success = 1;
    }
    else {
      $retval  = t("There are still outstanding tasks in the queue that depend on this task! Deleting this task will delete the queue records too.");
      $retval .= '<br>';
      $retval .= t("Continue with delete?");
      $retval .= '&nbsp;&nbsp;&nbsp;';
      $retval .= "<input type=\"button\" value=\"" . t('Yes') . "\" onclick=\"set_tool_tip(''); ";
      $retval .= "enable_ajax_indicator(); (function($) {\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/', {confirm_delete: 1}, delete_task, 'json'); })(jQuery);\">";
      $retval .= "<input type=\"button\" value=\"" . t('No') . "\" onclick=\"set_tool_tip('');\">";
      $success = 0;
    }

    return array('message' => $retval, 'success' => $success, 'task_id' => $this->_task_id);
  }

  //handles the update for the drag and drop
  function move() {
    $offset_left = intval($_POST['offset_left']);
    $offset_top = intval($_POST['offset_top']);

    db_query("UPDATE {maestro_template_data} SET offset_left=:ofl, offset_top=:ofr WHERE id=:tdid", array(':ofl' => $offset_left, ':ofr' => $offset_top, ':tdid' => $this->_task_id));
  }

  //handles the update when adding a line (insert the next step record)
  function drawLine() {
    $res = db_select('maestro_template_data_next_step', 'a');
    $res->fields('a', array('id'));

    $cond1 = db_or()->condition('a.template_data_to', $_POST['line_to'], '=')->condition('a.template_data_to_false', $_POST['line_to'], '=');
    $cond1fin = db_and()->condition('a.template_data_from', $this->_task_id, '=')->condition($cond1);

    $cond2 = db_or()->condition('a.template_data_to', $this->_task_id, '=')->condition('a.template_data_to_false', $this->_task_id, '=');
    $cond2fin = db_and()->condition('a.template_data_from', $_POST['line_to'], '=')->condition($cond2);

    $cond = db_or()->condition($cond1fin)->condition($cond2fin);

    $res->condition($cond);
    $rec = current($res->execute()->fetchAll());

    if ($rec == '') {
      $rec = new stdClass();
      $rec->template_data_from = $this->_task_id;
      $rec->template_data_to = $_POST['line_to'];
      $rec->template_data_to_false = 0;
      drupal_write_record('maestro_template_data_next_step', $rec);
    }
    else {
      //perhaps return a simple true/false with error message if the select failed
    }
  }

  //in theory only the if task will use this method
  function drawLineFalse() {
    $res = db_select('maestro_template_data_next_step', 'a');
    $res->fields('a', array('id'));

    $cond1 = db_or()->condition('a.template_data_to', $_POST['line_to'], '=')->condition('a.template_data_to_false', $_POST['line_to'], '=');
    $cond1fin = db_and()->condition('a.template_data_from', $this->_task_id, '=')->condition($cond1);

    $cond2 = db_or()->condition('a.template_data_to', $this->_task_id, '=')->condition('a.template_data_to_false', $this->_task_id, '=');
    $cond2fin = db_and()->condition('a.template_data_from', $_POST['line_to'], '=')->condition($cond2);

    $cond = db_or()->condition($cond1fin)->condition($cond2fin);

    $res->condition($cond);
    $rec = current($res->execute()->fetchAll());

    if ($rec == '') {
      $rec = new stdClass();
      $rec->template_data_from = $this->_task_id;
      $rec->template_data_to = 0;
      $rec->template_data_to_false = $_POST['line_to'];
      drupal_write_record('maestro_template_data_next_step', $rec);
    }
    else {
      //perhaps return a simple true/false with error message if the select failed
    }
  }

  //remove any next step records pertaining to this task
  function clearAdjacentLines() {
    db_query("DELETE FROM {maestro_template_data_next_step} WHERE template_data_from=:tdid OR template_data_to=:tdid OR template_data_to_false=:tdid", array(':tdid' => $this->_task_id));
  }

  //returns an array of options for when the user right-clicks the task
  function getContextMenu() {
    $draw_line_msg = t('Select a task to draw the line to.');
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "draw_status = 1; draw_type = 1; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "enable_ajax_indicator(); \$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/edit/', display_task_panel, 'json');"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "enable_ajax_indicator(); \$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/', delete_task, 'json');\n"
      )
    );

    return $options;
  }

  function getContextMenuHTML() {
    $options = $this->getContextMenu();
    $html = "<div id=\"maestro_task{$this->_task_id}_context_menu\" class=\"maestro_context_menu\"><ul>\n";

    foreach ($options as $key => $option) {
      $option = t($option);
      $html .= "<li style=\"white-space: nowrap;\" id=\"$key\">{$option['label']}</li>\n";
    }
    $html .= "</ul></div>\n";

    return $html;
  }

  function getContextMenuJS() {
    $options = $this->getContextMenu();
    $js  = "(function ($) {\n";
    $js .= "\$('#task{$this->_task_id}').contextMenu('maestro_task{$this->_task_id}_context_menu', {\n";
    $js .= "menuStyle: {\n";
    $js .= "width: 175,\n";
    $js .= "fontSize: 12,\n";
    $js .= "},\n";

    $js .= "itemStyle: {\n";
    $js .= "padding: 0,\n";
    $js .= "paddingLeft: 10,\n";
    $js .= "},\n";

    $js .= "bindings: {\n";

    foreach ($options as $key => $option) {
      $js .= "'$key': function(t) {\n";
      $js .= $option['js'];
      $js .= "},\n";
    }

    $js .= "}\n";
    $js .= "});\n";
    $js .= "})(jQuery);\n";

    return $js;
  }

  function edit() {
    return array('html' => theme('maestro_workflow_edit_tasks_frame', array('tdid' => $this->_task_id, 'tid' => $this->_template_id, 'form_content' => $this->get_edit_form_content())));
  }

  function setCanvasHeight() {
    watchdog('maestro', 'setCanvasHeight method');
    $rec = new stdClass();
    $rec->id = $this->_template_id;
    $rec->canvas_height = $_POST['height'];
    drupal_write_record('maestro_template', $rec, array('id'));
  }

  abstract function display();
  abstract function get_edit_form_content();
  abstract function save();
}

class MaestroTaskInterfaceStart extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'Start';
    $this->_is_interactive = 0;
  }

  function create() {
    parent::create();
    $update=db_update('maestro_template_data')
      ->fields(array( 'taskname' => t('Start'),
                      'offset_left' => 34,
                      'offset_top' => 38

      ))
      ->condition('id', $this->_task_id)
      ->execute();
  }

  function display() {
    return theme('maestro_task_start', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $draw_line_msg = t('Select a task to draw the line to.');
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "draw_status = 1; draw_type = 1; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceEnd extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'End';
    $this->_is_interactive = 0;
  }

  function create() {
    parent::create();
    $update=db_update('maestro_template_data')
      ->fields(array( 'taskname' => t('End'),
                      'offset_left' => 280,
                      'offset_top' => 200
      ))
      ->condition('id', $this->_task_id)
      ->execute();
  }

  function display() {
    return theme('maestro_task_end', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $options = array (
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceIf extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'If';
    $this->_is_interactive = 0;
  }

  function display() {
    return theme('maestro_task_if', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $draw_line_msg = t('Select a task to draw the line to.');
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Success Line'),
        'js' => "draw_status = 1; draw_type = 1; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'draw_line_false' => array(
        'label' => t('Draw Fail Line'),
        'js' => "draw_status = 1; draw_type = 2; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "enable_ajax_indicator(); \$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/edit/', display_task_panel, 'json');"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "enable_ajax_indicator(); \$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/', delete_task, 'json');\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceBatch extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'Batch';
    $this->_is_interactive = 0;
  }

  function display() {
    return theme('maestro_task_batch', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
    $this->_fetch_task_information();
    $this->_task_data->task_data['handler_location'] = variable_get('maestro_batch_script_location', drupal_get_path('module','maestro') . "/batch/");
    return theme('maestro_task_batch_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'ta_rec' => $this->_task_assignment_data));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->taskname = $_POST['taskname'];
    $rec->task_data = serialize(array('handler' => $_POST['handler']));
    drupal_write_record('maestro_template_data', $rec, array('id'));
  }
}

class MaestroTaskInterfaceBatchFunction extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'BatchFunction';
    $this->_is_interactive = 0;
  }

  function display() {
    return theme('maestro_task_batch_function', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
    $this->_fetch_task_information();
    $batch_function = drupal_get_path('module','maestro') . "/batch/batch_functions.php";
    $this->_task_data->task_data['handler_location'] = $batch_function;
    return theme('maestro_task_batch_function_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'ta_rec' => $this->_task_assignment_data));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->taskname = $_POST['taskname'];
    $rec->task_data = serialize(array('handler' => $_POST['handler']));
    drupal_write_record('maestro_template_data', $rec, array('id'));
  }
}

class MaestroTaskInterfaceInteractiveFunction extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'InteractiveFunction';
    $this->_is_interactive = 1;
  }

  function display() {
    return theme('maestro_task_interactive_function', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
    $res = db_select('maestro_template_data', 'a');
    $res->fields('a', array('taskname', 'task_data'));
    $res->condition('a.id', $this->_task_id, '=');
    $td_rec = current($res->execute()->fetchAll());
    $td_rec->task_data = unserialize($td_rec->task_data);

    $res2 = db_select('maestro_template_assignment', 'a');
    $res2->fields('a', array('uid', 'process_variable'));
    $res2->condition('a.template_data_id', $this->_task_id, '=');
    $ta_rec = current($res2->execute()->fetchAll());

    if ($ta_rec == '') {
      $ta_rec = new stdClass();
      $ta_rec->uid = 0;
      $ta_rec->process_variable = 0;
    }

    return theme('maestro_task_interactive_function_edit', array('tdid' => $this->_task_id, 'td_rec' => $td_rec, 'ta_rec' => $ta_rec));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->taskname = $_POST['taskname'];
    $rec->task_data = serialize(array('handler' => $_POST['handler'], 'optional_parm' => $_POST['optional_parm']));
    $rec->assigned_by_variable = ($_POST['uid'] == 0 && $_POST['process_variable'] > 0) ? 1:0;
    drupal_write_record('maestro_template_data', $rec, array('id'));

    /*$templateRec = db_query("SELECT template_id,logical_id FROM {maestro_template_data} WHERE id = :tid",
      array(':tid' => $_POST['template_data_id']))->fetchObject();
    $result = db_select('maestro_template_data', 'a')
        ->fields('a', array('logical_id'))
        ->orderBy('a.logical_id', 'DESC')
        ->condition('a.template_id',$templateRec->template_id, '=')
        ->condition(db_and()->isNotNull('a.logical_id'))
        ->range(0,1)
        ->execute();
    $dataRec = $result->fetchObject();
    if ($templateRec->logical_id == NULL or $templateRec->logical_id == 0) {
      $nextLogicalId = $dataRec->logical_id + 1;
      db_update('maestro_template_data')
      ->fields(array('logical_id' => $nextLogicalId))
      ->condition('id', $_POST['template_data_id'])
      ->execute();
    }*/

    $res = db_select('maestro_template_assignment', 'a');
    $res->fields('a', array('id'));
    $res->condition('a.template_data_id', $_POST['template_data_id'], '=');
    $rec = current($res->execute()->fetchAll());

    $new_rec = false;
    if ($rec == '') { //if record doesnt exist, create one
      $rec = new stdClass();
      $new_rec = true;
    }

    $rec->template_data_id = $_POST['template_data_id'];
    $rec->uid = $_POST['uid'];
    $rec->process_variable = ($_POST['uid'] > 0) ? 0:$_POST['process_variable'];

    if ($new_rec) {
      drupal_write_record('maestro_template_assignment', $rec);
    }
    else {
      drupal_write_record('maestro_template_assignment', $rec, array('id'));
    }
  }
}

class MaestroTaskInterfaceSetProcessVariable extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'SetProcessVariable';
    $this->_is_interactive = 0;
  }

  function display() {
    return theme('maestro_task_set_process_variable', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }
}

?>
