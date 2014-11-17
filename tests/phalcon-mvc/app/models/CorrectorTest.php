<?php

namespace OpenExam\Models;

use Exception;
use OpenExam\Tests\Phalcon\TestCase;
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
class CorrectorTest extends TestCase
{

        /**
         * @group model
         */
        public function testRelations()
        {
                $corrector = Corrector::findFirst();
                self::assertNotNull($corrector);

                self::assertNotEquals(0, $corrector->question->count());
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
                        'user'        => $this->caller
                );

                try {
                        $helper = new TestModelBasic(new Corrector());
                        $helper->tryPersist();
                        self::error("Excepted constraint violation exception");
                } catch (Exception $exception) {
                        // Expected exception
                }

                try {
                        $helper = new TestModelBasic(new Corrector());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::error($exception);
                }

                $values = array(
                        'question_id'  => Question::findFirst()->id,
                        'user'         => $this->caller,
                        'non_existing' => 666   // ignored wihout error
                );
                try {
                        $helper = new TestModelBasic(new Corrector());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::error("Unexcepted constraint violation exception");
                }
        }

        /**
         * @group model
         * @group security
         */
        public function testAccess()
        {
                $helper = new TestModelAccess(new Corrector());
                $helper->testModelAccess();
        }

        /**
         * @covers OpenExam\Models\Corrector::getSource
         * @group model
         */
        public function testGetSource()
        {
                $object = new CorrectorModel();
                $expect = "correctors";
                $actual = $object->getSource();
                self::assertNotNull($actual);
                self::assertEquals($expect, $actual);
        }

}
