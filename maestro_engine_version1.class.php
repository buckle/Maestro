<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */

  class MaestroEngineVersion1 extends MaestroEngine {

      var $_version = '1.x';
      var $_properties;

      function __construct($options) {
        global $user;
        $this->_properties = $options;
        $this->_userId = $user->uid;
      }


      public function getVersion() {
        return $this->_version;
      }

    /* Generate a new process for a workflow template
     * @param $template:
     *   The workflow template id (int) - Mandatory
     *
     * @param $startoffset
     *   Optional paramater to launch the workflow process at other then the default task step 0.
     *   Also used if the process regeneration will not be at task 0 (automatically handled by engine)
     *
     * @param $pid
     *   Optional paramater Parent Process id. This is used when regenerating a process or
     *   if this new process should be a child process (or associated) with another workflow grouping (project)
     *
     * @param $application_association
     *   Optional BOOLEAN value (default FALSE) that if TRUE triggers the process related records to be grouped (related)
     *   as part of a project or related workflow grouping.
     *
     * @return
     *   The process id
     */

    function newProcess($template, $startoffset = null, $pid = null , $application_association = FALSE) {
        global $user;
        // Execute the SQL statement to retrieve the first step of the process and kick it off
        if ($startoffset == null ) {
            $query = db_select('maestro_template_data_next_step', 'a');
            $query->fields('a',array('template_data_from'));
            $query->fields('b',array('regen_all_live_tasks','reminder_interval','task_class_name'));
            $query->addField('b','id','template_data_id');
            $query->addField('c','use_project','template_name');
            $query->join('maestro_template_data', 'b', 'a.template_data_from = b.id');     // default is an INNER JOIN
            $query->join('maestro_template', 'c', 'b.template_id = c.id');
            $query->condition('b.first_task',1,'=');
            $query->condition('c.id',$template,'=');
            $query->orderBy('template_data_from','ASC');
            $query->range(0,1);
        }
        else {
            // Retrieve the one queue record - where it is equal to the passed in start offset.
            $startoffset = int($startoffset);
            $query = db_select('maestro_template_data','a');
            $query->addField('a','id','template_data_id');
            $query->addField('b','template_name');
            $query->fields('a',array('regen_all_live_tasks','reminder_interval','task_class_name'));
            $query->join('maestro_template', 'b', 'b.template_id = a.id');
            $query->condition('a.id',$startoffset);
        }
        if ($this->_debug ) {
            watchdog('maestro','New process code executing');
        }

        // Only 1 record expected - query returns an array of object records
        $templaterec = current($query->execute()->fetchAll());

        if (!empty($templaterec->template_data_id)) {
            $pid = intval($pid);
            if ($pid > 0) {
                $custom_flowname = db_query("SELECT custom_flow_name FROM {maestro_process} WHERE id=$pid")->fetchField();
            }
            else {
              $custom_flowname = '';
            }

            $process_record = new stdClass();
            $process_record->template_id = $template;
            $process_record->custom_flow_name = $custom_flowname;
            $process_record->complete = 0;
            $process_record->pid = $pid;
            $process_record->initiated_date = time();
            drupal_write_record('maestro_process',$process_record);
            $new_processid = $process_record->id;

            if ($process_record->id > 0) {
              $this->_processId = $process_record->id;
            }
            else {
              watchdog('maestro', "New Process Code FAIL! - for template: $template");
              return FALSE;
            }

            if ($templaterec->reminder_interval > 0) {
                $next_reminder_date = time() + $templaterec->reminder_interval;
            }
            else {
              $next_reminder_date = 0;
            }

            $queue_record = new stdClass();
            $queue_record->process_id = $this->_processId;
            $queue_record->template_data_id = $templaterec->template_data_id;
            $queue_record->task_class_name = $templaterec->task_class_name;
            $queue_record->status = 0;
            $queue_record->archived = 0;
            $queue_record->engine_version = $this->_version;
            $queue_record->created_date = time();
            $queue_record->next_reminder_date = $next_reminder_date;
            drupal_write_record('maestro_queue',$queue_record);
            if ($queue_record->id > 0) {
              $this->_queueId = $queue_record->id;
            }
            else {
              watchdog('maestro', "New Process Code FAIL! - Unexpected problem creating initial queue record for template: $template");
              return FALSE;
            }

            // Check if notification has been defined for new task assignment
            $this->sendTaskAssignmentNotifications();

            // Determine if the offset is set.. if so, pack the original pid pointer with a status of 2
            if (!empty($startoffset) AND !empty($pid)) {
                $record = new stdClass();
                $record->id = $pid;
                $record->complete = 2;
                $record->completed_date = time();
                drupal_write_record('maestro_process',$record);

                // Within this section we need to detect whether or not the startoffset task has the "regenerate all live tasks" option set.
                // if so, the process we just layed to rest will hold some in-production tasks, and those tasks will have their pids set to the new pid.
                // @TODO: Need to test this condition -- Randy to add more comments to explain what we are doing here
                if($templaterec->regen_all_live_tasks == 1) {
                  $q2 = db_select('maestro_queue','a');
                  $query->addField('a','id','id');
                  $q2->join('maestro_template_data', 'b', 'a.template_data_id = b.id');
                  $q2->condition('b.task_class_name','MaestroTaskTypeAnd');
                  $q2->condition('a.process_id',$pid);
                  $q2->condition(db_or()->condition('a.archived',0)->condition('a.archived',NULL));
                  $active_queue_tasks_result = $q2->execute();
                  foreach ($active_queue_tasks_result as $active_queue_record) {
                    /* The maestro_queue_from table is used to simplify later reporting of active tasks
                     * @TODO: Review if this table is really being used or is necessary
                     */
                    $q3 = db_select('maesto_queue_from','a');
                    $q3->addField('a','from_queue_id');
                    $q3->condition("a.queue_id = {$active_queue_record->id}");
                    $queue_reporting_result = $q3->execute();
                    foreach ($queue_reporting_result as $queue_reporting_record) {
                      $record = new stdClass();
                      $record->id = $queue_reporting_record->from_queue_id;
                      $record->process_id = $this->_processId;
                      drupal_write_record('maestro_queue',$record);
                    }
                    db_update('maestro_queue')
                      ->fields(array('process_id' => $this->_processId))
                      ->condition('id', $active_queue_record->id)
                      ->condition(db_or()->condition('archived',0)->condition('archived',NULL))
                      ->execute();
                  }
                }
                // Select the process variables for the parent and create new ones for the new process $this->_processId
                $pvquery = db_select('maestro_process_variables','a');
                $pvquery->addExpression($this->_processId,'process_id');
                $pvquery->fields('a',array('variable_value','template_variable_id'));
                $pvquery->condition("a.process_id=$pid");
                db_insert('maestro_process_variables')
                  ->fields(array('variable_value','template_variable_id','process_id'))
                  ->from($pvquery)
                  ->execute();


            } else {
                // Situation where this is the root process, inserts the default template variables into the process
                $pvquery = db_select('maestro_template_variables','a');
                $pvquery->addExpression($this->_processId,'process_id');
                $pvquery->fields('a',array('variable_value','id'));
                $pvquery->condition('a.template_id',$template,'=');
                db_insert('maestro_process_variables')
                  ->fields(array('variable_value','template_variable_id','process_id'))
                  ->from($pvquery)
                  ->execute();
            }
            if ($this->_debug ) {
                watchdog('maestro',"New queue id (1) : {$this->_queueId} - Template Taskid: {$templaterec->template_data_id}");
            }

            // Set the initiator variable here if not already set - via a regenerated process creation
            if ($this->getProcessVariable('INITIATOR') == 0) {
                $this->setProcessVariable('INITIATOR',$user->uid);
            }

            $newTaskAssignedUsers = $this->getAssignedUID();
            if (is_array($newTaskAssignedUsers) AND count($newTaskAssignedUsers) > 0) {
                $this->assignTask($this->_queueId,$newTaskAssignedUsers);
            }

            if($application_association) {
                // Detect whether this new process needs a more detailed project table association created for it.
                if($templaterec->use_project == 1 && empty($pid)){
                    // Condition where there is no parent (totally new process)
                    $project_record = new stdClass();
                    $project_record->process_id = $this->_processId;
                    $project_record->originator_uid = $user->uid;
                    $project_record->task_id = $this->_queueId;
                    $project_record->status = 0;
                    $project_record->description = $templaterec->template_name;
                    drupal_write_record('maestro_projects',$project_record);
                    $this->set_ProcessVariable('PID',$project_record->id);
                    if ($this->_debug ) {
                        watchdog('maestro',"new process: created new project_id: {$project_record->id}");
                    }
                }
                elseif($templaterec->use_project && !empty($pid)) {
                    // Condition where there IS a parent AND we want a project table association
                    // One different step here - to update the wf process association for the original PID to the new insertID
                    db_update('maestro_projects')
                      ->fields(array('process_id' => $this->_processId))
                      ->condition('process_id', $pid, '=')
                      ->execute();
                    if ($this->_debug ) {
                      watchdog('maestro',"updated existing project record - set process_id to {$this->_processId}");
                    }
                }
            }
            else {
                // Condition here where we are spawning a new process from an already existing process
                // BUT we are not going to create a new tracking project.  Rather we are going to associate this process with the
                // parent's already established tracking project
                if(!empty($pid)) {
                    // First, pull back the existing projects entry
                    $existing_project_result = db_select('maestro_projects')
                      ->fields('maestro_projects', array('id', 'related_processes'))
                      ->condition('process_id', $pid, '=')
                      ->execute();
                    if($existing_project_result->related_processes != ''){
                        $existing_project_result->related_processes .= ",";
                    }
                    $existing_project_result->related_processes .= $this->_processId;
                    if($existing_project_result->id != 0) {
                      db_update('maestro_projects')
                        ->fields(array('related_processes' => $existing_project_result->related_processes))
                        ->condition('id', $existing_project_result->id, '=')
                        ->execute();
                    }
                }

            }

            return $this->_processId;

        }
        else {
            watchdog('maestro', "New Process Code FAIL! - Template: $template not defined");
        }
    }





    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    function cleanQueue() {
      $processTaskList = array("id" => array(), "processid" => array() );
      $processTaskListcount = 0;

      /* Call Observer Hooks to send out any task notifications and reminders */
      $interactiveCondition = db_and()->condition('a.status',0)->condition('a.is_interactive',1);
      $batchStatusCondition = db_or()->condition('a.status',0)->condition('a.status',3)->condition('a.status',4);
      $batchOverallCondition = db_and()->condition($batchStatusCondition)->condition('a.is_interactive',0);
      $lastCondition = db_and()->condition('a.archived',0)->condition('b.complete',0);
      $finalCondition = db_and()->condition(db_or()->condition($interactiveCondition)->condition($batchOverallCondition))->condition($lastCondition);

      $query = db_select('maestro_queue', 'a');
      $query->join('maestro_process', 'b', 'a.process_id = b.id');
      $query->join('maestro_template_data', 'c', 'a.template_data_id = c.id');
      $query->join('maestro_template', 'd', 'b.template_id = d.id');
      $query->fields('a',array('id','status','template_data_id','task_class_name','engine_version','is_interactive'));
      $query->addField('b','id','process_id');
      $query->addField('c','task_class_name','step_type');
      $query->addField('c','handler');
      $query->addField('d','template_name');
      $query->condition($finalCondition);
      $res = $query->execute();
      watchdog('maestro',"CleanQueue: Number of entries in the queue:" . count($res));
      $numrows = 0;
      foreach ($res as $queueRecord) {
        watchdog('maestro',"CleanQueue: processing task of type: {$queueRecord->step_type}");
        $numrows++;
        $this->_processId = $queueRecord->process_id;
        $this->_queueId = $queueRecord->id;

        /* Using the strategy Design Pattern - Pass a new taskclass as the object to the maestro engine execute method */
        $task = $this->executeTask(new $queueRecord->task_class_name($queueRecord));
        if ($task->executionStatus === FALSE) {
          watchdog('maestro',"Failed Task: {$this->_queueId}, Process: {$this->_processId} , Step Type: $this->_taskType");
          watchdog('maestro',"Task Information: ". $task->getMessage());
          //@TODO:  what do we do for a failed task?
          //A task should have some sort of error recovery method
        }
        else {
          //Execution successful.  Complete the task here.
          //We will always complete a task, regardless of its task type.
          $this->completeTask($this->_queueId);
          $this->_archiveStatus=$task->getArchiveStatus();
          //@TODO:  any post complete task hooks?
          $this->nextStep();
        }
      }
      if ($numrows == 0 AND $this->_debug) {
        watchdog('maestro','cleanQueue - 0 rows returned.  Nothing in queue.');
      }
      return $this;
    }



    function nextStep() {
        if ($this->_debug ) {
            watchdog('maestro', "nextStep: QueueId: $this->_queueId, ProcessId: $this->_processId");
        }
        // using the queueid and the processid, we are able to create or generate the
        // next step or the regenerated next step in a new process
        $query = db_select('maestro_queue', 'a');
        //if the archive status explicitly says that we're looking at a false condition from an IF, use the false path instead
        if($this->_archiveStatus == MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE) {
          $query->addField('b','template_data_to_false','taskid');
        }
        else {
          $query->addField('b','template_data_to','taskid');
        }
        $query->fields('c',array('task_class_name','is_interactive','reminder_interval'));
        $query->join('maestro_template_data_next_step', 'b', 'a.template_data_id = b.template_data_from');
        if($this->_archiveStatus == MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE) {
          $query->join('maestro_template_data', 'c', 'c.id = b.template_data_to_false');
        }
        else {
          $query->join('maestro_template_data', 'c', 'c.id = b.template_data_to');
        }
        $query->condition('a.process_id',$this->_processId,'=');
        $query->condition('a.id',$this->_queueId,'=');
        $nextTaskResult = $query->execute();

        $nextTaskRows = $query->countQuery()->execute()->fetchField();
        watchdog('maestro',"nextStep: Number of next task records: $nextTaskRows");
        if ($nextTaskRows == 0 ) {
            // There are no rows for this specific queueId and nothing for this processId, there's no next task
            $this->archiveTask($this->_queueId, $this->_archiveStatus);
            db_update('maestro_process')
              ->fields(array('complete' => 1, 'completed_date' => time()))
              ->condition('id', $this->_processId, '=')
              ->execute();

        } else { // we've got tasks
            foreach ($nextTaskResult as $nextTaskRec) {
                if ($this->_debug ) {
                    watchdog('maestro',"Got tasks  qid: {$this->_queueId}, pid: {$this->_processId} and Next taskid: {$nextTaskRec->taskid}");
                }
                // Check if the next template id is null, ensures that if we're on the last task and it points to null, that we end it properly
                if ($nextTaskRec->taskid == null or $nextTaskRec->taskid == '' ) {
                    // Process is done, set the process status to complete and archive queue item
                    $this->archiveTask($this->_queueId, $this->_archiveStatus);
                    db_update('maestro_process')
                      ->fields(array('complete' => 1, 'completed_date' => time()))
                      ->condition('id', $this->_processId, '=')
                      ->execute();
                }
                else {
                    // we have a next step, thus we can archive the queue item and also insert a
                    // new queue item with the next step populated as the next template_stepid

                    $query = db_select('maestro_queue', 'a');
                    $query->addField('a','id');
                    $query->addExpression('COUNT(a.id)','rec_count');
                    $query->groupBy('a.id');
                    $query->condition('a.process_id', $this->_processId,'=');
                    $query->condition('a.template_data_id', $nextTaskRec->taskid,'=');
                    $nextTaskQueueRec = $query->execute()->fetchObject();
                    if ($nextTaskQueueRec == FALSE OR $nextTaskQueueRec->rec_count == 0 ) {
                        $this->archiveTask($this->_queueId, $this->_archiveStatus);
                        if ($nextTaskRec->reminder_interval > 0) {
                            $next_reminder_date = time() + $nextTaskRec->reminder_interval;
                        }
                        else {
                          $next_reminder_date = 0;
                        }
                        // No next item in the queue.. just create it
                        $queue_record = new stdClass();
                        $queue_record->process_id = $this->_processId;
                        $queue_record->template_data_id = $nextTaskRec->taskid;
                        $queue_record->task_class_name = $nextTaskRec->task_class_name;
                        $queue_record->is_interactive =$nextTaskRec->is_interactive;
                        $queue_record->status = 0;
                        $queue_record->archived = 0;
                        $queue_record->engine_version = $this->_version;
                        $queue_record->created_date = time();
                        $queue_record->next_reminder_date = $next_reminder_date;
                        // Instantiate the tasktype specific method to set the queue record task data
                        $taskdata = $this->prepareTask(new $nextTaskRec->task_class_name($nextTaskRec));
                        if (isset($taskdata) AND is_array($taskdata)) {
                          if (isset($taskdata['handler'])) $queue_record->handler = $taskdata['handler'];
                          if (isset($taskdata['serialized_data'])) $queue_record->task_data = $taskdata['serialized_data'];
                        }

                        drupal_write_record('maestro_queue',$queue_record);

                        // Test that we have a new queue record and then set $this->_queueId for use by class methods
                        if ($queue_record->id > 0) {
                          $this->_queueId = $queue_record->id;
                          if ($this->_debug ) {
                              $logmsg  = "New queue id (3) : {$this->_queueId} - Template Taskid: {$nextTaskRec->taskid} - ";
                              $logmsg .= "Assigned to " . $this->getTaskOwner($nextTaskRec->taskid,$this->_processId);
                              watchdog('maestro', $logmsg);
                          }
                        }
                        else {
                          watchdog('maestro', "nextStep Method FAIL! - Unexpected problem creating queue record");
                        }
                        $newTaskAssignedUsers = $this->getAssignedUID();
                        if (is_array($newTaskAssignedUsers) AND count($newTaskAssignedUsers) > 0) {
                            $this->assignTask($this->_queueId,$newTaskAssignedUsers);
                        }
                        $next_record = new stdClass();
                        $next_record->queue_id = $this->_queueId;
                        $next_record->from_queue_id = $nextTaskRec->taskid;
                        drupal_write_record('maestro_queue_from',$next_record);

                        // Check if notification has been defined for new task assignment
                        $this->sendTaskAssignmentNotifications();

                    }
                    else {
                        // we have a situation here where the next item already exists.
                        // need to determine if the next item has a regeneration flag.
                        // if there is a regeneration flag, then create a new process starting with that regeneration flagged item
                        $query = db_select('maestro_template_data', 'a');
                        $query->fields('a',array('id','regenerate','template_id'));
                        $query->addExpression('COUNT(id)','rec_count');
                        $query->groupBy('a.regenerate');
                        $query->groupBy('a.template_id');
                        $query->groupBy('a.id');
                        $query->condition('a.id', $nextTaskRec->taskid,'=');
                        $regenRec = current($query->execute()->fetchAll());

                        if ($regenRec->regenerate == 1) {
                            // regenerate the same process starting at the next step
                            // set the current process' complete status to 2.. 0 is active, 1 is done, 2 is has children
                            $this->newProcess($regenRec->template_id, $nextTaskRec->taskid, $this->_processId);
                            $this->archiveTask($this->_queueId, $this->_archiveStatus);

                        }
                        else {
                            //no regeneration  we're done
                            $toQueueID = $nextTaskQueueRec->id;
                            $next_record = new stdClass();
                            $next_record->queue_id = $regenRec->id;
                            $next_record->from_queue_id = $this->_queueId;
                            drupal_write_record('maestro_queue_from',$next_record);
                            $this->archiveTask($this->_queueId, $this->_archiveStatus);

                            $query = db_select('maestro_queue', 'a');
                            $query->addExpression('COUNT(id)','rec_count');
                            $query->condition('a.process_id', $this->_processId,'=');
                            $query->condition('a.template_data_id', $nextTaskRec->taskid,'=');
                            if ($query->execute()->fetchField() == 0 ) {
                              db_update('maestro_process')
                                ->fields(array('complete' => 1, 'completed_date' => time()))
                                ->condition('id', $this->_processId, '=')
                                ->execute();
                            }
                        }
                    }
                }
            } // end for $nextstep
        } //end else portion for nextStepTest=0
        return $this;
    }


    /**
    * Method assign task - create productionAssignment Record and test if to-be-assigned user has their out-of-office setting active
    * @param        int         $queueID     Task ID from the workflow queue table
    * @param        array       $assignemnt  Array of records where the key is the variable id  if applicable and the user id
                                             If the assignment is by user, the key will be 0 or a negative value - in the case of multiple assignments
    * @return       n/a         No return
    */
    function assignTask($queueId,$userObject) {
        foreach ($userObject as $processVariableId => $userId) {
            if (strpos($userId, ':') !== false) {
                $userIds = explode(':', $userId);
            }
            else {
                $userIds = array($userId);
            }

            foreach ($userIds as $userId) {
              $userId = intval($userId);
              /* The array of users to be assigned may be an array of multiple assignments by user not variable
               * In this case, we can not have multiple array records with a key of 0 - so a negative value is used
              */

              if($processVariableId < 0) $processVariableId = 0;
              if ($userId > 0) {
                $query = db_select('maestro_user_away', 'a');
                $query->fields('a',array('away_start','away_return','is_active'));
                $query->condition('a.uid',$userId,'=');
                $res1 = $query->execute()->fetchObject();
                if ($res1) {
                  // Check if user is away - away feature active and current time within the away window
                  if ($res1->is_active == 1 AND time() > $res1->away_start AND time() < $res1->away_return) {
                      /* User is away - determine who to re-assign task to */
                      $assignToUserId = $this->getAwayReassignmentUid($userId);
                      // If we have a new value for the assignment - then we need to set the assignBack field
                      if ($assignToUserId != $userId) {
                          $assignBack = $userId;
                      }
                      else {
                          $assignBack = 0;
                      }
                  }
                  else {
                      $assignToUserId = $userId;
                      $assignBack = 0;
                  }
                }
                else {
                    $assignToUserId = $userId;
                    $assignBack = 0;
                }
              }
              else {
                  $assignToUserId = 0;
                  $assignBack = 0;
              }

              // Check and see if we have an production assignment record for this task and processVariable
              $query = db_select('maestro_production_assignments', 'a');
              $query->addField('a','uid');
              $query->condition('a.task_id',$queueId,'=');
              if ($processVariableId > 0) {
                $query->condition('a.process_variable',$processVariableId,'=');
              }
              else {
                $query->condition('a.process_variable',0,'=');
                $query->condition('a.uid',$userId,'=');
              }
              $res2 = $query->execute();
              $numrows = $query->countQuery()->execute()->fetchField();
              if ($numrows < count($userIds)) {
                db_insert('maestro_production_assignments')
                  ->fields(array('task_id','uid','process_variable','assign_back_uid','last_updated'))
                  ->values(array(
                    'task_id' => $queueId,
                    'uid' => $assignToUserId,
                    'process_variable' => $processVariableId,
                    'assign_back_uid' => $assignBack,
                    'last_updated'  => time()
                    ))
                  ->execute();
              }
              else {
                db_update('maestro_production_assignments')
                  ->fields(array('uid' => $assignToUserId, 'last_updated' => time(), 'assign_back_uid' => $assignBack))
                  ->condition('task_id', $queueId, '=')
                  ->condition('process_variable',$processVariableId,'=')
                  ->execute();
              }
            }
        }
    }



    function getAssignedUID() {
      $assigned = array();
      $query = db_select('maestro_queue', 'a');
      $query->join('maestro_template_data', 'b', 'a.template_data_id = b.id');
      $query->fields('a',array('template_data_id','is_interactive'));
      $query->fields('b',array('assigned_by_variable'));
      $query->condition('a.id', $this->_queueId,'=');
      $queueRec = $query->execute()->fetchObject();
      if ($queueRec->is_interactive) { // Only need to create assignment records for interactive tasks
        if($queueRec->assigned_by_variable == 1 || $queueRec->assigned_by_variable == true) {
          $query = db_select('maestro_template_assignment', 'a');
          $query->join('maestro_process_variables', 'b', 'b.template_variable_id = a.process_variable');
          $query->fields('a',array('template_data_id','process_variable'));
          $query->fields('b',array('variable_value'));
          $query->condition('a.template_data_id', $queueRec->template_data_id,'=');
          $query->condition('b.process_id', $this->_processId,'=');
          $processRecResult = $query->execute();
          foreach ($processRecResult as $processRec) {
            $assigned[$processRec->process_variable] = $processRec->variable_value;
          }
        }
        else {
          $result = db_query("SELECT uid FROM {maestro_template_assignment} WHERE template_data_id = :template_data_id AND uid is not NULL",
          array('template_data_id' => $queueRec->template_data_id));
          /* Create an array of assignment records - but if there are multple assignments for this task by user then
          * we need to create multiple array records but can not have multiple array records with a key of 0
          * In this case, use a negative key value. Any non positive value is therefore not a variableID and
          * can be assumed to be an assignment by user record
          */
          $cntr = 0;
          foreach ($result as $rec) {
            $assigned[-$cntr] = $rec->uid;  // Possible negative value for key if multiple assignments
            $cntr++;
          }
        }

        if (count($assigned) == 0) {
          // Valid interactive task that should have an assignment record
          $assigned[0] = 0;
        }
      }

      return $assigned;

    }

    function sendTaskAssignmentNotifications () { }

    function sendTaskCompletionNotifications () { }

    function completeTask($qid) {
      $pid = db_query("SELECT process_id FROM {maestro_queue} WHERE id = :qid",
              array(':qid' => $qid))->fetchField();

      if (empty($pid)) {
          watchdog('maestro',"Task ID #$qid no longer exists in queue table.  It was potenially removed by an admin from outstanding tasks.");
          return FALSE;
      }

      if ($this->_debug ) {
          watchdog('maestro',"Complete_task - updating queue item: $qid");
      }

      // Update Project Task History record as completed
      // RK - lets check if there's even an entry for this task first.  if there's no entry, create one
      // This takes into account those flows that do NOT have taskhistory records (non-'project' flows);
      $query = db_select('maestro_project_task_history', 'a');
      $query->addExpression('COUNT(id)','rec_count');
      $query->condition('a.task_id', $qid,'=');
      if ($query->execute()->fetchField() > 0 ) {
          db_update('maestro_project_task_history')
            ->fields(array('status' => 1, 'date_completed' => time()))
            ->condition('task_id',$qid,'=')
            ->condition('status',0,'=')
            ->execute();
      } else {
          $dateCreated = db_query("SELECT created_date FROM {maestro_queue} WHERE id = :qid",
              array(':qid' => $qid))->fetchField();
          $history_record = new stdClass();
          $history_record->task_id = $qid;
          $history_record->process_id = $pid;
          $history_record->date_assigned = $dateCreated;
          $history_record->date_started = $dateCreated;
          $history_record->date_completed = time();
          $history_record->status = 1;
          drupal_write_record('maestro_project_task_history',$history_record);
      }

      if ($this->_userId == '' or $this->_userId == null ) {
          $currentUid = db_query("SELECT uid FROM {maestro_production_assignments} WHERE task_id = :qid",
              array(':qid' => $qid))->fetchField();

          if ($currentUid == '' OR $currentUid == null) {
            db_update('maestro_queue')
              ->fields(array('uid' => NULL, 'status' => 1))
              ->condition('id',$qid,'=')
              ->execute();
          } else {
            db_update('maestro_queue')
              ->fields(array('uid' => $currentUid , 'status' => 1))
              ->condition('id',$qid,'=')
              ->execute();
          }
      } else {
          db_update('maestro_queue')
            ->fields(array('uid' => $this->_userId , 'status' => 1))
            ->condition('id',$qid,'=')
            ->execute();
      }
      // Self Prune Production Assignment table - delete the now completed task assignment record
      db_delete('maestro_production_assignments')
        ->condition('task_id',$qid,'=')
        ->execute();

      $this->sendTaskCompletionNotifications();

    }

    function archiveTask($qid,$status=0) {
        // Set the status field to completed if not set
        $setstatus = '';
        if ($status == 0) {
            // If status has no current value then set the status to 1 (completed)
            $currentStatus = db_query("SELECT status FROM {maestro_queue} WHERE id = :qid",
              array(':qid' => $qid))->fetchField();
            if ($currentStatus == 0) $status = 1;
        }
        if ($status > 0) {
          db_update('maestro_queue')
            ->fields(array('status' => $status, 'completed_date' => time(), 'archived' => 1))
            ->condition('id',$qid,'=')
            ->execute();
        } else {
          db_update('maestro_queue')
            ->fields(array('completed_date' => time(), 'archived' => 1))
            ->condition('id',$qid,'=')
            ->execute();
        }

        // Self Prune Production Assignment table - delete the now completed task assignment record
        db_delete('maestro_production_assignments')
          ->condition('task_id',$qid,'=')
          ->execute();
    }


    function cancelTask($queueId) {}

    function getQueue() {
        if (!empty($this->_userId) AND $this->_userId > 0) {
         /* Instance where the user id is known.  need to see if there is a processID given.
          * This means that the mode in which we're working is user based.. we only care about a user in this case
          */
          $this->_mode = 'user';
          if ($this->_debug ) {
              watchdog('maestro',"Entering getQueue - user mode");
          }
          $this->_userTaskCount = 0;
          $query = db_select('maestro_queue', 'a');
          $query->join('maestro_template_data', 'b', 'a.template_data_id = b.id');
          $query->join('maestro_production_assignments', 'c', 'a.id = c.task_id');
          $query->fields('a',array('id','template_data_id','process_id','is_interactive','handler','task_data','created_date','started_date'));
          $query->fields('b',array('task_class_name','template_id','taskname','is_dynamic_taskname','dynamic_taskname_variable_id'));
          $query->condition('c.uid',$this->_userId,'=');
          $query->condition(db_or()->condition('a.archived',0)->condition('a.archived',NULL));
          $userTaskResult = $query->execute();
          $numTaskRows = $query->countQuery()->execute()->fetchField();
          if ($numTaskRows == 0) {
            if ($this->_debug ) {
              watchdog('maestro',"getQueue - 0 rows returned.  Nothing in queue for this user: {$this->_userId}.");
            }
          }
          else {
            // Return a semi-colon delimited list of queue id's for that user.
            foreach ($userTaskResult as $userTaskRecord) {
              if ($this->_queueId == '' ) {
                $this->_queueId = $userTaskRecord->id;
              } else {
                $this->_queueId .= ";" . $userTaskRecord->id;
              }

              // Simple test to determine if the task ID already exists for this user
              $flag = 0;
              for($flagcntr = 0;$flagcntr <= $this->_userTaskCount;$flagcntr++ ) {
                if (isset($this->_userTaskObject[$flagcntr]->queue_id) AND $this->_userTaskObject[$flagcntr]->queue_id == $userTaskRecord->id ) {
                  $flag = 1;
                }
              }
              if ($flag == 0 ) {
                $taskObject = new stdClass();
                $templatename = db_query("SELECT template_name FROM {maestro_template} WHERE id = :tid",
                  array(':tid' => $userTaskRecord->template_id))->fetchField();
                $queueRecDates = array('created' => $userTaskRecord->created_date, 'started' => $userTaskRecord->started_date);
                $queueRecFlags = array('is_interactive' => $userTaskRecord->is_interactive);
                $taskObject->queue_id = $userTaskRecord->id;
                $taskObject->process_id = $userTaskRecord->process_id;
                $taskObject->template_id = $userTaskRecord->template_id;
                $taskObject->template_name = $templatename;
                $taskObject->url = $userTaskRecord->handler;
                $taskObject->dates = $queueRecDates;
                $taskObject->flags = $queueRecFlags;

                // Handle dynamic task name based on a variable's value
                $taskname = '';
                if($userTaskRecord->is_dynamic_taskname == 1) {
                  $q2 = db_select('maestro_process_variables', 'a');
                  $q2->addField('a','variable_value');
                  $q2->condition('a.process_id',$userTaskRecord->process_id,'=');
                  $q2->condition('a.template_variable_id',$userTaskRecord->dynamic_taskname_variable_id,'=');
                  $res1 = $query->execute()->fetchObject();
                  if ($res1) {
                    $userTaskRecord->taskname = $res1->variable_value;
                  }
                }
                /* @TODO: Need to look at using a module HOOK that can be used in a similar way to define an custom taskname */
                /*
                if (function_exists('PLG_Nexflow_taskname')) {
                  $parms = array('pid' => $A['nf_processID'], 'tid' => $A['nf_templateDataID'], 'qid' => $A['id'], 'user' => $this->_nfUserId);
                  if (!empty($taskame)) {
                    $apiRetval = PLG_Nexflow_taskname($parms,$taskname);
                  } else {
                    $apiRetval = PLG_Nexflow_taskname($parms,$A['taskname']);
                  }
                  $taskname = $apiRetval['taskname'];
                }
                */

                $taskObject->taskname = $userTaskRecord->taskname;
                $taskObject->tasktype = $userTaskRecord->task_class_name;
                $this->_userTaskObject[$this->_userTaskCount] = $taskObject;
                $this->_userTaskCount += 1; // Increment the total user task counter
              }
            }
          }
        }

        if ($this->_debug ) {
            watchdog('maestro',"Exiting getQueue - user mode");
        }
        return $this->_userTaskObject;
    }



  }