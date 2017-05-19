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

   public function testSend() {
      global $CFG_GLPI;

      $CFG_GLPI['pluginirc_config_channels'] = '#glpi_test';

      $connection = new \mock\PluginIrcConnection();
      $event = new \PluginIrcNotificationEventIrc();

      $fake_srv = [];
      $this->calling($connection)->connectIrc = function() use (&$fake_srv) {
         $this->server = &$fake_srv;
         return $fake_srv;
      };

      $this->calling($connection)->sendCommand = function($cmd) {
         $this->server[] = $cmd;
      };
      $event::setConnection($connection);

      $this->array($fake_srv)->isIdenticalTo([]);

      $event->send([
         ['body_text' => 'This is a message from GLPI IRC plugin unit tests!'],
         ['body_text' => 'This is a message from GLPI IRC plugin unit tests!']
      ]);
      $this->array($fake_srv)->isIdenticalTo([
         0 => 'JOIN :#glpi_test',
         1 => 'PRIVMSG #glpi_test :This is a message from GLPI IRC plugin unit tests!',
         2 => 'QUIT'
      ]);

      $fake_srv = [];
      $CFG_GLPI['pluginirc_config_channels'] = 'glpi_test,#another_channel';
      $event->send([
         ['body_text' => 'This is a message from GLPI IRC plugin unit tests!'],
      ]);
      $this->array($fake_srv)->isIdenticalTo([
         0 => 'JOIN :#glpi_test',
         1 => 'JOIN :#another_channel',
         2 => 'PRIVMSG #glpi_test :This is a message from GLPI IRC plugin unit tests!',
         3 => 'PRIVMSG #another_channel :This is a message from GLPI IRC plugin unit tests!',
         4 => 'QUIT'
      ]);

      $fake_srv = [];
      $CFG_GLPI['pluginirc_config_nicksto'] = 'oneuser,anotheruser';
      $event->send([
         ['body_text' => 'This is a message from GLPI IRC plugin unit tests!'],
      ]);
      $this->array($fake_srv)->isIdenticalTo([
         0 => 'JOIN :#glpi_test',
         1 => 'JOIN :#another_channel',
         2 => 'PRIVMSG #glpi_test :This is a message from GLPI IRC plugin unit tests!',
         3 => 'PRIVMSG #another_channel :This is a message from GLPI IRC plugin unit tests!',
         4 => 'NOTICE oneuser :This is a message from GLPI IRC plugin unit tests!',
         5 => 'NOTICE anotheruser :This is a message from GLPI IRC plugin unit tests!',
         6 => 'QUIT'
      ]);
   }
}
