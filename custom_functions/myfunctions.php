<?php
// $Id:

/**
 * @file
 * myfunctions.php
 */

function maestro_showmessage($msg) {
  $retval = '<div style="text-align:center;margin:5px;padding:10px;border:1px solid #CCC;font-size:14pt;">';
  $retval .= $msg;
  $retval .= '</div>';

  return $retval;
}



?>
