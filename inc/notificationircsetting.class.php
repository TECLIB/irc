<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2017 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 *  This class manages the irc notifications settings
 */
class PluginIrcNotificationIrcSetting extends NotificationSetting {


   static function getTypeName($nb=0) {
      return __('IRC followups configuration', 'irc');
   }


   public function getEnableLabel() {
      return __('Enable followups via IRC', 'irc');
   }


   static public function getMode() {
      return NotificationTemplateTemplate::MODE_IRC;
   }


   function showFormConfig($options = []) {
      global $CFG_GLPI;

      $params = [
         'display'   => true
      ];
      $params = array_merge($params, $options);

      $out = "<form action='".Toolbox::getItemTypeFormURL(__CLASS__)."' method='post'>";
      $out .= "<div>";
      $out .= "<input type='hidden' name='id' value='1'>";
      $out .= "<table class='tab_cadre_fixe'>";
      $out .= "<tr class='tab_bg_1'><th colspan='4'>"._n('IRC notification', 'IRC notifications', Session::getPluralNumber(), 'irc')."</th></tr>";

      if ($CFG_GLPI['notifications_irc']) {
         $out .= "<tr class='tab_bg_2'>";
         $out .= "<td>" . __('IRC server') . "</td>";
         $out .= "<td><input type='text' name='pluginirc_config_server' size='40' value='".
                    $CFG_GLPI["pluginirc_config_server"]."' required='required'>";
         $out .= "</td>";
         $out .= "<td >" . __('Port') . "</td>";
         $out .= "<td><input type='text' name='pluginirc_config_port' size='40' value='" .
                    $CFG_GLPI["pluginirc_config_port"] . "' required='required'>";
         $out .= " </td></tr>";

         $out .= "<tr class='tab_bg_2'>";
         $out .= "<td>" . __('Nickname') . "</td>";
         $out .= "<td><input type='text' name='pluginirc_config_nick' size='40' value='".
                    $CFG_GLPI["pluginirc_config_nick"]."' required='required'>";
         $out .= "</td>";
         //TODO: do not show password
         $out .= "<td >" . __('Password') . "</td>";
         $out .= "<td><input type='password' name='pluginirc_config_pass' size='40' value='" .
                    $CFG_GLPI["pluginirc_config_pass"] . "'>";
         $out .= " </td></tr>";

         $out .= "<tr class='tab_bg_2'>";
         $out .= "<td >" . __('Channels') . "</td>";
         $out .= "<td><input type='text' name='pluginirc_config_channels' size='40' value='" .
                    $CFG_GLPI["pluginirc_config_channels"] . "'>";
         $out .= " </td>";
         $out .= "<td >" . __('Nick to') . "</td>";
         $out .= "<td><input type='text' name='pluginirc_config_nicksto' size='40' value='" .
                    $CFG_GLPI["pluginirc_config_nicksto"] . "'>";
         $out .= " </td></tr>";
      } else {
         $out .= "<tr><td colspan='4'>" . __('Notifications are disabled.')  . " <a href='{$CFG_GLPI['root_doc']}/front/setup.notification.php'>" . _('See configuration') .  "</td></tr>";
      }
      $options['candel']     = false;
      if ($CFG_GLPI['notifications_irc']) {
         $options['addbuttons'] = array('test_irc_send' => __('Send a test over IRC', 'irc'));
      }

      if (defined('PLUGIN_IRC_UNIT_TESTS')) {
         return $out;
      }
      //Ignore display parameter since showFormButtons is now ready :/ (from all but tests)
      echo $out;

      $this->showFormButtons($options);

      /*if ($params['display']) {
         echo $out;
      } else {
         eturn $out
      }*/
   }

}
