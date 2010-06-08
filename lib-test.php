<?php  

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include_once './' . drupal_get_path('module', 'maestro') . '/maestro.class.php';
include_once './' . drupal_get_path('module', 'maestro') . '/maestro_tasks.class.php';
echo "<br>Instantiate the Maestro class";
$maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x  
echo "<br>Sucessfully launched the Maestro class version: " . $maestro->getVersion;
