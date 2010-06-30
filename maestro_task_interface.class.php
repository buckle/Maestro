<?php
// $Id:

/**
 * @file
 * maestro_task_interface.class.php
 */

abstract class MaestroTaskInterface {
  protected $_task_id;

  function __construct($task_id=0) {
    $this->_task_id = $task_id;
  }

  //create task will insert the shell record of the task, and then the child class will handle the edit.
  function create() {
  }

  //handles the update for the drag and drop
  function move() {
    $offset_left = intval($_POST['offset_left']);
    $offset_top = intval($_POST['offset_top']);

    db_query("UPDATE {maestro_template_data} SET offset_left=:ofl, offset_top=:ofr WHERE id=:tdid", array(':ofl' => $offset_left, ':ofr' => $offset_top, ':tdid' => $this->_task_id));
  }

  //handles the update when adding a line (insert the next step record)
  function drawLine() {
  }

  //in theory only the if task will use this method
  function drawLineFalse() {
  }

  //returns an array of options for when the user right-clicks the task
  function getContextMenu() {
    $options = array (
      'draw_line' => t('Draw Line'),
      'clear_lines' => t('Clear Adjascent Lines'),
      'edit_task' => t('Edit Task'),
      'delete_task' => t('Delete Task')
    );

    return $options;
  }

  abstract function display();
  abstract function edit();
  abstract function save();
  abstract function destroy();

}

class MaestroTaskInterfaceStart extends MaestroTaskInterface {
  function display() {
    echo theme('maestro_task_start', array('tdid' => $this->_task_id));
  }

  function edit() {
  }

  function save() {
  }

  function destroy() {
  }
}

class MaestroTaskInterfaceEnd extends MaestroTaskInterface {
  function display() {
    echo theme('maestro_task_end', array('tdid' => $this->_task_id));
  }

  function edit() {
  }

  function save() {
  }

  function destroy() {
  }
}

class MaestroTaskInterfaceIf extends MaestroTaskInterface {
  function display() {
    echo theme('maestro_task_if', array('tdid' => $this->_task_id));
  }

  function edit() {
  }

  function save() {
  }

  function destroy() {
  }

  function getContextMenu() {
    $options = parent::getContextMenu();
    $options['draw_line'] = 'Draw Success Line';
    $options['draw_line_false'] = 'Draw Fail Line';

    return $options;
  }
}

class MaestroTaskInterfaceBatch extends MaestroTaskInterface {
  function display() {
    echo theme('maestro_task_batch', array('tdid' => $this->_task_id));
  }

  function edit() {
  }

  function save() {
  }

  function destroy() {
  }
}

class MaestroTaskInterfaceInteractiveFunction extends MaestroTaskInterface {
  function display() {
    echo theme('maestro_task_interactive_function', array('tdid' => $this->_task_id));
  }

  function edit() {
  }

  function save() {
  }

  function destroy() {
  }
}

class MaestroTaskInterfaceSetProcessVariable extends MaestroTaskInterface {
  function display() {
    echo theme('maestro_task_set_process_variable', array('tdid' => $this->_task_id));
  }

  function edit() {
  }

  function save() {
  }

  function destroy() {
  }
}

?>
