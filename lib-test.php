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
  echo "<br>Sucessfully launched the Maestro class version: " . $maestro->engine->getVersion();
} else {
  echo "<br>Failed to launch a new Maestro class.";
} 

echo '<hr>';
echo 'Attempt to launch 2nd instance of engine - version 2 this time.';
$maestro2 = Maestro::createMaestroObject(2,$options);    // Initiate the processing of all tasks of type 2.x 
if ($maestro2) {
  echo '<br>';
  print_r($maestro2);  
  echo "<br>Sucessfully launched the Maestro class version: " . $maestro2->engine->getVersion();
} else {
  echo "<br>Failed to launch a new Maestro class.";
}

echo '<hr>';
echo 'Attempt to execute task.<br>';
echo $maestro->engine->executeTask('start',array('taskparm1' => '1'));
echo $maestro->engine->executeTask('and',array('taskparm1' => '4'));
echo $maestro->engine->executeTask('batch',array('taskparm1' => '3'));
echo $maestro->engine->executeTask('end',array('taskparm1' => '2'));

echo '<h1>End of test</h1>';