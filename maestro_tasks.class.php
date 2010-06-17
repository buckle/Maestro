<?php

abstract class MaestroTask {
  public $_properties = NULL;
  private $_message = NULL;
  public $executionStatus = NULL;

  function __construct($properties) {
    $this->_properties = $properties;

  }

  abstract function execute ();

  function setMessage($msg) {
    $this->_message = $msg;
  }

  function getMessage() {
    return $this->_message;
  }

}


// Classes can be in their own file or library and included via several options

class MaestroTaskTypeStart extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Start" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }


}

class MaestroTaskTypeEnd extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "End" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }


}


class MaestroTaskTypeBatch extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "Batch" - properties: ';
    watchdog('maestro',$msg);
    $success = FALSE;

    $current_path = drupal_get_path('module','maestro') . "/batch/";

    if (file_exists($current_path . $this->_properties->handler)) {
      require($current_path . $this->_properties->handler );
    } elseif (file_exists($this->_properties->handler)) {  // Check in current directory
      require($this->_properties->handler);
    }
    //Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->executionStatus = TRUE;
    }else{
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }
}

class MaestroTaskTypeBatchFunction extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "BatchFunction" - properties: ';
    watchdog('maestro',$msg);
    $success = FALSE;
    $current_path = drupal_get_path('module','maestro') . "/batch/";
    include($current_path . "batch_functions.php" );


    if (function_exists($this->_properties->handler)) {
      $this->_properties->handler($this->_properties->id,$this->_properties->process_id);
    }
    //Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->executionStatus = TRUE;
    }else{
      $this->executionStatus = FALSE;
    }

    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    return $this;
  }
}


class MaestroTaskTypeAnd extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "AND" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

}


class MaestroTaskTypeIf extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "IF" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

}

class MaestroTaskTypeInteractivefunction extends MaestroTask {

  function execute() {
    $msg = 'Execute Task Type: "MaestroTaskTypeInteractivefunction" - properties: ';
    watchdog('maestro',$msg);
    $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
    $this->executionStatus = TRUE;
    return $this;
  }

}


