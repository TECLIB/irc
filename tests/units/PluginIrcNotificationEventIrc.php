<?php

namespace tests\units;

use \atoum;

class PluginIrcNotificationEventIrc extends \DbTestCase {

   public function testGetTargetField() {
      $data = [];
      $this->string(\PluginIrcNotificationEventIrc::getTargetField($data))->isIdenticalTo('users_id');
      $this->array($data)->isIdenticalTo(['users_id' => null]);

      $data = ['users_id' => '121'];
      $this->string(\PluginIrcNotificationEventIrc::getTargetField($data))->isIdenticalTo('users_id');
      $this->array($data)->isIdenticalTo(['users_id' => '121']);
   }

   public function testCanCron() {
      $this->boolean(\PluginIrcNotificationEventIrc::canCron())->isTrue();
   }
}
