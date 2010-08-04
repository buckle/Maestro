<?php
// $Id$

/**
 * @file
 * batch_functions.php
 *
 * Description:  Repository for all Maestro batch processing functions.
 */



  /* Sample Batch Function
   * @param $queueID:
   *   The currently operating Maestro Queue ID for the task in the active queue
   *
   * @param $processID
   *   The process ID associated with this record
   *
   *
   * @return
   *   Nothing.  You MUST set a variable called $success to TRUE for a valid operation.  FALSE for failure.
   */
function MaestroBatch_Sample($queueID, $processID){
  global $success;

  $success = TRUE;
}


function maestro_publishArticle($queue_id, $process_id) {
  $nid = db_query("SELECT nid FROM {maestro_project_content} WHERE process_id = :pid AND content_type='article'",
      array(':pid' => $process_id))->fetchField();
  $node = node_load($nid);
  node_publish_action($node);
  node_save($node);
  drupal_set_message('New article has been published');
  return TRUE;

}