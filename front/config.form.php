<?php

include('../../../inc/includes.php');

// Verificar permisos
Session::checkRight("plugin_roundrobinassignment_config", READ);

// Para guardar configuración se necesita permiso de actualización
if (isset($_POST['add']) && Session::haveRight("plugin_roundrobinassignment_config", UPDATE)) {
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

// Remove a configuration - requires UPDATE permission
if (isset($_GET['remove']) && Session::haveRight("plugin_roundrobinassignment_config", UPDATE)) {
   $config = new PluginRoundrobinassignmentConfig();
   $config->delete(['id' => $_GET['remove']]);
   Session::addMessageAfterRedirect(__('Group configuration removed', 'roundrobinassignment'), true, INFO);
   Html::back();
}

// Display form
Html::header(PluginRoundrobinassignmentConfig::getTypeName(), $_SERVER['PHP_SELF'], "config", "plugins");
PluginRoundrobinassignmentConfig::showConfigForm();
Html::footer();