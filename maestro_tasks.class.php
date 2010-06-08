<?php
  
  abstract class MaestroTask {
    
    
    function __contruct( Tasktype $tasktype, $properties) {
      $this->tasktype = $tasktype;
      //  Initialize task properties registry      
    }
    
    abstract function execute ($properties);
    
  }
  
  
  // Classes can be in their own file or library and included via several options
  
  class MaestroTaskTypeStart extends MaestroTask {
    
    function execute($properties) {      
      return TRUE;
      
    }
    
    
  }
  
  class MaestroTaskTypeEnd extends MaestroTask {
    
    function execute($properties) {
      return TRUE;       
    }
    
    
  }    
  
  
  class MaestroTaskTypeBatch extends MaestroTask {    
    
    function execute($properties) {
      return TRUE;       
    }
    
    
  }
  
  
  class MaestroTaskTypeAnd extends MaestroTask { 
    
    function execute($properties) {
      return TRUE;       
    }    
    
  }
