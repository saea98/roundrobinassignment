<?php
/**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */
include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

// Save form data
if (isset($_POST['add'])) {
   $config = new PluginRoundrobinassignmentConfig();
   
   // Check if group is already configured
   $exists = countElementsInTable(
      'glpi_plugin_roundrobinassignment_configs',
      ['group_id' => $_POST['group_id']]
   );
   
   if ($exists == 0) {
      $config->add([
         'group_id' => $_POST['group_id'],
         'active' => $_POST['active'],
         'date_mod' => date('Y-m-d H:i:s')
      ]);
      Session::addMessageAfterRedirect(__('Group added successfully', 'roundrobinassignment'), true, INFO);
   } else {
      Session::addMessageAfterRedirect(__('Group already configured', 'roundrobinassignment'), true, ERROR);
   }
   
   Html::back();
}

// Remove a configuration
if (isset($_GET['remove'])) {
   $config = new PluginRoundrobinassignmentConfig();
   $config->delete(['id' => $_GET['remove']]);
   Session::addMessageAfterRedirect(__('Group configuration removed', 'roundrobinassignment'), true, INFO);
   Html::back();
}

// Display form
Html::header(PluginRoundrobinassignmentConfig::getTypeName(), $_SERVER['PHP_SELF'], "config", "plugins");
PluginRoundrobinassignmentConfig::showConfigForm();
Html::footer();