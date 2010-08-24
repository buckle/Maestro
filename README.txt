// $Id:

August 24/2010

INSTALLATION INSTRUCTIONS
-------------------------
The installation of Maestro should be done in the sites/all/modules folder structure.  
Do NOT install Maestro in the core modules directory.
Maestro ships with 3 add-on modules: Common Functions, Content Publish and Maestro Test Workflow Patterns.
We recommend that you install all 3 modules to begin.  Common Functions and Content Publish will enable functions/functionality
that is used in the tasks shipped with Maestro.  The Test Workflow patterns module is strongly suggested to get you up and 
running and familiar with Maestro.  It will install a handful of workflows that give you examples to begin structuring your workflows with.

CONFIGURATION INSTRUCTIONS
--------------------------
You will find the Maestro base configuration options under the Configuration menu.  Maestro is found under the Workflow category and
is listed as Maestro Config.  Out of the box, you will find that Maestro has a few default settings enabled.

THIS IS IMPORTANT!! PLEASE READ!
One of the settings is "Run the Orchestrator when the Task Console Renders".  This setting allows the Maestro engine to run
when you click on the Task Console link in the Nav menu.  If you uncheck this option, the engine will not run.  This is an ALPHA
release of Maestro.  So be advised that the Orchestrator will have its own asynchronous way to fire as we draw closer to a BETA release.

The other options are:

-Enable the import window in the Template Editor:  
    You will be unable to do manual IMPORTS of workflows without this setting turned on.  If you try to use the IMPORT option on the 
    Maestro Workflow main editor page, you will get an error.  

-Enable Maestro Notifications:
    You have the ability to globally turn on/off all notifications sent out by Maestro.  
    Check this on to enable, check if off to disable.
    
-Select Which Notifiers to Enable:
    This gives you fine grain control over which specific notifications to actually enable.  
    Its a multi select, so choose the notifications you want to use.
    
    
    
THE ORCHESTRATOR
----------------
The whole point of Maestro is that it has an engine that can (and should) run independently of end-user clicks.
The way this is accomplished is through a mechanism we call the Orchestrator.  The Orchestrator does exactly what it sounds like it does:
it orchestrates the execution of tasks and marshalls the engine properly.

By default for this ALPHA we have only "shipped" Maestro with the orchestrator to run through hits to the Task Console.
This is NOT an optimal configuration and is only there for testing.  We have enabled the option to run the Orchestrator through
the task console rendering by default for this ALPHA.

Over the very near future, we will release an Orchestrator script for both *NIX systems and Windows.  You will be able to run the
Orchestrator through a cron job or scheduled task.  Please, if you are for some reason running Maestro in production, it is NOT
recommended to run the Orchestrator through Task Console refreshes.





