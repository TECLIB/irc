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

/** @file
* @brief
*/

include ('../../../inc/includes.php');

Session::checkRight("config", UPDATE);
$notificationirc = new PluginIrcNotificationIrcSetting();

if (!empty($_POST["test_irc_send"])) {
   try {
      PluginIrcNotificationIrc::testNotification();
   } catch (\RuntimeException $e) {
      Session::addMessageAfterRedirect(
         __('Unable to send test notification.', 'irc') . ' ' .
         __('Error was:', 'irc') . $e->getMessage(),
         true,
         ERROR
      );
   }
   Html::back();
} else if (!empty($_POST["update"])) {
   $config = new Config();
   $config->update($_POST);
   Html::back();
}

Html::header(Notification::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "config", "notification", "config");

$notificationirc->display(array('id' => 1));

Html::footer();
