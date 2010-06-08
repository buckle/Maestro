<?php
  
  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */
  
  class Maestro {
      
    private static $MAESTRO;
    var $_version                 = '';       // Current engine version
    var $_processId               = null;     // Current Process the workflow engine is working on
    var $_templateId              = null;     // Current workflow template id being processed
    var $_queueId                 = null;     // Current Queue record id being processed. This is either null, a single item or a semi colon delimited list
    var $_userId                  = null;     // Current User Id
    var $_taskType                = '';    
    var $_debug                   = false;    // Set the current debug level to false.
    var $_userTaskCount           = 0;        // Number of tasks the user has in the queue
    var $_templateCount           = 0;        // Number of templates the user is able to kick off
    var $_processTaskCount        = 0;        // Number of tasks the current process has in the queue  
    
    function createMaestroObject ($type, $options = FALSE){
        echo "<br>Executing createMaestroObject type:$type";       
        if (!isset(self::$MAESTRO)) {
            echo "<br>MAESTRO Object not set";
            // instance does not exist, so create it
            self::$MAESTRO= new self($type, $options = FALSE); 
        }
        return self::$MAESTRO;
    }

    function __construct($type, $options = FALSE) {
      echo "<br>Executing __construct for the base Maestro class type:$type";
      $classfile = drupal_get_path('module','maestro')."/maestro_type{$type}.class.php";
      if (require_once $classfile) {
        $class = "MaestroEngineType{$type}";
        $options = array('color1' => 'red');
        echo "<br>Class $class";
        if (class_exists($class)) {
          $object = new $class($options);
        } else {
          die("maestro.class - Unable to instantiate class $class from $classfile");
        }
      } else {
        die("maestro.class - Unable to include file: $classfile");
      }
    }
    
    function getVersion() {}
    
    
    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if 
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    function cleanQueue() {}
    
    function executeTask(task $task,$properties) {
      return $task->execute($properties);      
    }
    
    function assignTask($queueId,$userObject) {}
      
    function completeTask($queueId) {}
    
    function archiveTask($queueId) {}
   
    function cancelTask($queueId) {}
   
    function getProcessVariable($variable) {}

  
}