<?php

class PluginRoundrobinassignmentAssignment {
    /**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */
   
   /**
    * Check if a group is configured for round robin assignment
    * @param int $group_id
    * @return boolean
    */
   public function isGroupConfigured($group_id) {
      global $DB;
      
      $iterator = $DB->request([
         'FROM' => 'glpi_plugin_roundrobinassignment_configs',
         'WHERE' => [
            'group_id' => $group_id,
            'active' => 1
         ]
      ]);
      
      return count($iterator) > 0;
   }
   
   /**
    * Get the next user to assign from the group
    * @param int $group_id
    * @return int|null
    */
   public function getNextUser($group_id) {
      global $DB;
      
      // Get the group's member users who can be assigned to a ticket
      $user_group = new Group_User();
      $users = $user_group->find([
         'groups_id' => $group_id
      ]);
      
      if (empty($users)) {
         return null;
      }
      
      // Filter users with proper rights and construct an array of user IDs
      $validUserIds = [];
      foreach ($users as $data) {
         $user_id = $data['users_id'];
         $user = new User();
         if ($user->getFromDB($user_id) && $user->fields['is_active']) {
            // Check if user has tech profile rights
            // This is a simplified check - you might need more complex logic based on your GLPI setup
            $validUserIds[] = $user_id;
         }
      }
      
      if (empty($validUserIds)) {
         return null;
      }
      
      // Get current configuration
      $config = $DB->request([
         'FROM' => 'glpi_plugin_roundrobinassignment_configs',
         'WHERE' => ['group_id' => $group_id]
      ]);
      
      if (count($config) == 0) {
         return null;
      }
      
      $configData = $config->current();
      $lastUserId = $configData['last_user_id'];
      
      // Find the next user in the round robin
      $nextUserId = null;
      if (empty($lastUserId) || !in_array($lastUserId, $validUserIds)) {
         // If no last user or last user no longer in group, start with first user
         $nextUserId = reset($validUserIds);
      } else {
         // Find the current user's position
         $currentPos = array_search($lastUserId, $validUserIds);
         
         // Get next user
         if ($currentPos !== false) {
            $nextPos = ($currentPos + 1) % count($validUserIds);
            $nextUserId = $validUserIds[$nextPos];
         } else {
            $nextUserId = reset($validUserIds);
         }
      }
      
      // Update the configuration with the new last user
      $DB->update(
         'glpi_plugin_roundrobinassignment_configs',
         [
            'last_user_id' => $nextUserId,
            'date_mod' => date('Y-m-d H:i:s')
         ],
         ['id' => $configData['id']]
      );
      
      return $nextUserId;
   }
}