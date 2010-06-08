<?php
  
  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */
  
  class Maestro {
      
    private static $MAESTRO;
    var $engine = null;
     
    function createMaestroObject ($version, $options = FALSE){
        echo "<br>Executing createMaestroObject version:$version";       
        if (!isset(self::$MAESTRO)) {
            echo "<br>MAESTRO Object not set";
            // instance does not exist, so create it
            self::$MAESTRO = new self($version, $options);    
        } else {
          echo "<br>MAESTRO Object already exists";
          return FALSE;
        }
        return self::$MAESTRO;
    }

    function __construct($version, $options = FALSE) {
      echo "<br>Executing __construct for the base Maestro class version:$version";
      include_once './' . drupal_get_path('module', 'maestro') . '/maestro_base_engine.class.php';
      include_once './' . drupal_get_path('module', 'maestro') . '/maestro_tasks.class.php';       
      $classfile = drupal_get_path('module','maestro')."/maestro_engine_version{$version}.class.php";
      if (require_once $classfile) {
        $class = "MaestroEngineVersion{$version}";
        echo "<br>Class $class";
        if (class_exists($class)) {
          $this->engine = new $class($options); 
        } else {
          die("maestro.class - Unable to instantiate class $class from $classfile");
        }
      } else {
        die("maestro.class - Unable to include file: $classfile");
      }
    }
}