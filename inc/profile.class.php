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
 * Manage the profile rights for the plugin
 */
class PluginRoundrobinassignmentProfile extends Profile {
   static $rightname = "profile";

   /**
    * Add rights to the profile
    *
    * @param $profiles_id the profile ID
    *
    * @return Nothing
    **/
   function addRightsToProfile($profiles_id) {
      $profileRight = new ProfileRight();
      
      $rights = [
         'plugin_roundrobinassignment_config' => ALLSTANDARDRIGHT, // Give full rights on config
      ];
      
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   ['profiles_id' => $profiles_id,
                                    'name' => $right])) {
            $profileRight->add([
               'profiles_id' => $profiles_id,
               'name' => $right,
               'rights' => $value
            ]);
         }
      }
   }
   
   /**
    * @param $profile
   **/
   static function uninstallProfile($profile) {
      $profileRight = new ProfileRight();
      foreach ([
         'plugin_roundrobinassignment_config'
      ] as $right) {
         $profileRight->deleteByCriteria(['profiles_id' => $profile->fields['id'],
                                          'name' => $right]);
      }
   }
   
   /**
    * Show profile form
    *
    * @param $items_id integer id of the profile
    * @param $target value url of target
    *
    * @return nothing
    **/
   function showForm($profiles_id=0, $openform=true, $closeform=true) {
      global $CFG_GLPI;
      
      $profile = new Profile();
      $profile->getFromDB($profiles_id);
      
      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }
      
      $rights = [
         ['rights'    => Profile::getRightsFor('PluginRoundrobinassignmentConfig'),
          'label'     => PluginRoundrobinassignmentConfig::getTypeName(1),
          'field'     => 'plugin_roundrobinassignment_config']
      ];
      
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                    'default_class' => 'tab_bg_2',
                                                    'title'         => __('General')]);
      
      if ($canedit && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $profiles_id]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }
}