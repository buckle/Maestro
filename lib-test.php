<?php  

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

function maestro_enginetest() {
  
include_once './' . drupal_get_path('module', 'maestro') . '/maestro.class.php';
include_once './' . drupal_get_path('module', 'maestro') . '/maestro_tasks.class.php';
echo "<br>Instantiate the Maestro class";
$options = array('color1' => 'red');     
$maestro = Maestro::createMaestroObject(1,$options);    // Initiate the processing of all tasks of type 1.x
if ($maestro) {
  echo '<br>';
  print_r($maestro);  
  echo "<br>Sucessfully launched the Maestro class version: " . $maestro->engine->getVersion() . '<br>';
} else {
  echo "<br>Failed to launch a new Maestro class.";
  exit;
}


$template = 1;
$newprocess = $maestro->engine->newProcess($template);  
if ($newprocess > 0) {
  echo "New Process Code Success! - Process ID: $newprocess";      
} else {
  echo "New Process Code FAIL! - Template: $template not defined";    
}

echo '<hr>';
echo 'Attempt to launch 2nd instance of engine - version 2 this time.';
$maestro2 = Maestro::createMaestroObject(2,$options);    // Initiate the processing of all tasks of type 2.x 
if ($maestro2) {
  echo '<br>';
  print_r($maestro2);  
  echo "<br>Sucessfully launched the Maestro class version: " . $maestro2->engine->getVersion() . '<br>';
} else {
  echo "<br>Failed to launch a new Maestro class.";
}

echo '<hr>';
//echo 'Attempt to execute task.<br>';
//echo $maestro->engine->executeTask(new MaestroTaskTypeStart(array('taskparm1' => '1')) );
//echo $maestro->engine->executeTask(new MaestroTaskTypeAnd(array('taskparm1' => '4')) );
//echo $maestro->engine->executeTask(new MaestroTaskTypeBatch(array('taskparm1' => '3')) );
//echo $maestro->engine->executeTask(new MaestroTaskTypeEnd(array('taskparm1' => '2')) );
     
    
$query = db_select('users');
$query->addExpression(100,'id');
$query->fields('users',array('uid','name'));
$result = $query->execute(); 
foreach ($result as $rec) {
  //print_r($rec);
}      
            
 
echo '<h1>End of test</h1>';

}
