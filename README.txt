
August 24/2010

INSTALLATION INSTRUCTIONS
-------------------------
The installation of Maestro should be done in the sites/all/modules folder structure.
Do NOT install Maestro in the core modules directory.
Maestro ships with 3 add-on modules: Common Functions, Content Publish and Maestro Test Workflow Patterns.
We recommend that you install all 3 modules to begin.  Common Functions and Content Publish will enable functions/functionality
that is used in the tasks shipped with Maestro.  The Test Workflow patterns module is strongly suggested to get you up and
running and familiar with Maestro.  It will install a handful of workflows that give you examples to begin structuring your workflows with.

During the installation of the Maestro Test Workflow Patterns module, a content type test workflow is installed.
The content type test workflow pattern requires at least 3 distinct users -- the person initiating the workflow, a user named Editor
and a user named Publisher.  Since you probably don't have those users in your system, the import will not be able to assign two
of the tasks to those users.

You will have to do one of the following two things to ensure the Content Type test workflow works for you:

1.  Edit the Test Content Type Task workflow and assign the Editor Review Article to an existing user in your system.
    Edit the Publisher Review Article Task and assign it to an existing user in your system.

  OR

2. Create an Editor and Publisher user.  Assign the Editor Review Article to the Editor user and assign the Publisher Review Article task
  to the Publisher user.


CONFIGURATION INSTRUCTIONS
--------------------------
You will find the Maestro base configuration options under the Configuration menu.  Maestro is found under the Workflow category and
is listed as Maestro Config.  Out of the box, you will find that Maestro has a few default settings enabled.

THIS IS IMPORTANT!! PLEASE READ!
One of the settings is "Run the Orchestrator when the Task Console Renders".  This setting allows the Maestro engine to run
when you click on the Task Console link in the Nav menu.  If you uncheck this option, the engine will not run on task console refresh.  

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

While the orchestrator can be run through hits to the Task Console, This is NOT an optimal configuration and is
only there for testing. We have enabled the option to run the Orchestrator through the task console rendering by
default for ease of use, but that can be disabled on the Maestro configuration page.

To set up the cron, see http://drupal.org/cron. Note however in this documentation, instead of (or in addition to)
using http://www.example.com/cron.php as the url, use http://www.example.com/maestro/orchestrator or 
http://www.example.com/index.php?q=maestro/orchestrator. The orchestrator cron should be set to run every few minutes.

Current release of Maestro does not have a "secured" orchestrator link.  Therefore anyone can hit the maestro/orchestrator link and run 
the orchestrator.  While this is not necessarily harmful, it is not optimal as the engine will run and potentially be run more than once
at the same time causing queue issues.  Eventually there will be an application token that would have to be passed to the orchestrator link
in order to run the orchestrator from cron. However for now, be aware there are no safeguards around it.


FOR WINDOWS USERS - THE ORCHESTRATOR
------------------------------------
Until we release a Service for the Orchestrator, your best bet is to use a simple started task using the Task Scheduler.
Place the following code in to a vbs file and have the task scheduler run it:

CONST URL="http://FQDNofTHEserver/?q=maestro/orchestrator"
CONST USER=""
CONST PASSWORD=""
CONST MESSAGES=0
CONST SLEEPTIME=2000 'sleep for 2 seconds
ON ERROR RESUME NEXT

set WshShell = WScript.CreateObject("WScript.Shell")
while true
  runOrchestrator()
  WScript.Sleep SLEEPTIME
wend

sub runOrchestrator
  set objHttp= CreateObject("Msxml2.ServerXMLHttp")
  objHttp.open "GET", url ,false ,USER,PASSWORD
  objHttp.send()
  str=objHttp.responseText
  if MESSAGES then msgbox str
end sub


*NOTES: 
- change the FQDNofTHEserver setting to match your server.
- The choice is yours, but here are some useful settings for the started task:

Program/script:
c:\windows\system32\cscript.exe

Add Arguments (optional):
//B //Nologo path\to\your\vbs\orchestrator.vbs




