<?php
/**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Notification target for Round Robin Assignment plugin
 */
class PluginRoundrobinassignmentNotificationTarget extends NotificationTarget {

   function getEvents() {
      return [
         'assign_ticket' => __('Automatic round robin assignment', 'roundrobinassignment')
      ];
   }

   /**
    * Add recipients for the notification
    */
   function addNotificationTargets($event) {
      $this->addTarget(Notification::AUTHOR, __('Requester'));
      $this->addTarget(Notification::ASSIGN_TECH, __('Assigned technician'));
      $this->addTarget(Notification::SUPERVISOR_ASSIGN_GROUP, __('Group supervisor'));
      $this->addTarget(Notification::SUPERVISOR_REQUESTER_GROUP, __('Requester group supervisor'));
      $this->addTarget(Notification::ITEM_TECH_IN_CHARGE, __('Technician in charge of the hardware'));
      $this->addTarget(Notification::ITEM_TECH_GROUP_IN_CHARGE, __('Group in charge of the hardware'));
      $this->addTarget(Notification::ASSIGN_GROUP, __('Group in charge of the ticket'));
      $this->addTarget(Notification::REQUESTER_GROUP, __('Requester group'));
   }

   /**
    * Add specific data for notification
    */
   function addDataForTemplate($event, $options = []) {
      
      // Add ticket data
      $ticket = new Ticket();
      $ticket->getFromDB($this->obj->fields['id']);
      
      $this->data['##ticket.id##'] = $ticket->fields['id'];
      $this->data['##ticket.title##'] = $ticket->fields['name'];
      $this->data['##ticket.content##'] = $ticket->fields['content'];
      
      // Add assigned user data
      $user = new User();
      if ($user->getFromDB($ticket->fields['users_id_tech'])) {
         $this->data['##ticket.assigneduser##'] = $user->getName();
         $this->data['##ticket.assignedemail##'] = $user->getDefaultEmail();
      } else {
         $this->data['##ticket.assigneduser##'] = '';
         $this->data['##ticket.assignedemail##'] = '';
      }
      
      // Add assigned group data
      $group = new Group();
      if ($group->getFromDB($ticket->fields['groups_id_tech'])) {
         $this->data['##ticket.assignedgroup##'] = $group->getName();
      } else {
         $this->data['##ticket.assignedgroup##'] = '';
      }
      
      // Add URL
      $this->data['##ticket.url##'] = $this->formatURL($options['additionnaloption']['usertype'],
                                                       "Ticket_".$ticket->fields['id']);
   }
}