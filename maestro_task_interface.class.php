<?php
// $Id:

/**
 * @file
 * maestro_task_interface.class.php
 */

abstract class MaestroTaskInterface {
  function __construct() {
  }

  //create task will insert the shell record of the task, and then the child class will handle the edit.
  function createTask() {
  }

  //handles the update for the drag and drop
  function moveTask() {
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

  abstract function displayTask();
  abstract function editTask();
  abstract function saveTask();
  abstract function deleteTask();

}

class MaestroTaskInterfaceBatch extends MaestroTaskInterface {
  function displayTask() {
  }

  function editTask() {
  }

  function saveTask() {
  }

  function deleteTask() {
  }
}

class MaestroTaskInterfaceIf extends MaestroTaskInterface {
  function displayTask() {
  }

  function editTask() {
  }

  function saveTask() {
  }

  function deleteTask() {
  }

  function getContextMenu() {
    $options = parent::getContextMenu();
    $options['draw_line'] = 'Draw Success Line';
    $options['draw_line_false'] = 'Draw Fail Line';

    return $options;
  }
}

?>
