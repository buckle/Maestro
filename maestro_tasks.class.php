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
      $this->setMessage( $msg . print_r($this->_properties, true) . '<br>');
      $this->executionStatus = TRUE;
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
  
  
  