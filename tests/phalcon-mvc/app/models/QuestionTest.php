<?php

namespace OpenExam\Models;

use Exception;
use OpenExam\Tests\Phalcon\TestModelAccess;
use OpenExam\Tests\Phalcon\TestModelBasic;

/**
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class QuestionModel extends Question
{

        public function initialize()
        {
                parent::initialize();
        }

}

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-09-15 at 19:49:10.
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class QuestionTest extends TestModelBasic
{

        public function __construct()
        {
                parent::__construct(new QuestionModel());
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
                $question = Question::findFirst();
                self::assertNotNull($question);

                self::assertNotEquals($question->exam->count(), 0);
                self::assertNotEquals($question->topic->count(), 0);
                self::assertTrue(count($question->exam) == 1);
                self::assertTrue(count($question->topic) == 1);

                $question = Answer::findFirst()->question;
                self::assertNotNull($question);

                self::assertNotEquals($question->answers->count(), 0);
                self::assertTrue(count($question->answers) > 0);

                $question = Corrector::findFirst()->question;
                self::assertNotNull($question);

                self::assertNotEquals($question->correctors->count(), 0);
                self::assertTrue(count($question->correctors) > 0);
        }

        /**
         * @group model
         */
        public function testProperties()
        {
                $values = array(
                        'exam_id'  => Exam::findFirst()->id,
                        'topic_id' => Topic::findFirst()->id,
                        'score'    => 3.5,
                        'name'     => 'name1',
                        'quest'    => 'quest1',
                        'user'     => 'user1'
                );

                try {
                        $helper = new TestModelBasic(new Question());
                        $helper->tryPersist();
                        self::fail("Excepted constraint violation exception");
                } catch (Exception $exception) {
                        // Expected exception
                }

                try {
                        $helper = new TestModelBasic(new Question());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail($exception);
                }

                $helper->checkDefaults(array(
                        'status' => 'active'
                ));

                $values = array(
                        'exam_id'  => Exam::findFirst()->id,
                        'topic_id' => Topic::findFirst()->id,
                        'status'   => 'active',
                        'score'    => 4.5,
                        'name'     => 'Name1',
                        'quest'    => 'Question text 1',
                        'comment'  => 'Comment1',
                        'grades'   => '// Ooh, really missing C++ ;-)'
                );
                try {
                        $helper = new TestModelBasic(new Question());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail("Unexcepted constraint violation exception");
                }

                $values = array(
                        'exam_id'      => Exam::findFirst()->id,
                        'topic_id'     => Topic::findFirst()->id,
                        'status'       => 'active',
                        'score'        => 4.5,
                        'name'         => 'Name1',
                        'quest'        => 'Question text 1',
                        'comment'      => 'Comment1',
                        'grades'       => '// Ooh, really missing C++ ;-)',
                        'non_existing' => 666
                );
                try {
                        $helper = new TestModelBasic(new Question());
                        $helper->tryPersist($values);
                } catch (Exception $exception) {
                        self::fail("Unexcepted constraint violation exception");
                }

                $values = array(
                        'exam_id'  => Exam::findFirst()->id,
                        'topic_id' => Topic::findFirst()->id,
                        'score'    => 3.5,
                        'name'     => 'name1',
                        'quest'    => 'quest1',
                        'user'     => 'user1',
                        'status'   => 'unknown' // should fail at validation
                );
                try {
                        $helper = new TestModelBasic(new Question());
                        $helper->tryPersist($values);
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
                        'exam_id'  => Exam::findFirst()->id,
                        'topic_id' => Topic::findFirst()->id,
                        'score'    => 3.5,
                        'name'     => 'name1',
                        'quest'    => 'quest1',
                        'user'     => 'user1'
                );

                $helper = new TestModelAccess(new Question(), $values);
                $helper->testModelAccess();
        }

        /**
         * @covers OpenExam\Models\Question::getSource
         * @group model
         */
        public function testGetSource()
        {
                $expect = "questions";
                $actual = $this->object->getSource();
                self::assertNotNull($actual);
                self::assertEquals($expect, $actual);
        }

}
