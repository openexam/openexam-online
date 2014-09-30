<?php

namespace OpenExam\Models;

use Exception;
use OpenExam\Tests\Phalcon\TestModelAccess;
use OpenExam\Tests\Phalcon\TestModelBasic;

/**
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class AnswerModel extends Answer
{

        public function initialize()
        {
                parent::initialize();
        }

}

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-09-15 at 11:38:53.
 * 
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class AnswerTest extends TestModelBasic
{

        public function __construct()
        {
                parent::__construct(new AnswerModel());
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
                $answer = Result::findFirst()->answer;
                self::assertNotNull($answer);

                self::assertNotEquals($answer->question->count(), 0);
                self::assertNotEquals($answer->result->count(), 0);
                self::assertNotEquals($answer->student->count(), 0);

                self::assertTrue(count($answer->question) == 1);
                self::assertTrue(count($answer->result) == 1);
                self::assertTrue(count($answer->student) == 1);

                $answer = File::findFirst()->answer;
                self::assertNotEquals($answer->files->count(), 0);
                self::assertTrue(count($answer->files) > 0);
        }

        /**
         * @group model
         */
        public function testProperties()
        {
                $values = array(
                        'student_id'  => Student::findFirst()->id,
                        'question_id' => Question::findFirst()->id
                );

                try {
                        $helper = new TestModelBasic(new Answer());
                        $helper->tryPersist();
                        self::fail("Excepted constraint violation exception");
                } catch (Exception $exception) {
                        // Expected exception
                }

                try {
                        $helper = new TestModelBasic(new Answer());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail($exception);
                }

                $helper->checkDefaults(array(
                        'answered' => false
                ));

                $values = array(
                        'student_id'  => Student::findFirst()->id,
                        'question_id' => Question::findFirst()->id,
                        'answered'    => true,
                        'answer'      => 'Answer text',
                        'comment'     => 'Comment text'
                );
                try {
                        $helper = new TestModelBasic(new Answer());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail($exception);
                }

                $values = array(
                        'student_id'   => Student::findFirst()->id,
                        'question_id'  => Question::findFirst()->id,
                        'non_existing' => 666
                );
                try {
                        $helper = new TestModelBasic(new Answer());
                        $helper->tryPersist();
                        self::fail("Excepted constraint violation exception");
                } catch (Exception $exception) {
                        // Expected exception
                }
        }

        /**
         * @group model
         * @group security
         */
        public function testAccess()
        {
                $values = array(
                        'student_id'  => Student::findFirst()->id,
                        'question_id' => Question::findFirst()->id
                );

                $helper = new TestModelAccess(new Answer(), $values);
                $helper->testModelAccess();
        }

        /**
         * @covers OpenExam\Models\Answer::getSource
         * @group model
         */
        public function testGetSource()
        {
                $expect = "answers";
                $actual = $this->object->getSource();
                self::assertNotNull($actual);
                self::assertEquals($expect, $actual);
        }

}
