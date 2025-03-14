<?php
/**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */
class PluginRoundrobinassignmentConfig extends CommonDBTM {
   static protected $notable = false;
   static $rightname = 'config';

   static function getTypeName($nb = 0) {
      return __('Round Robin Assignment', 'roundrobinassignment');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Config') {
         return self::getTypeName();
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'Config') {
         self::showConfigForm();
      }
      return true;
   }

   static function showConfigForm() {
      global $DB, $CFG_GLPI;

      $config = new self();
      
      // Get all configured groups
      $configured_groups = [];
      $iterator = $DB->request([
         'FROM' => 'glpi_plugin_roundrobinassignment_configs',
         'ORDER' => 'id'
      ]);
      
      foreach ($iterator as $data) {
         $configured_groups[$data['group_id']] = $data;
      }

      echo "<form name='form' method='post' action='../front/config.form.php'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='4'>" . __('Groups configured for Round Robin Assignment', 'roundrobinassignment') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Group') . "</th>";
      echo "<th>" . __('Active') . "</th>";
      echo "<th>" . __('Last assigned user') . "</th>";
      echo "<th>" . __('Actions') . "</th>";
      echo "</tr>";

      // List current configurations
      foreach ($configured_groups as $group_id => $data) {
         $group = new Group();
         $group->getFromDB($group_id);

         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $group->fields['name'] . "</td>";
         echo "<td>" . Dropdown::getYesNo($data['active']) . "</td>";
         
         $username = '';
         if ($data['last_user_id'] > 0) {
            $user = new User();
            if ($user->getFromDB($data['last_user_id'])) {
               $username = $user->getName();
            }
         }
         echo "<td>" . $username . "</td>";
         
         echo "<td class='center'>";
         echo "<a href='../front/config.form.php?remove=" . $data['id'] . "' class='vsubmit'>" . __('Delete') . "</a>";
         echo "</td>";
         echo "</tr>";
      }

      // Form to add new group configuration
      echo "<tr class='tab_bg_1'><td colspan='4' class='center'><h2>" . __('Add a new group for round robin', 'roundrobinassignment') . "</h2></td></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      Group::dropdown(['name' => 'group_id', 'entity' => $_SESSION['glpiactive_entity']]);
      echo "</td>";
      echo "<td>";
      Dropdown::showYesNo('active', 1);
      echo "</td>";
      echo "<td colspan='2' class='center'>";
      echo "<input type='submit' name='add' value=\"" . __('Add') . "\" class='submit'>";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      echo "</div>";
      
      Html::closeForm();
   }
}