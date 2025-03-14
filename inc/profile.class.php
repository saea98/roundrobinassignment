<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Manage the profile rights for the plugin
 */
class PluginRoundrobinassignmentProfile extends Profile {
   static $rightname = "profile";
   
   /**
    * Define all rights for the plugin
    * @return array
    */
   static function getAllRights() {
      return [
         [
            'itemtype' => 'PluginRoundrobinassignmentConfig',
            'label'    => __('Round Robin Assignment Configuration', 'roundrobinassignment'),
            'field'    => 'plugin_roundrobinassignment_config',
            'rights'   => [
               READ    => __('Read'),
               UPDATE  => __('Update'),
            ]
         ]
      ];
   }
   
   /**
    * Initialize profile during installation
    * @return void
    */
   static function initProfile() {
      global $DB;
      
      // Add rights for all existing profiles
      $profiles = $DB->request("SELECT *
                               FROM `glpi_profiles`
                               WHERE `interface` = 'central'");
                               
      foreach ($profiles as $profile) {
         self::createAccess($profile['id']);
      }
   }
   
   /**
    * Create profile access during first installation
    * @param int $profile_id
    */
   static function createFirstAccess($profile_id) {
      $rights = [
         'plugin_roundrobinassignment_config' => ALLSTANDARDRIGHT
      ];
      
      self::updateProfileRights($profile_id, $rights);
   }
   
   /**
    * Create specific access for a profile
    * @param int $profile_id
    */
   static function createAccess($profile_id) {
      self::updateProfileRights($profile_id, [
         'plugin_roundrobinassignment_config' => READ
      ]);
   }
   
   /**
    * Update profile rights
    * @param int $profiles_id
    * @param array $rights
    */
   static function updateProfileRights($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                 ['profiles_id' => $profiles_id,
                                  'name' => $right])) {
            $profileRight->add([
               'profiles_id' => $profiles_id,
               'name' => $right,
               'rights' => $value
            ]);
         } else {
            $profileRight->updateProfileRights($profiles_id, [$right => $value]);
         }
      }
   }
   
   /**
    * Method called when changing profile
    */
   static function changeProfile() {
      // Reload rights if user changes profile
      if (isset($_SESSION['glpiactiveprofile'])) {
         $profile = new self();
         $profile->getFromDB($_SESSION['glpiactiveprofile']['id']);
         $_SESSION['glpi_plugin_roundrobinassignment_profile'] = $profile->fields;
      }
   }

   /**
    * Show the profile form
    * @param int $profiles_id
    * @param bool $openform
    * @param bool $closeform
    * @return void
    */
   function showForm($profiles_id=0, $openform=true, $closeform=true) {
      global $CFG_GLPI;
      
      if (!self::canView()) {
         return false;
      }
      
      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      
      if ($openform) {
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }
      
      $rights = self::getAllRights();
      $profile->displayRightsChoiceMatrix($rights, [
         'canedit'       => Profile::canUpdate(),
         'default_class' => 'tab_bg_2',
         'title'         => __('Round Robin Assignment', 'roundrobinassignment')
      ]);
      
      if ($closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
   }
   
   /**
    * Installs the profile tab during plugin installation
    */
   static function installProfile() {
      global $DB;
      
      // Check if profile table already exists
      $query = "SELECT * FROM `glpi_profilerights` 
               WHERE `name` = 'plugin_roundrobinassignment_config' LIMIT 1";
      $result = $DB->query($query);
      
      if ($DB->numrows($result) == 0) {
         // Create profile rights for all existing profiles
         $query = "SELECT * FROM `glpi_profiles`";
         $result = $DB->query($query);
         
         if ($DB->numrows($result) > 0) {
            while ($prof = $DB->fetchAssoc($result)) {
               $query = "INSERT INTO `glpi_profilerights` 
                        (`profiles_id`, `name`, `rights`) VALUES 
                        ('".$prof['id']."', 'plugin_roundrobinassignment_config', '".READ."')";
               $DB->query($query);
            }
         }
      }
   }
   
   /**
    * Remove profile rights for the plugin
    */
   static function uninstallProfile() {
      global $DB;
      
      $query = "DELETE FROM `glpi_profilerights` 
               WHERE `name` LIKE 'plugin_roundrobinassignment_%'";
      $DB->query($query);
   }
   
   /**
    * Add the tab to the profile page
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      if ($item->getType() == 'Profile' && $item->getField('interface') == 'central') {
         return __('Round Robin Assignment', 'roundrobinassignment');
      }
      return '';
   }
   
   /**
    * Display the profile tab content
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'Profile') {
         $profile = new self();
         $profile->showForm($item->getID());
      }
      return true;
   }
}