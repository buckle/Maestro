<?php
  
  abstract class MaestroTask {
    var $_properties = null;
    
    function __construct($properties) {      
      $this->_properties = $properties;
        
    }
      
    abstract function execute ();
    
  }
  
  
  // Classes can be in their own file or library and included via several options
  
  class MaestroTaskTypeStart extends MaestroTask {
    
    function execute() {
      $test = 'Execute Task Type: "Start" - properties: ' . print_r($this->_properties,true) . '<br>';
      return $test;
      
    }
    
    
  }
  
  class MaestroTaskTypeEnd extends MaestroTask {
    
    function execute() {
      $test = 'Execute Task Type: "End" - properties: ' . print_r($this->_properties,true) . '<br>';
      return $test;     
    }
    
    
  }    
  
  
  class MaestroTaskTypeBatch extends MaestroTask {    
    
    function execute() {
      $test = 'Execute Task Type: "Batch" - properties: ' . print_r($this->_properties,true) . '<br>';
      return $test;       
    }
    
    
  }
  
  
  class MaestroTaskTypeAnd extends MaestroTask { 
    
    function execute() {
      $test = 'Execute Task Type: "And" - properties: ' . print_r($this->_properties,true) . '<br>';
      return $test;;       
    }    
    
  }
