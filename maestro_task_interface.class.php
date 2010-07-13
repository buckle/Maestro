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
      watchdog('maestro', 'test' . $this->_template_id);
    }
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

    watchdog('maestro', print_r($res, true));
    print "<div id=\"task{$this->_task_id}\" class=\"MaestroTaskInterface{$this->_task_type} maestro_task_container\" style=\"position: absolute; left: {$_POST['offset_left']}px; top: {$_POST['offset_top']}px;\">";
    $this->display();
    print '</div>';

    exit();
  }

  //deletes the task
  function destroy() {
    watchdog('notice', "maestro destroy task");
  }

  //handles the update for the drag and drop
  function move() {
    $offset_left = intval($_POST['offset_left']);
    $offset_top = intval($_POST['offset_top']);

    db_query("UPDATE {maestro_template_data} SET offset_left=:ofl, offset_top=:ofr WHERE id=:tdid", array(':ofl' => $offset_left, ':ofr' => $offset_top, ':tdid' => $this->_task_id));
  }

  //handles the update when adding a line (insert the next step record)
  function drawLine() {
    watchdog('notice', "maestro drawLine");
  }

  //in theory only the if task will use this method
  function drawLineFalse() {
    watchdog('notice', "maestro drawLineFalse");
  }

  //remove any next step records pertaining to this task
  function clearAdjacentLines() {
    watchdog('notice', "maestro clearAdjacentLines");
  }

  //returns an array of options for when the user right-clicks the task
  function getContextMenu() {
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/drawLine/');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/clearAdjacentLines/');\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "$.ajax({
          type: \"POST\",
          url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/edit/',
          dataType: \"html\",
          success: display_task_panel
        });"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/');\n"
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
    print theme('maestro_workflow_edit_tasks_frame', array('tdid' => $this->_task_id, 'form_content' => $this->get_edit_form_content()));
    exit();
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

  function display() {
    print theme('maestro_task_start', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/drawLine/');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/clearAdjacentLines/');\n"
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

  function display() {
    print theme('maestro_task_end', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $options = array (
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/clearAdjacentLines/');\n"
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
    print theme('maestro_task_if', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Success Line'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/drawLine/');\n"
      ),
      'draw_line_false' => array(
        'label' => t('Draw Fail Line'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/drawLineFalse/');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/clearAdjacentLines/');\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/edit/');\n"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/');\n"
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
    watchdog('maestro', theme('maestro_task_batch', array('tdid' => $this->_task_id)));
    print theme('maestro_task_batch', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }
}

class MaestroTaskInterfaceInteractiveFunction extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'InteractiveFunction';
    $this->_is_interactive = 1;
  }

  function display() {
    print theme('maestro_task_interactive_function', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
    return theme('maestro_task_interactive_function_edit', array('tdid' => $this->_task_id));
  }

  function save() {
    watchdog('notice', "maestro save " . $_POST['taskname']);
  }

  function destroy() {
    parent::destroy();
    watchdog('notice', "maestro destroy interactive function task {$this->_task_id}");
  }
}

class MaestroTaskInterfaceSetProcessVariable extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    parent::__construct($task_id, $template_id);
    $this->_task_type = 'SetProcessVariable';
    $this->_is_interactive = 0;
  }

  function display() {
    print theme('maestro_task_set_process_variable', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }
}

?>
