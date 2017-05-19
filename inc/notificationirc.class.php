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
 *  NotificationIrc
**/
class PluginIrcNotificationIrc implements NotificationInterface {

   /**
    * Check data
    *
    * @param mixed $value   The data to check (may differ for every notification mode)
    * @param array $options Optionnal special options (may be needed)
    *
    * @return boolean
   **/
   static function check($value, $options = []) {
      //waiting for a user ID
      return (is_int($value) && $value > 0);
   }

   static function testNotification() {
      $instance = new self();
      $instance->sendNotification([
         '_itemtype'                   => 'PluginIrcNotificationIrc',
         '_items_id'                   => 1,
         '_notificationtemplates_id'   => 0,
         '_entities_id'                => 0,
         'fromname'                    => 'TEST',
         'subject'                     => 'Test notification',
         'content_text'                => "Hello, this is a test notification.",
         'to'                          => Session::getLoginUserID(),
         '_send_now'                   => true
      ]);
   }


   function sendNotification($options=array()) {

      $data = array();
      $data['itemtype']                             = $options['_itemtype'];
      $data['items_id']                             = $options['_items_id'];
      $data['notificationtemplates_id']             = $options['_notificationtemplates_id'];
      $data['entities_id']                          = $options['_entities_id'];

      $data['sendername']                           = $options['fromname'];

      $data['name']                                 = $options['subject'];
      $data['body_text']                            = $options['content_text'];
      $data['recipient']                            = $options['to'];

      $data['mode'] = NotificationTemplateTemplate::MODE_IRC;

      $mailqueue = new QueuedMail();

      if (!$mailqueue->add(Toolbox::addslashes_deep($data))) {
         Session::addMessageAfterRedirect(__('Error inserting IRC notification to queue'), true, ERROR);
         return false;
      } else {
         //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
         Toolbox::logInFile("notification",
                            sprintf(__('%1$s: %2$s'),
                                    sprintf(__('An IRC notification to %s was added to queue', 'irc'),
                                            $options['to']),
                                    $options['subject']."\n"));
      }

      if (isset($options['_send_now'])) {
         $mailqueue::forceSendFor(
            $options['_itemtype'],
            $options['_items_id']
         );
      }

      return true;
   }
}
