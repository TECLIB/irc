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

class PluginIrcNotificationEventIrc extends NotificationEventAbstract implements NotificationEventInterface {
   static $connection;

   static public function getTargetFieldName() {
      return 'users_id';
   }

   static public function getTargetField(&$data) {
      $field = self::getTargetFieldName();

      if (!isset($data[$field])) {
         //Missing field; set to null
         $data[$field] = null;
      }

      return $field;
   }


   static public function canCron() {
      return true;
   }


   static public function getAdminData() {
      //no conf available for global admin right now
      return false;
   }


   static public function getEntityAdminsData($entity) {
      //no conf available for entity admins right now
      return false;
   }


   static public function send(array $data) {
      $conf = Config::getConfigurationValues('plugin:irc');
      $connection = self::getConnection();
      $connection->connectIrc();

      $channels = [];
      if (!empty($conf['channels'])) {
         $channels = explode(',', $conf['channels']);
      }

      $nicks = [];
      if (!empty($conf['nicksto'])) {
         $nicks = explode(',', $conf['nicksto']);
      }

      foreach ($channels as &$channel) {
         if (substr($channel, 0, 1) !== '#') {
            $channel = '#' . $channel;
         }
         $connection->sendCommand("JOIN :$channel");
      }

      $sent = [];
      foreach ($data as $row) {
         $current = new QueuedNotification();
         $current->getFromResultSet($row);

         $msg = str_replace(["\r", "\n", "\t"], ' ', $current->fields['body_text']);
         //prevent dups
         if (in_array($msg, $sent)) {
            continue;
         }

         //take care of too long lines
         $lines = str_split($msg, 500);
         //prevent excess flood
         if (count($lines) > 3) {
            Toolbox::logDebug('IRC notification is above the limit of 3 x 500 chars!');
            $lines = array_slice(0, 3);
            $lines[2] .= ' (' . __('truncated', 'irc') . ')';
         }

         //send to configured channels
         foreach ($channels as $chan) {
            foreach ($lines as $line) {
               $connection->sendCommand("PRIVMSG $chan :$line");
            }
         }

         //send to configured nicks
         foreach ($nicks as $nick) {
            foreach ($lines as $line) {
               $connection->sendCommand("NOTICE $nick :$line");
            }
         }
         $sent[] = $msg;
         $current->update([
            'id'        => $current->getID(),
            'sent_time' => $_SESSION['glpi_currenttime']
         ]);
         $current->delete(['id' => $current->getID()]);
      }
      $connection->sendCommand("QUIT");
      return count($data);
   }


   /**
    * Get connection instance
    *
    * @return PluginIrcConnection
    */
   protected static function getConnection() {
      if (static::$connection === null) {
         static::$connection = new PluginIrcConnection();
      }
      return static::$connection;
   }

   /**
    * Set connection instance
    *
    * @param PluginIrcConnection $connection Instance
    *
    * @return void
    */
   static public function setConnection($connection) {
      static::$connection = $connection;
   }
}
