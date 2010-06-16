<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include_once './' . drupal_get_path('module', 'maestro') . '/maestro.class.php';
include_once './' . drupal_get_path('module', 'maestro') . '/maestro_tasks.class.php';
echo "<br>Instantiate the Maestro class";
$options = array('color1' => 'red');
$maestro = Maestro::createMaestroObject(1,$options);    // Initiate the processing of all tasks of type 1.x
if ($maestro) {
  echo '<br>';
  print_r($maestro);
  echo "<br>Sucessfully launched the Maestro class version: " . $maestro->engine()->getVersion() . '<br>';
} else {
  echo "<br>Failed to launch a new Maestro class.";
  exit;
}

function maestro_enginetest() {

  $maestro = Maestro::createMaestroObject(1,$options);    // Initiate the processing of all tasks of type 1.x
  $template = 1;
  $newprocess = $maestro->engine()->newProcess($template);
  if ($newprocess > 0) {
    echo "New Process Code Success! - Process ID: $newprocess";
  } else {
    echo "New Process Code FAIL! - Template: $template not defined";
  }

  echo '<hr>';
  echo '<h1>End of test</h1>';

}

function maestro_runengine() {
  $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
  $maestro->engine()->cleanQueue();
  //lets clean the queue
  //$maestro->engine()->cleanQueue();
  //$maestro->engine()->nextStep();

}


