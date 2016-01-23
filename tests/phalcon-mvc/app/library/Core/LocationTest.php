<?php

namespace OpenExam\Library\Core;

use OpenExam\Tests\Phalcon\SampleData;
use OpenExam\Tests\Phalcon\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-04-09 at 15:21:03.
 */
class LocationTest extends TestCase
{

        /**
         * @var Location
         */
        protected $object;
        /**
         * Test locations.
         * @var array 
         */
        private static $locations = array(
                'O1' => array(
                        'C1' => array(
                                'P1' => array(
                                        'addr' => '192.168.1.1'
                                ),
                                'P2' => array(
                                        'addr' => '192.168.1.2',
                                        'desc' => 'Place 2'
                                )
                        ),
                        'C2' => array(
                                'P3' => array(
                                        'addr' => '192.168.2.0/24'
                                ),
                                'P4' => array(
                                        'addr' => '10.1.0.0/16',
                                        'desc' => 'Place 4'
                                )
                        )
                ),
                'O2' => array(
                        'C3' => array(
                                'P5' => array(
                                        'addr' => '169.254.1.50-169.255.255.254',
                                        'info' => 'http://en.wikipedia.org/wiki/Link-local_address'
                                ),
                                'P6' => array(
                                        'addr' => '|^.*\.uu\.se$|',
                                        'desc' => 'Uppsala University'
                                )
                        ),
                )
        );
        /**
         * Sample data object.
         * @var SampleData 
         */
        private $sample;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp()
        {
                $this->object = new Location(self::$locations);
                $this->sample = new SampleData();
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown()
        {
                
        }

        /**
         * @covers OpenExam\Library\Core\Location::getEntry
         * @group core
         */
        public function testGetEntry()
        {
                // 
                // Test existing entries:
                // 

                $actual = $this->object->getEntry('192.168.1.1');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O1');
                self::assertEquals($actual['ckey'], 'C1');
                self::assertEquals($actual['pkey'], 'P1');

                $actual = $this->object->getEntry('192.168.1.2');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O1');
                self::assertEquals($actual['ckey'], 'C1');
                self::assertEquals($actual['pkey'], 'P2');

                $actual = $this->object->getEntry('192.168.2.78');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O1');
                self::assertEquals($actual['ckey'], 'C2');
                self::assertEquals($actual['pkey'], 'P3');

                $actual = $this->object->getEntry('10.1.0.1');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O1');
                self::assertEquals($actual['ckey'], 'C2');
                self::assertEquals($actual['pkey'], 'P4');

                $actual = $this->object->getEntry('10.1.255.255');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O1');
                self::assertEquals($actual['ckey'], 'C2');
                self::assertEquals($actual['pkey'], 'P4');

                $actual = $this->object->getEntry('169.254.37.50');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O2');
                self::assertEquals($actual['ckey'], 'C3');
                self::assertEquals($actual['pkey'], 'P5');

                $actual = $this->object->getEntry('169.255.1.1');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O2');
                self::assertEquals($actual['ckey'], 'C3');
                self::assertEquals($actual['pkey'], 'P5');

                $actual = $this->object->getEntry('169.255.255.254');
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O2');
                self::assertEquals($actual['ckey'], 'C3');
                self::assertEquals($actual['pkey'], 'P5');

                $actual = $this->object->getEntry('130.238.7.135');    // live.webb.uu.se
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O2');
                self::assertEquals($actual['ckey'], 'C3');
                self::assertEquals($actual['pkey'], 'P6');

                $actual = $this->object->getEntry('130.238.55.23');    // it.bmc.uu.se
                self::assertNotNull($actual);
                self::assertTrue(is_array($actual));
                self::assertEquals($actual['okey'], 'O2');
                self::assertEquals($actual['ckey'], 'C3');
                self::assertEquals($actual['pkey'], 'P6');

                // 
                // Test non-existing entries:
                // 

                $actual = $this->object->getEntry('10.0.0.1');
                self::assertNotNull($actual);
                self::assertFalse($actual);

                $actual = $this->object->getEntry('192.168.3.1');
                self::assertNotNull($actual);
                self::assertFalse($actual);
        }

        /**
         * @covers OpenExam\Library\Core\Location::getEntries
         * @group core
         */
        public function testGetEntries()
        {
                $access = $this->sample->getSample('access');

                $entries = $this->object->getEntries();
                self::assertTrue(array_key_exists('system', $entries));
                self::assertTrue(array_key_exists('recent', $entries));
                self::assertFalse(array_key_exists('active', $entries));

                $entries = $this->object->getEntries($access['exam_id']);
                self::assertTrue(array_key_exists('system', $entries));
                self::assertTrue(array_key_exists('recent', $entries));
                self::assertTrue(array_key_exists('active', $entries));

                $entries = $this->object->getEntries($access['exam_id'], array(
                        'system' => false,
                        'recent' => false,
                        'active' => false
                ));
                self::assertFalse(array_key_exists('system', $entries));
                self::assertFalse(array_key_exists('recent', $entries));
                self::assertFalse(array_key_exists('active', $entries));

                $entries = $this->object->getEntries($access['exam_id'], array(
                        'system' => true,
                        'recent' => false,
                        'active' => false
                ));
                self::assertTrue(array_key_exists('system', $entries));
                self::assertFalse(array_key_exists('recent', $entries));
                self::assertFalse(array_key_exists('active', $entries));

                $entries = $this->object->getEntries($access['exam_id'], array(
                        'system' => false,
                        'recent' => true,
                        'active' => false
                ));
                self::assertFalse(array_key_exists('system', $entries));
                self::assertTrue(array_key_exists('recent', $entries));
                self::assertFalse(array_key_exists('active', $entries));

                $entries = $this->object->getEntries($access['exam_id'], array(
                        'system' => false,
                        'recent' => false,
                        'active' => true
                ));
                self::assertFalse(array_key_exists('system', $entries));
                self::assertFalse(array_key_exists('recent', $entries));
                self::assertTrue(array_key_exists('active', $entries));

                $path = explode(';', $access['name']);
                self::assertTrue(count($path) == 3);
                self::assertTrue(array_key_exists($path[0], $entries['active']));
                self::assertTrue(array_key_exists($path[1], $entries['active'][$path[0]]));
                self::assertTrue(array_key_exists($path[2], $entries['active'][$path[0]][$path[1]]));
        }

        /**
         * @covers OpenExam\Library\Core\Location::getActive
         * @group core
         */
        public function testGetActive()
        {
                $access = $this->sample->getSample('access');

                $entries = $this->object->getEntries($access['exam_id'], array('active' => true), false);
                self::assertNotNull($entries);
                self::assertNotNull($entries['active']);
                $path = explode(";", $access['name']);
                self::assertEquals($access, $entries['active'][$path[0]][$path[1]][$path[2]]);
                
                $entries = $this->object->getActive($access['exam_id']);
                self::assertNotNull($entries);
                $path = explode(";", $access['name']);
                $path = sprintf("%s -> %s -> %s", $path[0], $path[1], $path[2]);
                self::assertNotNull($entries[$path]);
                self::assertTrue(is_array($entries[$path]));
                self::assertEquals($access, $entries[$path]);
        }

        /**
         * @covers OpenExam\Library\Core\Location::getRecent
         * @group core
         */
        public function testGetRecent()
        {
                self::markTestIncomplete("Depends on runtime data");
        }

        /**
         * @covers OpenExam\Library\Core\Location::getSystem
         * @group core
         */
        public function testGetSystem()
        {
                $entries = $this->object->getEntries(0, array('system' => true), false);
                self::assertNotNull($entries);
                self::assertNotNull($entries['system']);
                self::assertEquals($entries['system'], self::$locations);
                self::assertEquals($entries['system'], self::$locations);
        }

}