<?php
// $Id:

/**
 * @file
 * maestro.admin.inc
 */


function maestro_admin() {
  return theme('maestro_workflow_list', array());
}

function maestro_edit_workflow($tid) {
  return theme('maestro_workflow_edit', $tid);
}

