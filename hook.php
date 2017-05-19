<?php
/*
 -------------------------------------------------------------------------
 irc plugin for GLPI
 Copyright (C) 2017 by the irc Development Team.

 https://github.com/pluginsGLPI/irc
 -------------------------------------------------------------------------

 LICENSE

 This file is part of irc.

 irc is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 irc is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with irc. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_irc_install() {
   Config::setConfigurationValues('core', ['notifications_irc']);
   Config::setConfigurationValues(
      'plugin:irc', [
         'server',
         'port',
         'nick',
         'password',
         'channels',
         'nicksto'
      ]
   );

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_irc_uninstall() {
   return true;
}
