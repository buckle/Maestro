<?php

//Id:


/**
 * @file
 * maestro_notification.class.php
 */

/*
 * We are implementing an observer pattern to accomplish our notifications.
 * Any additional module that would like to create a notification simply
 * has to subscribe/attach to the main notification object as an observer
 * and the main notification mechanism will push out the notification to them.
 * I've included our email observer in this file as well as a Skeletal Twitter observer pattern.
*/

abstract class MaestroNotificationObserver {
  public $displayName;

  function __construct() {
    $this->displayName = "";  //You must give the observer a friendly display name so that the admin console can display it
  }

  abstract function notify(MaestroNotification &$obj);
}

class MaestroNotificationTypes {
  CONST GENERAL = 'general_notification';
  CONST ASSIGNMENT = 'assignment_notification';
  CONST REMINDER = 'reminder_notification';
  CONST COMPLETION = 'completion_notification';
  CONST ESCALATION = 'escalation_notification';
}


class MaestroNotification {
  protected $_userIDArray = array();
  protected $_userEmailArray = array();
  protected $_observers = array();
  protected $_message = "";
  protected $_subject = "";
  protected $_queueID = 0;
  protected $_notificationType = "";

  /**
   * Constructor
   *
   * @param $users
   *   Mandatory - An array of integers or single integer specifying the Drupal users to notify.
   *
   * @param $message
   *   String: The actual message to send in the email.
   *
   * @param $subject
   *   String: The email subject
   *
   * @param $queueID
   *   Integer: The QueueID associated with the message you're sending out
   *
   * @param $type
   *   String: The actual notification type using the MaestroNotificationTypes Constants
   */
  function __construct($message = '', $subject = '', $queueID = 0, $type = MaestroNotificationTypes::ASSIGNMENT) {
    $this->_subject = $subject;
    $this->_notificationType = $type;
    $observers = array();
    $this->_message = $message;
    $this->_queueID = $queueID;
    $this->getNotificationUserIDs();

    //Now, lets determine if we've got our observers cached.  If not, lets rebuild that observer list
    //This is how we subscribe to becoming a notification provider for Maestro.
    $observers = cache_get('maestro_notification_observers');
    if($observers === FALSE) {  //build the observer cache
      //need to scan through each available class type and fetch its corresponding context menu.
      foreach (module_implements('maestro_notification_observer') as $module) {
        $function = $module . '_maestro_notification_observer';
        if ($declaredObserver = $function()) {
          foreach($declaredObserver as $observerToCache) {
            $observers[] = $observerToCache;
            $this->_observers[] = $observerToCache;
          }
        }
      }
      cache_set('maestro_notification_observers', $observers);
    }
    else {
      $this->_observers = ($observers->data);
    }
  }

  function getNotificationUserIDs() {
    if(intval($this->_queueID) > 0 && $this->_notificationType != '') {
      $query = db_select('maestro_queue', 'a');
      $query->fields('a', array('process_id'));
      $query->fields('b', array('id', 'notify_type'));
      $query->leftJoin('maestro_template_data', 'b', 'a.template_data_id=b.id');
      $query->condition('a.id', $this->_queueID, '=');
      $qRec = current($query->execute()->fetchAll());

      $query = db_select('maestro_template_notification', 'a');
      switch($this->_notificationType) {
        case MaestroNotificationTypes::ASSIGNMENT:
          if ($qRec->notify_type == 0) {
            $query->addField('a', 'pre_notify_id', 'uid');
            $query->leftJoin('users', 'c', 'a.pre_notify_id = c.uid');
          }
          else if ($qRec->notify_type == 1) {  //storing the user ids
            $query->addField('b', 'variable_value', 'uid');
            $query->leftJoin('maestro_process_variables', 'b', 'a.pre_notify_id=b.template_variable_id');
            $query->condition('b.process_id', $qRec->process_id, '=');
            $query->leftJoin('users', 'c', 'b.variable_value = c.uid');
          }
          $query->condition('a.pre_notify_id', 0, '>');
          break;

        case MaestroNotificationTypes::REMINDER:
          if ($qRec->notify_type == 0) {
            $query->addField('a', 'reminder_notify_id', 'uid');
            $query->leftJoin('users', 'c', 'a.reminder_notify_id = c.uid');
          }
          else if ($qRec->notify_type == 1) {  //storing the user ids
            $query->addField('b', 'variable_value', 'uid');
            $query->leftJoin('maestro_process_variables', 'b', 'a.reminder_notify_id=b.template_variable_id');
            $query->condition('b.process_id', $qRec->process_id, '=');
            $query->leftJoin('users', 'c', 'b.variable_value = c.uid');
          }
          $query->condition('a.reminder_notify_id', 0, '>');
        break;

        case MaestroNotificationTypes::COMPLETION:
          if ($qRec->notify_type == 0) {
            $query->addField('a', 'post_notify_id', 'uid');
            $query->leftJoin('users', 'c', 'a.post_notify_id = c.uid');
          }
          else if ($qRec->notify_type == 1) {  //storing the user ids
            $query->addField('b', 'variable_value', 'uid');
            $query->leftJoin('maestro_process_variables', 'b', 'a.post_notify_id=b.template_variable_id');
            $query->condition('b.process_id', $qRec->process_id, '=');
            $query->leftJoin('users', 'c', 'b.variable_value = c.uid');
          }
          $query->condition('a.post_notify_id', 0, '>');
        break;

        case MaestroNotificationTypes::GENERAL:
          if ($qRec->notify_type == 0) {
            $query->addField('a', 'pre_notify_id', 'uid');
            $query->leftJoin('users', 'c', 'a.pre_notify_id = c.uid');
          }
          else if ($qRec->notify_type == 1) {  //storing the user ids
            $query->addField('b', 'variable_value', 'uid');
            $query->leftJoin('maestro_process_variables', 'b', 'a.pre_notify_id=b.template_variable_id');
            $query->condition('b.process_id', $qRec->process_id, '=');
            $query->leftJoin('users', 'c', 'b.variable_value = c.uid');
          }
          $query->condition('a.pre_notify_id', 0, '>');
          break;
      }
      $query->condition('a.template_data_id', $qRec->id, '=');
      $query->fields('c', array('mail'));
      $res = $query->execute();
      $this->_userIDArray = array();
      $this->_userEmailArray = array();
      foreach ($res as $rec) {
        $this->_userIDArray[$rec->uid] = $rec->uid;
        $this->_userEmailArray[$rec->uid] = $rec->mail;
      }
    }
    else {
      return FALSE;
    }
  }

  function getQueueId() {
    return $this->_queueID;
  }

  function setQueueId($id) {
    $this->_queueID = $id;
  }

  function getNotificationType() {
    return $this->_notificationType;
  }

  function setNotificationType($type) {
    $this->_notificationType = $type;
  }

  function getSubject() {
    return $this->_subject;
  }

  function setSubject($subject) {
    $this->_subject = $subject;
  }

  function getMessage() {
    return $this->_message;
  }

  function setMessage($message) {
    $this->_message = $message;
  }

  function setUserIDs($userIDs) {
    if(is_array($userIDs) && count($userIDs) > 0) {
      $this->_userIDArray = $userIDs;
    }
    else {
      $this->_userIDArray = array();
      $this->_userIDArray[] = $userIDs;
    }
  }

  function getUserIDs(){
    return $this->_userIDArray;
  }

  function getUserEmailAddresses($userid = 0){
    $userid = intval($userid);
    if($userid == 0) return $this->_userEmailArray;

    return $this->_userEmailArray[$userid];
  }

  public function attach(MaestroNotificationObserver $observer) {
    $this->_observers[] = $observer;
  }

  /*
   * notify method
   * Responsible for pushing out the notifications to the subscribed notification mechanisms
   * Notify will be disabled when the configuration option is disabled.
   */
  public function notify() {
    if(variable_get('maestro_enable_notifications',1) == 1) {
      //we are now going to check if the maestro_enabled_notifiers is set.  If its not set, we will just set all observers to be enabled
      $enabled_notifiers = variable_get('maestro_enabled_notifiers');
      if($enabled_notifiers == NULL) {
        if(is_array($this->_observers) && count($this->_observers) > 0 ) {
          foreach($this->_observers as $obj) {
            if(class_exists($obj)) {
             $notifyObject = new $obj();
              $notifyObject->notify($this);
            }
          }
        }
      }
      else {
        foreach($enabled_notifiers as $obj) {
          if(class_exists($obj)) {
            $notifyObject = new $obj();
            $notifyObject->notify($this);
          }
        }
      }
    }
  }
}


/*
 * Here is the implementation of the observer pattern where we implement the MaestroNotificationObserver interface.
 * The only method we MUST implement is the notify where we accept the passed in object by reference to save memory.
 */

class MaestroEmailNotification extends MaestroNotificationObserver {

  public function __construct() {
    $this->displayName = "Maestro Email Notifier";
  }

  public function notify(MaestroNotification &$obj) {
    //now, we're offloading the notification to this class to do whatever it needs to do.
    $from = variable_get('site_mail', 'admin@example.com');
    $send = TRUE;
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        $to =  $obj->getUserEmailAddresses($userID);
        $params = array('message' => $obj->getMessage(), 'subject' => $obj->getSubject(), 'queueID' => $obj->getQueueId());
        $result = drupal_mail('maestro', $obj->getNotificationType(), $to, language_default(), $params, $from, $send);
      }
    }
  }

}


class MaestroWatchDogNotification extends MaestroNotificationObserver {

  public function __construct() {
    $this->displayName = "Watchdog Notifier";
  }

  public function notify(MaestroNotification &$obj) {
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        watchdog('Maestro', "Notification issued for UserID: ". $userID . " email address: " . $obj->getUserEmailAddresses($userID));
      }
    }
  }
}


/*
 * This is just a sample stub observer pattern for anyone to use and how simple it is to implement
 * You need to enable this observer in the maestro.module file in the maestro_maestro_notification_observer function
 * by adding 'SAMPLEMaestroTwitterNotification' in the return array.  Clear your cache and this observer pattern will
 * automatically be added and subscribed.
 * If you are writing your own Maestro task/notification module, please implement your own MODULENAME_maestro_notification_observer hook
 * and do not edit the main maestro.module file.
 */

class SAMPLEMaestroTwitterNotification extends MaestroNotificationObserver {

  public function __construct() {
    $this->displayName = "Sample Twitter Notifier";
  }

  public function notify(MaestroNotification &$obj) {
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        //send a twitter update however that is done :-)
        //echo "twitter update to userID:" . $userID;
      }
    }
  }
}

