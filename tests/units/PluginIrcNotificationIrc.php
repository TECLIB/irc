<?php

namespace tests\units;

use \atoum;

class PluginIrcNotificationIrc extends atoum {

   public function testPluginSetup() {
      global $CFG_GLPI;

      $plugin = new \Plugin();
      $plugin->getFromDBbyDir('irc');

      //check if plugin is up & running
      $this->boolean($plugin->isInstalled('irc'))->isTrue();
      $this->boolean($plugin->isActivated('irc'))->isTrue();

      //check if mode has been registered successfully
      $this->array($CFG_GLPI)->hasKey('notifications_irc');
      $expected = [
         'label'  => 'IRC',
         'from'   => 'irc'
      ];
      $this->array($CFG_GLPI['notifications_modes'])->hasKey('irc');
      $this->array($CFG_GLPI['notifications_modes']['irc'])->isIdenticalTo($expected);
   }
}
