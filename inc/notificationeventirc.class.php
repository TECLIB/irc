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

class PluginIrcNotificationEventIrc implements NotificationEventInterface {
   static $connection;

   /**
    * Raise a IRC notification event
    *
    * @param string               $event              Event
    * @param CommonDBTM           $item               Notification data
    * @param array                $options            Options
    * @param string               $label              Label
    * @param array                $data               Notification data
    * @param NotificationTarget   $notificationtarget Target
    * @param NotificationTemplate $template           Template
    * @param boolean              $notify_me          Whether to notify current user
    *
    * @return void
    */
   static public function raise(
      $event,
      CommonDBTM $item,
      array $options,
      $label,
      array $data,
      NotificationTarget $notificationtarget,
      NotificationTemplate $template,
      $notify_me
   ) {
      global $CFG_GLPI;
      if ($CFG_GLPI['notifications_irc']) {
         $entity = $notificationtarget->getEntity();
         $processed    = array();
         $notprocessed = array();

         $targets = getAllDatasFromTable(
            'glpi_notificationtargets',
            "notifications_id = {$data['id']}"
         );

         //Foreach notification targets
         foreach ($targets as $target) {
            //Get all users affected by this notification
            $notificationtarget->addForTarget($target, $options);

            foreach ($notificationtarget->getTargets() as $phone => $users_infos) {
               if ($label
                     || $notificationtarget->validateSendTo($event, $users_infos, $notify_me)) {
                  //If the user have not yet been notified
                  if (!isset($processed[$users_infos['language']][$phone])) {
                     //If ther user's language is the same as the template's one
                     if (isset($notprocessed[$users_infos['language']]
                                                   [$phone])) {
                        unset($notprocessed[$users_infos['language']]
                                                   [$phone]);
                     }
                     $options['item'] = $item;
                     if ($tid = $template->getTemplateByLanguage($notificationtarget,
                                                                  $users_infos, $event,
                                                                  $options)) {
                        //Send notification to the user
                        if ($label == '') {
                           $send_data = $template->getDataToSend(
                              $notificationtarget,
                              $tid,
                              $phone,
                              $users_infos,
                              $options
                           );
                           $send_data['_notificationtemplates_id'] = $data['notificationtemplates_id'];
                           $send_data['_itemtype']                 = $item->getType();
                           $send_data['_items_id']                 = $item->getID();
                           $send_data['_entities_id']              = $entity;
                           $send_data['mode']                      = $data['mode'];

                           Notification::send($send_data);
                        } else {
                           $notificationtarget->getFromDB($target['id']);
                           echo "<tr class='tab_bg_2'><td>".$label."</td>";
                           echo "<td>".$notificationtarget->getNameID()."</td>";
                           echo "<td>".sprintf(__('%1$s (%2$s)'), $template->getName(),
                                                $users_infos['language'])."</td>";
                           echo "<td>".$options['mode']."</td>";
                           echo "<td>".$$phone."</td>";
                           echo "</tr>";
                        }
                        $processed[$users_infos['language']][$phone]
                                                                  = $users_infos;

                     } else {
                        $notprocessed[$users_infos['language']][$phone]
                                                                     = $users_infos;
                     }
                  }
               }
            }
         }

         unset($processed);
         unset($notprocessed);
      }
   }


   static public function getTargetField(&$data) {
      $field = 'users_id';

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
         $current = new QueuedMail();
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
