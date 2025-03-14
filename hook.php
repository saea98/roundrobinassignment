<?php
/**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */
/**
 * Install hook
 * @return boolean
 */
function plugin_roundrobinassignment_install() {
    global $DB;
 
    if (!$DB->tableExists("glpi_plugin_roundrobinassignment_configs")) {
       $query = "CREATE TABLE `glpi_plugin_roundrobinassignment_configs` (
                   `id` int(11) NOT NULL AUTO_INCREMENT,
                   `group_id` int(11) NOT NULL,
                   `active` tinyint(1) NOT NULL DEFAULT '1',
                   `last_user_id` int(11) DEFAULT NULL,
                   `date_mod` timestamp NULL DEFAULT NULL,
                   PRIMARY KEY (`id`),
                   KEY `group_id` (`group_id`)
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
       $DB->query($query) or die("Error creating table glpi_plugin_roundrobinassignment_configs " . $DB->error());
    }
 
    // Agregar los permisos predeterminados
    PluginRoundrobinassignmentProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
    
    // Agregar configuración a todos los perfiles existentes
    PluginRoundrobinassignmentProfile::initProfile();
 
    return true;
 }

/**
 * Uninstall hook
 * @return boolean
 */
function plugin_roundrobinassignment_uninstall() {
    global $DB;

    $tables = [
       'glpi_plugin_roundrobinassignment_configs'
    ];
 
    foreach ($tables as $table) {
       $DB->query("DROP TABLE IF EXISTS `$table`") or die($DB->error());
    }
    
    // Eliminar derechos de perfiles
    $profileRight = new ProfileRight();
    foreach (PluginRoundrobinassignmentProfile::getAllRights() as $right) {
       $profileRight->deleteByCriteria(['name' => $right['field']]);
    }
 
    return true;
 }

/**
 * Hook called when a ticket is added
 * @param Ticket $item
 * @return void
 */
function plugin_roundrobinassignment_item_add(Ticket $item) {
   _process_ticket_assignment($item);
}

/**
 * Hook called when a ticket is updated
 * @param Ticket $item
 * @return void
 */
function plugin_roundrobinassignment_item_update(Ticket $item) {
   _process_ticket_assignment($item);
}

/**
 * Process the round robin assignment for a ticket
 * @param Ticket $item
 * @return void
 */
function _process_ticket_assignment(Ticket $item) {
   // Verificamos que sea un ticket 
   if (!($item instanceof Ticket)) {
      return;
   }
   
   // Obtenemos los datos actualizados del ticket completo
   $ticket = new Ticket();
   if (!$ticket->getFromDB($item->fields['id'])) {
      return; // No se pudo cargar el ticket
   }
   
   // Verificamos si hay un grupo técnico asignado y no hay un técnico asignado
   $group_id_tech = 0;
   
   // Verificamos si el campo groups_id_tech existe
   if (isset($ticket->fields['groups_id_tech'])) {
      $group_id_tech = $ticket->fields['groups_id_tech'];
   } else {
      // Si no existe, necesitamos buscar en la tabla de grupos asignados al ticket
      $group_ticket = new Group_Ticket();
      $assigned_groups = $group_ticket->find([
         'tickets_id' => $ticket->fields['id'],
         'type' => CommonITILActor::ASSIGN  // 2 es el tipo para asignación
      ]);
      
      if (!empty($assigned_groups)) {
         // Tomamos el primer grupo asignado como referencia
         $group_data = reset($assigned_groups);
         $group_id_tech = $group_data['groups_id'];
      }
   }
   
   // Verificamos si hay un usuario técnico asignado
   $user_id_tech = 0;
   if (isset($ticket->fields['users_id_tech'])) {
      $user_id_tech = $ticket->fields['users_id_tech'];
   } else {
      // Si no existe, necesitamos buscar en la tabla de usuarios asignados al ticket
      $ticket_user = new Ticket_User();
      $assigned_users = $ticket_user->find([
         'tickets_id' => $ticket->fields['id'],
         'type' => CommonITILActor::ASSIGN  // 2 es el tipo para asignación
      ]);
      
      if (!empty($assigned_users)) {
         // Ya hay un usuario asignado, no hacemos nada
         $user_id_tech = 1; // Usamos 1 como flag para indicar que hay un usuario asignado
      }
   }
   
   // Si hay un grupo asignado y no hay un técnico asignado, aplicamos el round robin
   if ($group_id_tech > 0 && $user_id_tech == 0) {
      // Check if this group is configured for round robin
      $assignment = new PluginRoundrobinassignmentAssignment();
      if ($assignment->isGroupConfigured($group_id_tech)) {
         // Get next user and assign ticket
         $next_user_id = $assignment->getNextUser($group_id_tech);
         if ($next_user_id > 0) {
            // Asignamos el ticket usando el método apropiado según la versión de GLPI
            $ticket_user = new Ticket_User();
            $ticket_user->add([
               'tickets_id' => $ticket->fields['id'],
               'users_id'   => $next_user_id,
               'type'       => CommonITILActor::ASSIGN, // Tipo asignado
               '_disablenotif' => true  // No enviar otra notificación
            ]);
            
            // Log the assignment
            Toolbox::logInfo("Ticket #{$item->fields['id']} assigned to user $next_user_id by Round Robin plugin");
         }
      }
   }
}