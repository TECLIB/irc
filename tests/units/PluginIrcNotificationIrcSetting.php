<?php

namespace tests\units;

use \atoum;

class PluginIrcNotificationIrcSetting extends atoum {

   public function testGetTypeName() {
      $this->string(\PluginIrcNotificationIrcSetting::getTypeName())
         ->isIdenticalTo('IRC followups configuration');
      $this->string(\PluginIrcNotificationIrcSetting::getTypeName(10))
         ->isIdenticalTo('IRC followups configuration');
   }

   public function testGetEnableLabel() {
      $this->string(\PluginIrcNotificationIrcSetting::getEnableLabel())
         ->isIdenticalTo('Enable followups via IRC');
   }

   public function testGetMode() {
      $this->string(\PluginIrcNotificationIrcSetting::getMode())
         ->isIdenticalTo(\Notification_NotificationTemplate::MODE_IRC);
   }

   public function testShowFormConfig() {
      global $CFG_GLPI;

      $instance = new \PluginIrcNotificationIrcSetting();

      $this->variable($CFG_GLPI['notifications_irc'])->isEqualTo(0);
      $out = $instance->showFormConfig();

      $match = strpos($out, 'Notifications are disabled.');
      $this->integer($match)->isGreaterThanOrEqualTo(0);

      $CFG_GLPI['notifications_irc'] = 1;
      $out = $instance->showFormConfig();
      $match = strpos($out, 'Notifications are disabled.');
      $this->boolean($match)->isFalse();

      //rest to defaults
      $CFG_GLPI['notifications_irc'] = 0;
   }
}
