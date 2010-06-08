<?php
  
  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */
  
  class MaestroEngineType1 extends Maestro {
      
      var $_version = '1.x';
      var $_properties;
      
      function __contruct($options) {
        $this->_properties = $options;        
      }
      
      
      public function getVersion() {
        return $this->_version;      
      }   
    
    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if 
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    function cleanQueue() {
      $processTaskList = array("id" => array(), "processid" => array() );
      $processTaskListcount = 0;

      /* Call Observer Hooks to send out any task notifications and reminders */

      $sql = "SELECT distinct a.id, a.status,a.template_data_id, c.template_id, c.step_type, c.handlerid, ";
      $sql .= "c.function, e.template_name, f.handler, b.id AS process_id, h.steptype ";
      $sql .= "FROM {maestro_queue} a inner join {maestro_process} b on  a.process_id = b.id ";
      $sql .= "inner join {maestro_templatedata} c on a.template_data_id = c.id ";
      $sql .= "inner join {maestro_templte} e on b.template_id = e.id ";
      $sql .= "inner join {maestro_steptype} h on c.step_type = h.id ";
      $sql .= "left outer join {maestro_handlers} f on c.handler_id = f.id ";
      $sql .= "left outer join {maestro_templateassignment} g on g.template_data_id = c.id ";
      $sql .= "WHERE ((a.status <>0 AND a.status IS NOT NULL and a.status<>2 and (h.id=1 OR h.id=7 OR h.id=8)) ";
      $sql .= "OR ((a.status=0 or a.status=3 or a.status=4) and (h.id=2 or h.id=3 or h.id=4 or h.id=5 or h.id=6 or h.id=9 or h.id=10 or h.id=11)) ) ";
      $sql .= "AND (a.archived <> 1 OR a.archived IS NULL OR a.archived =0 ) and (b.complete=0)";

      $query = db_query($sql);
      $numrows = 0;
      while ($queueRecord = db_fetch_object($query)) {
        $numrows++;
        $this->_taskType = strtolower($queueRecord->step_type);
        $this->_processId = $queueRecord->process_id;
        $this->_queueID = $queueRecord->id;
        $handler = $queueRecord->handler;
        $templateName = $queueRecord->template_name;
        $templateDataID = $queueRecord->template_data_id;
        
        // this switch is used to determine what task type it is.
        // in the event its a manual web task, we'll just go ahead and clean it up..
        // however, in the event that its an AND task, we have to be careful that
        // we check the preceeding queue elements to ensure that they're all done before completing
        // the and task and then also entering the next queue item.
        if ($this->_debug ) {
            watchdog('maestro',"Process: {$this->_processId} , Step Type: $this->_taskType");
        }
        /* @todo: Need to determine what the task properties object looks like */
        $taskProperties = $queueRecord;
        
        $taskClassName = 'MaestroTaskType' . ucfirst($this->_taskType);
        $ret = $this->executeTask(new task($taskClassName,$taskProperties));
        if ($ret === FALSE) {
          watchdog('maestro',"Failed Task: {$this->_queueId}, Process: {$this->_processId} , Step Type: $this->_taskType");  
        }       
      }

      if ($numrows == 0 AND $this->_debug) {
        watchdog('maestro','cleanQueue - 0 rows returned.  Nothing in queue.');        
      }
      

    
    
      
    }
    
    function executeTask(task $task,$properties) {
      return $task->execute($properties);      
    }
    
    function assignTask($queueId,$userObject) {
      
    }
    
    function completeTask($queueId) {
      
    }     
    
    function archiveTask($queueId) {
      
    }    
    
    function cancelTask($queueId) {
      
    }    
    
    
    // Get a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name.
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function getProcessVariable($variable) {  
    
    
    }
  }
  
  
