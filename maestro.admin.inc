<?php
// $Id:

/**
 * @file
 * maestro.admin.inc
 */


function maestro_admin() {
    return theme('maestro_workflow_list', array());
}

function maestro_edit_workflow($id) {
    return theme('maestro_workflow_edit', array('id' => $id));
}

