<?php
/**
 * STI
 * Brenda Fierro Cervantes
 * GATGAD
 * Salvador Jiménez 
 */
include('../../../inc/includes.php');

Session::checkRight("config", READ);

// Redirección a la página de formulario de configuración
Html::header(PluginRoundrobinassignmentConfig::getTypeName(), $_SERVER['PHP_SELF'], "config", "plugins");
Html::redirect($CFG_GLPI["root_doc"] . "/plugins/roundrobinassignment/front/config.form.php");
Html::footer();