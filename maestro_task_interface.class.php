<?php
// $Id:

/**
 * @file
 * maestro_task_interface.class.php
 */

abstract class MaestroTaskInterface {
  protected $_task_id;
  protected $_task_type;

  function __construct($task_id=0) {
    $this->_task_id = $task_id;
    $this->_task_type = '';
  }

  //create task will insert the shell record of the task, and then the child class will handle the edit.
  function create() {
    watchdog('notice', "maestro {$_POST['offset_left']}, {$_POST['offset_top']}, {$_POST['task_type']}");
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
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/drawLine/');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/clearAdjacentLines/');\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "$.ajax({type: \"POST\", url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/edit/', dataType: \"html\", success: display_task_panel});"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/destroy/');\n"
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
  function __construct($task_id=0) {
    parent::__construct($task_id);
    $this->_task_type = 'Start';
  }

  function display() {
    echo theme('maestro_task_start', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/drawLine/');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/clearAdjacentLines/');\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceEnd extends MaestroTaskInterface {
  function __construct($task_id=0) {
    parent::__construct($task_id);
    $this->_task_type = 'End';
  }

  function display() {
    echo theme('maestro_task_end', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $options = array (
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/clearAdjacentLines/');\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceIf extends MaestroTaskInterface {
  function __construct($task_id=0) {
    parent::__construct($task_id);
    $this->_task_type = 'If';
  }

  function display() {
    echo theme('maestro_task_if', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }

  function getContextMenu() {
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Success Line'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/drawLine/');\n"
      ),
      'draw_line_false' => array(
        'label' => t('Draw Fail Line'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/drawLineFalse/');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/clearAdjacentLines/');\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/edit/');\n"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "\$.post(ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/destroy/');\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceBatch extends MaestroTaskInterface {
  function __construct($task_id=0) {
    parent::__construct($task_id);
    $this->_task_type = 'Batch';
  }

  function display() {
    echo theme('maestro_task_batch', array('tdid' => $this->_task_id));
  }

  function get_edit_form_content() {
  }

  function save() {
  }
}

class MaestroTaskInterfaceInteractiveFunction extends MaestroTaskInterface {
  function __construct($task_id=0) {
    parent::__construct($task_id);
    $this->_task_type = 'InteractiveFunction';
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
  function __construct($task_id=0) {
    parent::__construct($task_id);
    $this->_task_type = 'SetProcessVariable';
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
