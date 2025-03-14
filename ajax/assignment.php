<?php
/**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */
include('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$response = ['success' => false, 'message' => ''];

if (!isset($_POST['action'])) {
   $response['message'] = "No action specified";
   echo json_encode($response);
   exit;
}

switch ($_POST['action']) {
   case 'getNextUser':
      if (!isset($_POST['group_id']) || empty($_POST['group_id'])) {
         $response['message'] = "No group specified";
         break;
      }
      
      $group_id = intval($_POST['group_id']);
      $assignment = new PluginRoundrobinassignmentAssignment();
      
      if (!$assignment->isGroupConfigured($group_id)) {
         $response['message'] = "Group not configured for round robin";
         break;
      }
      
      $next_user_id = $assignment->getNextUser($group_id);
      
      if ($next_user_id) {
         $user = new User();
         $user->getFromDB($next_user_id);
         
         $response = [
            'success' => true,
            'user_id' => $next_user_id,
            'user_name' => $user->getFriendlyName()
         ];
      } else {
         $response['message'] = "No valid users found in group";
      }
      break;
      
   case 'getGroupConfig':
      if (!isset($_POST['group_id']) || empty($_POST['group_id'])) {
         $response['message'] = "No group specified";
         break;
      }
      
      $group_id = intval($_POST['group_id']);
      
      $config = getAllDataFromTable(
         'glpi_plugin_roundrobinassignment_configs',
         ['group_id' => $group_id]
      );
      
      if (!empty($config)) {
         $config_data = reset($config);
         $response = [
            'success' => true,
            'config' => $config_data
         ];
      } else {
         $response['message'] = "Group not configured";
      }
      break;
      
   default:
      $response['message'] = "Unknown action";
      break;
}

echo json_encode($response);