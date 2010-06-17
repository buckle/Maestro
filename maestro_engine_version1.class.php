<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */

  class MaestroEngineVersion1 extends MaestroEngine {

      var $_version = '1.x';
      var $_properties;

      function __construct($options) {
        $this->_properties = $options;
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
        } else {
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
            $process_record->initiated_date = date('Y-m-d H:i:s' );
            drupal_write_record('maestro_process',$process_record);
            $new_processid = $process_record->id;

            if ($process_record->id > 0) {
              $this->_processId = $process_record->id;
            } else {
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
            $queue_record->created_date = date('Y-m-d H:i:s' );
            $queue_record->next_reminder_date = $next_reminder_date;
            drupal_write_record('maestro_queue',$queue_record);
            if ($queue_record->id > 0) {
              $this->_queueId = $queue_record->id;
            } else {
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
                $pvquery->fields('a',array('template_variable_id','variable_value'));
                $pvquery->condition("a.process_id=$pid");
                db_insert('maestro_process_variables')
                  ->fields('process_id','variable_value','template_variable_id')
                  ->from($pvquery)
                  ->execute();


            } else {
                // Situation where this is the root process, inserts the default template variables into the process
                $pvquery = db_select('maestro_template_variables','a');
                $pvquery->addExpression($this->_processId,'process_id');
                $pvquery->fields('a',array('id','variable_value'));
                $pvquery->condition('a.template_id',$template,'=');
                db_insert('maestro_process_variables')
                  ->fields(array('process_id','variable_value','template_variable_id'))
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

            $newTaskAssignedUsers = $this->getAssignedUID($this->_queueId);
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
                } elseif($templaterec->use_project && !empty($pid)) {
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
            } else {
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

        } else {
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
        $task = $this->executeTask(new $queueRecord->task_class_name($queueRecord));
        if ($task->executionStatus === FALSE) {
          watchdog('maestro',"Failed Task: {$this->_queueId}, Process: {$this->_processId} , Step Type: $this->_taskType");
          watchdog('maestro',"Task Information: ". $task->getMessage());
          //@TODO:  what do we do for a failed task?
          //A task should have some sort of error recovery method
        }else{
          //Execution successful.  Complete the task here.
          //We will always complete a task, regardless of its task type.
          $this->completeTask($this->_queueId);
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
        $query->addField('b','template_data_to','taskid');
        $query->addField('c','reminder_interval');
        $query->addField('c','task_class_name');
        $query->join('maestro_template_data_next_step', 'b', 'a.template_data_id = b.template_data_from');
        $query->join('maestro_template_data', 'c', 'c.id = b.template_data_to');
        $query->condition('a.process_id',$this->_processId,'=');
        $query->condition('a.id',$this->_queueId,'=');
        $nextTaskResult = $query->execute();
        $nextTaskRows = $query->countQuery()->execute()->fetchField();
        watchdog('maestro',"nextStep: Number of next task records: $nextTaskRows");
        if ($nextTaskRows == 0 ) {
            // There are no rows for this specific queueId and nothing for this processId, there's no next task
            $this->archiveTask($this->_queueId);
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
                    $this->archiveTask($this->_queueId);
                    db_update('maestro_process')
                      ->fields(array('complete' => 1, 'completed_date' => time()))
                      ->condition('id', $this->_processId, '=')
                      ->execute();
                } else {
                    // we have a next step, thus we can archive the queue item and also insert a
                    // new queue item with the next step populated as the next template_stepid

                    $query = db_select('maestro_queue', 'a');
                    $query->addField('a','id');
                    $query->addExpression('COUNT(a.id)','rec_count');
                    $query->groupBy('a.id');
                    $query->condition('a.process_id', $this->_processId,'=');
                    $query->condition('a.template_data_id', $nextTaskRec->taskid,'=');
                    $nextTaskQueueRec = $query->execute()->fetchObject();

                    if ($nextTaskQueueRec->rec_count == 0 ) {
                        $this->archiveTask($this->_queueId);                       
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
                        $queue_record->status = 0;
                        $queue_record->archived = 0;
                        $queue_record->engine_version = $this->_version;
                        $queue_record->created_date = date('Y-m-d H:i:s' );
                        $queue_record->next_reminder_date = $next_reminder_date;
                        drupal_write_record('maestro_queue',$queue_record);
                        
                        // Test that we have a new queue record and then set $this->_queueId for use by class methods
                        if ($queue_record->id > 0) {
                          $this->_queueId = $queue_record->id;
                          if ($this->_debug ) {
                              $logmsg  = "New queue id (3) : {$this->_queueId} - Template Taskid: {$nextTaskRec->taskid} - ";
                              $logmsg .= "Assigned to " . $this->getTaskOwner($nextTaskRec->taskid,$this->_processId);
                              watchdog('maestro', $logmsg);
                          }
                        } else {
                          watchdog('maestro', "nextStep Method FAIL! - Unexpected problem creating queue record");
                        }

                        $newTaskAssignedUsers = $this->getAssignedUID($this->_queueId);
                        if (is_array($newTaskAssignedUsers) AND count($newTaskAssignedUsers) > 0) {
                            $this->assignTask($this->_queueId,$newTaskAssignedUsers);
                        }
                        $next_record = new stdClass();
                        $next_record->queue_id = $this->_queueId;
                        $next_record->from_queue_id = $nextTaskRec->taskid;
                        drupal_write_record('maestro_queue_from',$next_record);

                        // Check if notification has been defined for new task assignment
                        $this->sendTaskAssignmentNotifications();

                    } else {
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
                            $this->archiveTask($this->_queueId);

                        } else {
                            //no regeneration  we're done

                            $toQueueID = $nextTaskQueueRec->id;
                            $next_record = new stdClass();
                            $next_record->queue_id = $regenRec->id;
                            $next_record->from_queue_id = $this->_queueId;
                            drupal_write_record('maestro_queue_from',$next_record);
                            $this->archiveTask($this->_queueId);

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



    function assignTask($queueId,$userObject) { }

    function getAssignedUID($taskid) {}

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

      if ($this->_userId == '' or $this->userId == null ) {
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


      // Get a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name.
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function getProcessVariable($variable) {}


    function setProcessVariable($variable,$value) {}


  }