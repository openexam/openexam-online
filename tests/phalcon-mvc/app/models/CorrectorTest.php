<?php

namespace OpenExam\Models;

use Exception;
use OpenExam\Tests\Phalcon\TestModelAccess;
use OpenExam\Tests\Phalcon\TestModelBasic;

/**
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class CorrectorModel extends Corrector
{

        public function initialize()
        {
                parent::initialize();
        }

}

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-09-17 at 04:33:42.
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class CorrectorTest extends TestModelBasic
{

        public function __construct()
        {
                parent::__construct(new CorrectorModel());
        }

        protected function setUp()
        {
                $this->getDI()->get('user')->setPrimaryRole(null);
        }

        /**
         * @group model
         */
        public function testRelations()
        {
                $corrector = Corrector::findFirst();
                self::assertNotNull($corrector);

                self::assertNotEquals($corrector->question->count(), 0);
                self::assertTrue(count($corrector->question) == 1);
        }

        /**
         * @covers OpenExam\Models\Corrector::initialize
         * @group model
         */
        public function testProperties()
        {
                $values = array(
                        'question_id' => Question::findFirst()->id,
                        'user'        => 'user1'
                );

                try {
                        $helper = new TestModelBasic(new Corrector());
                        $helper->tryPersist();
                        self::fail("Excepted constraint violation exception");
                } catch (Exception $exception) {
                        // Expected exception
                }

                try {
                        $helper = new TestModelBasic(new Corrector());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail($exception);
                }

                $values = array(
                        'question_id'  => Question::findFirst()->id,
                        'user'         => 'user1',
                        'non_existing' => 666   // ignored wihout error
                );
                try {
                        $helper = new TestModelBasic(new Corrector());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail("Unexcepted constraint violation exception");
                }
        }

        /**
         * @group model
         * @group security
         */
        public function testAccess()
        {
                $values = array(
                        'question_id' => Question::findFirst()->id,
                        'user'        => 'user1'
                );

                $helper = new TestModelAccess(new Corrector(), $values);
                $helper->testModelAccess();
        }

        /**
         * @covers OpenExam\Models\Corrector::getSource
         * @group model
         */
        public function testGetSource()
        {
                $expect = "correctors";
                $actual = $this->object->getSource();
                self::assertNotNull($actual);
                self::assertEquals($expect, $actual);
        }

}