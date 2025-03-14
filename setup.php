<?php

define('ROUNDROBINASSIGNMENT_VERSION', '1.0.0');

/**
 * Init the hooks of the plugin
 * STI
 * Salvador Jiménez Sánchez
 * @return void
 */
function plugin_init_roundrobinassignment() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['roundrobinassignment'] = true;
   
   // Registro de clases
   Plugin::registerClass('PluginRoundrobinassignmentConfig', [
      'addtabon' => ['Preference', 'Config']
   ]);
   
   Plugin::registerClass('PluginRoundrobinassignmentProfile', [
      'addtabon' => ['Profile']  // Agregar pestaña en perfiles
   ]);
   
   // Si el usuario tiene permisos, muestra en el menú
   if (Session::haveRight('plugin_roundrobinassignment_config', READ)) {
      // Agregar al menú de configuración
      $PLUGIN_HOOKS['menu_toadd']['roundrobinassignment'] = [
         'config' => 'PluginRoundrobinassignmentConfig'
      ];
   }
   
   // Hook para tickets
   $PLUGIN_HOOKS['item_add']['roundrobinassignment'] = [
      'Ticket' => 'plugin_roundrobinassignment_item_add'
   ];
   
   $PLUGIN_HOOKS['item_update']['roundrobinassignment'] = [
      'Ticket' => 'plugin_roundrobinassignment_item_update'
   ];
   
   // Hook para perfiles (importante para agregar derechos en la instalación)
   $PLUGIN_HOOKS['change_profile']['roundrobinassignment'] = ['PluginRoundrobinassignmentProfile', 'changeProfile'];
}

/**
 * Get the name and the version of the plugin
 * @return array
 */
function plugin_version_roundrobinassignment() {
   return [
      'name'           => 'Round Robin Assignment',
      'version'        => ROUNDROBINASSIGNMENT_VERSION,
      'author'         => 'Tu Nombre',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/tuusuario/roundrobinassignment',
      'requirements'   => [
         'glpi' => [
            'min' => '9.5',
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
      echo "This plugin requires GLPI >= 9.5";
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