<?php
  
  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */
  
  abstract class MaestroEngine {

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
    
    var $task = null;

    public function executeTask(MaestroTask $task) {
       return $task->execute();
    }    
      
    abstract function getVersion();
    
    abstract function assignTask($queueId,$userObject);
      
    abstract function completeTask($queueId);
    
    abstract function archiveTask($queueId);
   
    abstract function cancelTask($queueId);
   
    abstract function getProcessVariable($variable);    

    abstract function setProcessVariable($variable,$value);     
    
    
    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if 
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    abstract function cleanQueue();    

  
}