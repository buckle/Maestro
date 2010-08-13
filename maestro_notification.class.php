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

interface MaestroNotificationObserver {
  public function notify(MaestroNotification &$obj);
}

class MaestroNotificationTypes {
  CONST GENERAL = 'maestro_general_email_notification';
  CONST ASSIGNMENT = 'maestro_assignment_email_notification';
  CONST REMINDER = 'maestro_reminder_email_notification';
  CONST COMPLETION = 'maestro_completion_email_notification';
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
   *   An array of integers or single integer specifying the Drupal users to notify.
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
  function __construct($users, $message, $subject, $queueID, $type = MaestroNotificationTypes::GENERAL) {
    $this->_subject = $subject;
    $this->_notificationType = $type;
    $observers = array();
    $this->_message = $message;
    $this->_queueID = $queueID;
    if(is_array($users)) {
      $this->_userIDArray = $users;
    }
    else {
      if(is_numeric($users)) {
        $this->_userIDArray[0] = $users;
      }
    }
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

  function getEmailFromUserIDs() {
    if(is_array($this->_userIDArray) && count($this->_userIDArray) > 0) {
      //@TODO: Should we be doing this in the constructor or even at all for the end user?
      //in here, get the users' email addresses
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

  function getUserIDs(){
    return $this->_userIDArray;
  }

  function getUserEmailAddresses(){
    return $this->_userEmailArray;
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
      if(is_array($this->_observers) && count($this->_observers) > 0 ) {
        foreach($this->_observers as $obj) {
          $notifyObject = new $obj();
          $notifyObject->notify($this);
        }
      }
    }
  }
}


/*
 * Here is the implementation of the observer pattern where we implement the MaestroNotificationObserver interface.
 * The only method we MUST implement is the notify where we accept the passed in object by reference to save memory.
 */

class MaestroEmailNotification implements MaestroNotificationObserver {

  public function notify(MaestroNotification &$obj) {
    //now, we're offloading the notification to this class to do whatever it needs to do.
    $from = variable_get('site_mail', 'admin@example.com');
    $send = TRUE;
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        $to = $this->getUserEmailAddressFromUID($userID);
        $params = array('message' => $obj->getMessage(), 'subject' => $obj->getSubject(), 'queueID' => $obj->getQueueId());
        $result = drupal_mail('maestro', $obj->getNotificationType(), $to, language_default(), $params, $from, $send);
      }
    }
  }

  public function getUserEmailAddressFromUID($userID) {
    $user = user_load($userID);
    return $user->email;
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

class SAMPLEMaestroTwitterNotification implements MaestroNotificationObserver {

  public function notify(MaestroNotification &$obj) {
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        //send a twitter update however that is done :-)
        //echo "twitter update to userID:" . $userID;
      }
    }
  }


}