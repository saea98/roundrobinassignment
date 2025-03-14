<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Rights definition for Round Robin Assignment plugin
 */
class PluginRoundrobinassignmentRight {
   /**
    * Get plugin rights
    * @return array
    */
   static function getRights() {
      return [
         'plugin_roundrobinassignment_config' => 'Round Robin Assignment Configuration'
      ];
   }
}