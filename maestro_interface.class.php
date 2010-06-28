<?php
// $Id:

/**
 * @file
 * maestro_task_interface.class.php
 */

class MaestroInterface {
  private $_template_id;

  function __construct($template_id) {
    $this->_template_id = $template_id;
  }

  //displays the main task page
  function displayPage() {
    return theme('maestro_workflow_edit', array('tid' => $this->_template_id));
  }

  //should get the valid task types to create, excluding start and end tasks, from the drupal cache
  function getContextMenu() {
    $options = array(
      'iftask' => 'New If Task',
      'batchtask' => 'New Batch Task'
    );

    return $options;
  }
}

?>
