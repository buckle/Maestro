<?php  

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include_once './' . drupal_get_path('module', 'maestro') . '/maestro.class.php';
include_once './' . drupal_get_path('module', 'maestro') . '/maestro_tasks.class.php';
echo "<br>Instantiate the Maestro class";
$options = array('color1' => 'red');     
$maestro = Maestro::createMaestroObject(1,$options);    // Initiate the processing of all tasks of type 1.x 
echo '<br>';
print_r($maestro);
echo "<br>Sucessfully launched the Maestro class version: " . $maestro->engine->getVersion();

echo '<hr>';
echo 'Attempt to launch 2nd instance of engine - type 2 this time>';
$maestro2 = Maestro::createMaestroObject(2,$options);    // Initiate the processing of all tasks of type 2.x 
echo '<br>';
print_r($maestro2);
echo "<br>Sucessfully launched the Maestro class version: " . $maestro2->engine->getVersion();
