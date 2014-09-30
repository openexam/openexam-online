<?php

namespace OpenExam\Models;

use Exception;
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
class ContributorTest extends TestModelBasic
{

        public function __construct()
        {
                parent::__construct(new ContributorModel());
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
                $contributor = Contributor::findFirst();
                self::assertNotNull($contributor);

                self::assertNotEquals($contributor->exam->count(), 0);
                self::assertTrue(count($contributor->exam) == 1);
        }

        /**
         * @group model
         */
        public function testProperties()
        {
                $values = array(
                        'exam_id' => Exam::findFirst()->id,
                        'user'    => 'user1'
                );

                try {
                        $helper = new TestModelBasic(new Contributor());
                        $helper->tryPersist();
                        self::fail("Excepted constraint violation exception");
                } catch (Exception$exception) {
                        // Expected exception
                }

                try {
                        $helper = new TestModelBasic(new Contributor());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail($exception);
                }

                $values = array(
                        'exam_id'      => Exam::findFirst()->id,
                        'user'         => 'user1',
                        'non_existing' => 666   // ignored wihout error
                );
                try {
                        $helper = new TestModelBasic(new Contributor());
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
                        'exam_id' => Exam::findFirst()->id,
                        'user'    => 'user1'
                );

                $helper = new TestModelAccess(new Contributor(), $values);
                $helper->testModelAccess();
        }

        /**
         * @covers OpenExam\Models\Contributor::getSource
         * @group model
         */
        public function testGetSource()
        {
                $expect = "contributors";
                $actual = $this->object->getSource();
                self::assertNotNull($actual);
                self::assertEquals($expect, $actual);
        }

}
