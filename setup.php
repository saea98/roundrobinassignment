<?php
/**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */
define('ROUNDROBINASSIGNMENT_VERSION', '1.0.0');

/**
 * Init the hooks of the plugin
 * @return void
 */
function plugin_init_roundrobinassignment() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['roundrobinassignment'] = true;
   
   // Add a tab to ticket form
   Plugin::registerClass('PluginRoundrobinassignmentConfig', [
      'addtabon' => ['Preference', 'Config']
   ]);
   
   // Add plugin to menu
   $PLUGIN_HOOKS['menu_toadd']['roundrobinassignment'] = [
      'config' => 'PluginRoundrobinassignmentConfig'
   ];
   
   // Hook to ticket actions
   $PLUGIN_HOOKS['item_add']['roundrobinassignment'] = [
      'Ticket' => 'plugin_roundrobinassignment_item_add'
   ];
   
   $PLUGIN_HOOKS['item_update']['roundrobinassignment'] = [
      'Ticket' => 'plugin_roundrobinassignment_item_update'
   ];
}

/**
 * Get the name and the version of the plugin
 * @return array
 */
function plugin_version_roundrobinassignment() {
   return [
      'name'           => 'Round Robin Assignment',
      'version'        => ROUNDROBINASSIGNMENT_VERSION,
      'author'         => 'Salvador Jiménez Sánchez',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/saea98/roundrobinassignment',
      'requirements'   => [
         'glpi' => [
            'min' => '9.5',
            'max' => '10.0.18',
         ]
      ]
   ];
}

/**
 * Check prerequisites before install
 * @return boolean
 */
function plugin_roundrobinassignment_check_prerequisites() {
   // Check GLPI version
   if (version_compare(GLPI_VERSION, '9.5', 'lt')) {
      echo "Versión requerida GLPI >= 9.5";
      return false;
   }
   return true;
}

/**
 * Check configuration
 * @return boolean
 */
function plugin_roundrobinassignment_check_config() {
   return true;
}