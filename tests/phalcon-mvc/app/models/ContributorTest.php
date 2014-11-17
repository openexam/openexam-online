<?php

namespace OpenExam\Models;

use Exception;
use OpenExam\Tests\Phalcon\TestCase;
use OpenExam\Tests\Phalcon\TestModelAccess;
use OpenExam\Tests\Phalcon\TestModelBasic;

/**
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class ContributorModel extends Contributor
{

        public function initialize()
        {
                parent::initialize();
        }

}

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-09-15 at 18:28:47.
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class ContributorTest extends TestCase
{

        /**
         * @group model
         */
        public function testRelations()
        {
                $contributor = Contributor::findFirst();
                self::assertNotNull($contributor);

                self::assertNotEquals(0, $contributor->exam->count());
                self::assertTrue(count($contributor->exam) == 1);
        }

        /**
         * @group model
         */
        public function testProperties()
        {
                $values = array(
                        'exam_id' => Exam::findFirst()->id,
                        'user'    => $this->caller
                );

                try {
                        $helper = new TestModelBasic(new Contributor());
                        $helper->tryPersist();
                        self::error("Excepted constraint violation exception");
                } catch (Exception$exception) {
                        // Expected exception
                }

                try {
                        $helper = new TestModelBasic(new Contributor());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::error($exception);
                }

                $values = array(
                        'exam_id'      => Exam::findFirst()->id,
                        'user'         => $this->caller,
                        'non_existing' => 666   // ignored without error
                );
                try {
                        $helper = new TestModelBasic(new Contributor());
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
                $helper = new TestModelAccess(new Contributor());
                $helper->testModelAccess();
        }

        /**
         * @covers OpenExam\Models\Contributor::getSource
         * @group model
         */
        public function testGetSource()
        {
                $object = new ContributorModel();
                $expect = "contributors";
                $actual = $object->getSource();
                self::assertNotNull($actual);
                self::assertEquals($expect, $actual);
        }

}
