<?php

namespace OpenExam\Models;

use Exception;
use OpenExam\Tests\Phalcon\TestModel;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-09-15 at 18:04:22.
 * 
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class ComputerTest extends TestModel
{

        public function __construct()
        {
                parent::__construct(new Computer());
        }

        /**
         * @group model
         */
        public function testRelations()
        {
                $computer = Computer::findFirst();

                self::assertNotEquals($computer->locks->count(), 0);
                self::assertNotEquals($computer->room->count(), 0);

                self::assertTrue(count($computer->locks) > 0);
                self::assertTrue(count($computer->room) == 1);
        }

        /**
         * @group model
         */
        public function testProperties()
        {
                $values = array(
                        'room_id'  => Room::findFirst()->id,
                        'ipaddr'   => '127.0.0.1',
                        'port'     => 4092,
                        'password' => 'secret'
                );

                try {
                        $helper = new TestModel(new Computer());
                        $helper->tryPersist();
                        self::fail("Excepted constraint violation exception");
                } catch (Exception $exception) {
                        // Expected exception
                }

                try {
                        $helper = new TestModel(new Computer());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail($exception);
                }

                $values = array(
                        'room_id'  => Room::findFirst()->id,
                        'created'  => date('Y-m-d H:i:s'),
                        'updated'  => date('Y-m-d H:i:s'),
                        'hostname' => 'localhost',
                        'ipaddr'   => '127.0.0.1',
                        'port'     => 4092,
                        'password' => 'secret'
                );
                try {
                        $helper = new TestModel(new Computer());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail($exception);
                }

                $values = array(
                        'room_id'      => Room::findFirst()->id,
                        'created'      => date('Y-m-d H:i:s'),
                        'updated'      => date('Y-m-d H:i:s'),
                        'non_existing' => 666
                );
                try {
                        $helper = new TestModel(new Computer());
                        $helper->tryPersist();
                        self::fail("Excepted constraint violation exception");
                } catch (Exception $exception) {
                        // Expected excetion (user required)
                }
        }

        /**
         * @covers OpenExam\Models\Computer::getSource
         * @group model
         */
        public function testGetSource()
        {
                $expect = "computers";
                $actual = $this->object->getSource();
                self::assertNotNull($actual);
                self::assertEquals($expect, $actual);
        }

}
