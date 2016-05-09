<?php

namespace OpenExam\Library\Model\Audit\Config;

use OpenExam\Tests\Phalcon\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-04-27 at 02:48:11.
 */
class AuditConfigTest extends TestCase
{

        /**
         * @var AuditConfig
         */
        private $_object;
        /**
         * @var array 
         */
        private static $_config = array(
                'actions' => array('create', 'delete'),
                'file'    => array(
                        'format' => 'json',
                        'name'   => 'audit.dat'
                ),
                'data'    => array(
                        'connection' => 'dbwr',
                        'table'      => 'audit1'
                )
        );

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp()
        {
                $this->_object = new AuditConfig(self::$_config);
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown()
        {
                
        }

        /**
         * @covers OpenExam\Library\Model\Audit\Config\AuditConfig::getActions
         * @group model
         */
        public function testGetActions()
        {
                $expect = self::$_config['actions'];
                $actual = $this->_object->getActions();
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($expect, $actual);
        }

        /**
         * @covers OpenExam\Library\Model\Audit\Config\AuditConfig::hasAction
         * @group model
         */
        public function testHasAction()
        {
                $expect = true;
                $actual = $this->_object->hasAction('create');
                self::assertTrue($actual);
                self::assertEquals($expect, $actual);

                $expect = true;
                $actual = $this->_object->hasAction('delete');
                self::assertTrue($actual);
                self::assertEquals($expect, $actual);

                $expect = false;
                $actual = $this->_object->hasAction('update');
                self::assertFalse($actual);
                self::assertEquals($expect, $actual);
        }

        /**
         * @covers OpenExam\Library\Model\Audit\Config\AuditConfig::getConfig
         * @group model
         */
        public function testGetConfig()
        {
                $expect = self::$_config;
                $actual = $this->_object->getConfig();
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($expect, $actual);
        }

        /**
         * @covers OpenExam\Library\Model\Audit\Config\AuditConfig::getTargets
         * @group model
         */
        public function testGetTargets()
        {
                $actual = $this->_object->getTargets();
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertTrue(in_array('file', $actual));
                self::assertTrue(in_array('data', $actual));
                self::assertFalse(in_array('http', $actual));
        }

        /**
         * @covers OpenExam\Library\Model\Audit\Config\AuditConfig::getTarget
         * @group model
         */
        public function testGetTarget()
        {
                $expect = self::$_config['file'];
                $actual = $this->_object->getTarget('file');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($expect, $actual);

                $expect = self::$_config['data'];
                $actual = $this->_object->getTarget('data');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($expect, $actual);

                $expect = false;
                $actual = $this->_object->getTarget('http');
                self::assertNotNull($actual);
                self::assertTrue(is_bool($actual));
                self::assertEquals($expect, $actual);
        }

        /**
         * @covers OpenExam\Library\Model\Audit\Config\AuditConfig::hasTarget
         * @group model
         */
        public function testHasTarget()
        {
                $expect = true;
                $actual = $this->_object->hasTarget('data');
                self::assertNotNull($actual);
                self::assertTrue(is_bool($actual));
                self::assertEquals($expect, $actual);

                $expect = true;
                $actual = $this->_object->hasTarget('file');
                self::assertNotNull($actual);
                self::assertTrue(is_bool($actual));
                self::assertEquals($expect, $actual);

                $expect = false;
                $actual = $this->_object->hasTarget('http');
                self::assertNotNull($actual);
                self::assertTrue(is_bool($actual));
                self::assertEquals($expect, $actual);
        }

}