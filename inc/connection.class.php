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

class PluginIrcConnection {
   private $connection;

   /**
    * Connects to IRc server and set nickname
    *
    * @return resource
    */
   public function connectIrc() {
      global $CFG_GLPI;

      $this->connection = fsockopen(
         $CFG_GLPI['pluginirc_config_server'],
         $CFG_GLPI['pluginirc_config_port']
      );

      $this->sendCommand("USER {$CFG_GLPI['pluginirc_config_nick']} USING PHP IRC"); //sends the user must have 4 paramters
      $this->sendCommand("NICK {$CFG_GLPI['pluginirc_config_nick']}"); //sends the nickname

      $process = true;
      while ($process) {
         $read = fgets($this->connection, 1024);
         Toolbox::logDebug("[RECIVE] $read");
         $ret = explode(':', $read);
         if (rtrim($ret[0]) == "PING") {
            $this->sendCommand("PONG :".$ret(0));
            $process  = false;
         }

         if (preg_match('#:(.+):End Of /MOTD Command.#i', $read)) {
            $process = false;
         }
      }
      return $this->connection;
   }

   /**
    * Send a message over the IRC server and print in log
    *
    * @param string $cmd Command to send
    *
    * @return void
    */
   public function sendCommand ($cmd) {
      fputs($this->connection, $cmd . "\r\n");
      Toolbox::logDebug("[SEND] $cmd \n");
   }
}
