<?php

  abstract class MaestroTask {
    private $_properties = NULL;
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
      @$this->setMessage('Execute Task Type: "Start" - properties: ' . print_r($this->_properties, true) . '<br>');
      $this->executionStatus = TRUE;
      return $this;
    }


  }

  class MaestroTaskTypeEnd extends MaestroTask {

    function execute() {
      $this->setMessage('Execute Task Type: "End" - properties: ' . print_r($this->_properties, true) . '<br>');
      $this->executionStatus = TRUE;
      return $this;
    }


  }


  class MaestroTaskTypeBatch extends MaestroTask {

    function execute() {
      $this->setMessage('Execute Task Type: "Batch" - properties: ' . print_r($this->_properties, true) . '<br>');
      $this->executionStatus = TRUE;
      return $this;
    }


  }


  class MaestroTaskTypeAnd extends MaestroTask {

    function execute() {
      $this->setMessage('Execute Task Type: "And" - properties: ' . print_r($this->_properties, true) . '<br>');
      $this->executionStatus = TRUE;
      return $this;
    }

  }
