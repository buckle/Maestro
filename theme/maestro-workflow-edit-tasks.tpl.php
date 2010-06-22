<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit-tasks.tpl.php
 */

  $res = db_query('SELECT id, step_type FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $tid));

?>



<?php

?>
