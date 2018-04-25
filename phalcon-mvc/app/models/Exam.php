<?php

/*
 * Copyright (C) 2014-2018 The OpenExam Project
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// 
// File:    Exam.php
// Created: 2014-02-24 07:04:58
// 
// Author:  Anders Lövgren (Computing Department at BMC, Uppsala University)
// 

namespace OpenExam\Models;

use OpenExam\Library\Core\Exam\Check;
use OpenExam\Library\Core\Exam\Grade;
use OpenExam\Library\Core\Exam\Staff;
use OpenExam\Library\Core\Exam\State;
use OpenExam\Library\Core\Pattern;
use OpenExam\Library\Model\Behavior\Exam as ExamBehavior;
use OpenExam\Library\Model\Behavior\Generate\Ownership;
use OpenExam\Library\Model\Behavior\Transform\DateTimeNull;
use OpenExam\Library\Model\Behavior\Transform\FilterText;
use OpenExam\Library\Model\Behavior\Transform\Trim;
use OpenExam\Library\Model\Exception;
use OpenExam\Library\Model\Filter;
use OpenExam\Library\Model\Validation\DateTime as DateTimeValidator;
use OpenExam\Library\Model\Validation\Sequence as SequenceValidator;
use OpenExam\Library\Security\Roles;
use Phalcon\DI as PhalconDI;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Behavior\Timestampable;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex as RegexValidator;

/**
 * The exam model.
 * 
 * Represents an exam. This class is the central model to which most other
 * models are related.
 * 
 * An exam is in one of these states: preparing, upcoming, active, finished 
 * or decoded. The enquiry step defines an intermediate state before decoding
 * if the exam needs some investigation.
 * 
 * The grades property (array) is defined as JSON in the database. It is
 * either an object defining grades or an array defining the function body
 * for a function that evaluates the final score for a single student on 
 * the exam:
 * 
 * <code>
 * { data: { "U":"0", "G":"55", "VG":"80" } }
 * { func: { // source code } }
 * </code>
 * 
 * @property Contributor[] $contributors The contributors for this exam.
 * @property Decoder[] $decoders The decoders for this exam.
 * @property Invigilator[] $invigilators The invigilators for this exam.
 * @property Lock[] $locks The computer locks acquired for this exam.
 * @property Resource[] $resources The multimedia or resource files associated with this exam.
 * @property Question[] $questions The questions that belongs to this exam.
 * @property Student[] $students The students assigned to this exam.
 * @property Topic[] $topics The topics associated with this exam.
 * @property Access[] $access The access definitions associated with this exam.
 * @property Render[] $render The render jobs associated with this exam.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Exam extends ModelBase
{

        /**
         * Show responsible people for examination.
         */
        const RESULT_EXPOSE_EMPLOYEES = 1;
        /**
         * Include statistics of all students.
         */
        const RESULT_OTHERS_STATISTICS = 2;
        /**
         * Expose student scoring percentage during the correction phase.
         */
        const SHOW_SUMMARY_DURING_CORRECTION = 4;
        /**
         * Blur media (i.e. images) in student result.
         */
        const BLUR_MEDIA_CONTENT_IN_RESULT = 8;
        /**
         * Hide media (i.e. images) in student result.
         */
        const HIDE_MEDIA_CONTENT_IN_RESULT = 16;
        /**
         * Expose anonymous code for correctors during correction.
         */
        const SHOW_CODE_DURING_CORRECTION = 32;
        /**
         * Don't include student grade in generated result.
         */
        const HIDE_STUDENT_GRADE_IN_RESULT = 64;

        /**
         * This object ID.
         * @var integer
         */
        public $id;
        /**
         * The name of the exam.
         * @var string
         */
        public $name;
        /**
         * The exam description.
         * @var string
         */
        public $descr;
        /**
         * The exam start date/time (might be null).
         * @var string
         */
        public $starttime;
        /**
         * The exam end date/time (might be null).
         * @var string
         */
        public $endtime;
        /**
         * The exam create date/time.
         * @var string
         */
        public $created;
        /**
         * The exam update date/time.
         * @var string
         */
        public $updated;
        /**
         * The creator of the exam.
         * @var string
         */
        public $creator;
        /**
         * Bitmask of exposed details in result (see RESULT_XXX constants).
         * @var integer
         */
        public $details;
        /**
         * Does this exam needs an enquiry?
         * @var bool 
         */
        public $enquiry;
        /**
         * Is this exam decoded?
         * @var bool
         */
        public $decoded;
        /**
         * Is this exam published?
         * @var bool
         */
        public $published;
        /**
         * The organization unit.
         * @var string
         */
        public $orgunit;
        /**
         * The organization division (parent of department).
         * @var string 
         */
        public $division;
        /**
         * The organization department (parent of group).
         * @var string 
         */
        public $department;
        /**
         * The organization group (department group).
         * @var string 
         */
        public $workgroup;
        /**
         * The exam grades.
         * @var string
         */
        public $grades;
        /**
         * The course code related to this exam.
         * @var string 
         */
        public $course;
        /**
         * The unique exam code.
         * @var string 
         */
        public $code;
        /**
         * Is this exam a testcase?
         * @var bool
         */
        public $testcase;
        /**
         * Does this exam require client lockdown?
         * @var object
         */
        public $lockdown;
        /**
         * Examination state (bitmask).
         * @var int 
         */
        public $state;
        /**
         * Examination flags (e.g. decodable).
         * @var string[]
         */
        public $flags;
        /**
         * The exam state.
         * @var State 
         */
        private $_state;
        /**
         * The exam staff.
         * @var Staff 
         */
        private $_staff;
        /**
         * The exam grade.
         * @var Grade 
         */
        private $_grade;
        /**
         * The exam check.
         * @var Check
         */
        private $_check;

        public function initialize()
        {
                parent::initialize();

                $this->hasMany('id', 'OpenExam\Models\Contributor', 'exam_id', array(
                        'alias'    => 'contributors',
                        'reusable' => true
                ));
                $this->hasMany('id', 'OpenExam\Models\Decoder', 'exam_id', array(
                        'alias'    => 'decoders',
                        'reusable' => true
                ));
                $this->hasMany('id', 'OpenExam\Models\Invigilator', 'exam_id', array(
                        'alias'    => 'invigilators',
                        'reusable' => true
                ));
                $this->hasMany('id', 'OpenExam\Models\Lock', 'exam_id', array(
                        'alias'    => 'locks',
                        'reusable' => true
                ));
                $this->hasMany('id', 'OpenExam\Models\Question', 'exam_id', array(
                        'alias'    => 'questions',
                        'reusable' => true
                ));
                $this->hasMany("id", "OpenExam\Models\Resource", "exam_id", array(
                        'alias'    => 'resources',
                        'reusable' => true
                ));
                $this->hasMany('id', 'OpenExam\Models\Student', 'exam_id', array(
                        'alias'    => 'students',
                        'reusable' => true,
                        'params'   => array(
                                'order' => 'id'
                        )
                ));
                $this->hasMany('id', 'OpenExam\Models\Topic', 'exam_id', array(
                        'alias'    => 'topics',
                        'reusable' => true
                ));
                $this->hasMany('id', 'OpenExam\Models\Access', 'exam_id', array(
                        'alias'    => 'access',
                        'reusable' => true
                ));
                $this->hasMany('id', 'OpenExam\Models\Render', 'exam_id', array(
                        'alias'    => 'render',
                        'reusable' => true
                ));

                $this->addBehavior(new Timestampable(array(
                        'beforeValidationOnCreate' => array(
                                'field'  => 'updated',
                                'format' => 'Y-m-d H:i:s'
                        ),
                        'beforeValidationOnUpdate' => array(
                                'field'  => 'updated',
                                'format' => 'Y-m-d H:i:s'
                        )
                )));

                $this->addBehavior(new Timestampable(array(
                        'beforeValidationOnCreate' => array(
                                'field'  => 'created',
                                'format' => 'Y-m-d H:i:s'
                        )
                )));

                $this->addBehavior(new DateTimeNull(array(
                        'beforeSave' => array(
                                'field'  => array('starttime', 'endtime'),
                                'format' => 'Y-m-d H:i:s'
                        )
                )));

                $this->addBehavior(new Ownership(array(
                        'beforeValidationOnCreate' => array(
                                'field' => 'creator',
                                'force' => false
                        ),
                        'beforeValidationOnUpdate' => array(
                                'field' => 'creator',
                                'force' => false
                        )
                )));

                $this->addBehavior(new Trim(array(
                        'beforeValidationOnCreate' => array(
                                'field' => array('name', 'descr', 'starttime', 'endtime', 'grades', 'course', 'code'),
                                'value' => null
                        ),
                        'beforeValidationOnUpdate' => array(
                                'field' => array('name', 'descr', 'starttime', 'endtime', 'grades', 'course', 'code'),
                                'value' => null
                        )
                )));

                // 
                // TODO: better do filtering on client side.
                // 
                $this->addBehavior(new FilterText(array(
                        'beforeValidationOnCreate' => array(
                                'fields' => 'descr'
                        ),
                        'beforeValidationOnUpdate' => array(
                                'fields' => 'descr'
                        )
                    )
                ));

                $this->addBehavior(new ExamBehavior());

                // 
                // Required for datetime validator:
                // 
                $this->keepSnapshots(true);
        }

        /**
         * Get source table name.
         * @return string
         */
        public function getSource()
        {
                return 'exams';
        }

        /**
         * Get table column map.
         * @return array
         */
        public function columnMap()
        {
                return array(
                        'id'        => 'id',
                        'name'      => 'name',
                        'descr'     => 'descr',
                        'starttime' => 'starttime',
                        'endtime'   => 'endtime',
                        'created'   => 'created',
                        'updated'   => 'updated',
                        'creator'   => 'creator',
                        'details'   => 'details',
                        'enquiry'   => 'enquiry',
                        'decoded'   => 'decoded',
                        'published' => 'published',
                        'orgunit'   => 'orgunit',
                        'orgdiv'    => 'division',
                        'orgdep'    => 'department',
                        'orggrp'    => 'workgroup',
                        'grades'    => 'grades',
                        'course'    => 'course',
                        'code'      => 'code',
                        'testcase'  => 'testcase',
                        'lockdown'  => 'lockdown'
                );
        }

        /**
         * Validates business rules.
         * @return boolean
         */
        public function validation()
        {
                $validator = new Validation();

                $validator->add(
                    "creator", new RegexValidator(
                    array(
                        "message" => "The username '$this->creator' is not matching expected format",
                        "pattern" => Pattern::get(Pattern::MATCH_USER)
                    )
                ));
                $validator->add(
                    "timestamp", new SequenceValidator(
                    array(
                        "sequence" => array("starttime", "endtime"),
                        "message"  => "Start time can't come after end time",
                        "type"     => "datetime"
                    )
                ));
                $validator->add(
                    array(
                        "starttime", "endtime"
                    ), new DateTimeValidator(
                    array(
                        "message" => "The datetime value can't be in the past",
                        "current" => $this->getSnapshotData()
                    )
                ));

                return $this->validate($validator);
        }

        /**
         * Called before model is created.
         */
        public function beforeValidationOnCreate()
        {
                if (!isset($this->details)) {
                        $this->details = $this->getDI()->getConfig()->result->details;
                }
                if (!isset($this->enquiry)) {
                        $this->enquiry = false;
                }
                if (!isset($this->decoded)) {
                        $this->decoded = false;
                }
                if (!isset($this->published)) {
                        $this->published = false;
                }
                if (!isset($this->testcase)) {
                        $this->testcase = false;
                }
                if (!isset($this->lockdown)) {
                        $this->lockdown = (object) array('enable' => true);
                }
                if (!isset($this->orgunit) || !isset($this->division)) {
                        $this->setOrganization();
                }
        }

        /**
         * Called before model is saved.
         */
        public function beforeSave()
        {
                $this->enquiry = $this->enquiry ? 'Y' : 'N';
                $this->decoded = $this->decoded ? 'Y' : 'N';
                $this->published = $this->published ? 'Y' : 'N';
                $this->testcase = $this->testcase ? 'Y' : 'N';
                if (!is_string($this->lockdown)) {
                        $this->lockdown = json_encode($this->lockdown);
                }
        }

        /**
         * Called after model is saved.
         */
        public function afterSave()
        {
                $this->enquiry = $this->enquiry == 'Y';
                $this->decoded = $this->decoded == 'Y';
                $this->published = $this->published == 'Y';
                $this->testcase = $this->testcase == 'Y';
                $this->lockdown = json_decode($this->lockdown);
        }

        /**
         * Called after the model was read.
         */
        public function afterFetch()
        {
                if ($this->name == '@@replace@@') {
                        $this->name = null;
                }
                if ($this->descr == '@@replace@@') {
                        $this->descr = null;
                }

                $this->enquiry = $this->enquiry == 'Y';
                $this->decoded = $this->decoded == 'Y';
                $this->published = $this->published == 'Y';
                $this->testcase = $this->testcase == 'Y';
                $this->lockdown = json_decode($this->lockdown);

                $this->setStudentDatetime();

                $state = $this->getState();
                $this->state = $state->getState();
                $this->flags = $state->getFlags();

                parent::afterFetch();
        }

        /**
         * Called after the model was deleted.
         */
        public function afterDelete()
        {
                $cachekey = sprintf("roles-%s", $this->creator);

                if ($this->getDI()->get('cache')->exists($cachekey)) {
                        $this->getDI()->get('cache')->delete($cachekey);
                }
        }

        /**
         * Get examination staff.
         * @return Staff
         */
        public function getStaff()
        {
                if (!isset($this->_staff)) {
                        return $this->_staff = new Staff($this);
                } else {
                        return $this->_staff;
                }
        }

        /**
         * Get examination state.
         * @return State
         */
        public function getState()
        {
                if (!isset($this->_state)) {
                        return $this->_state = new State($this);
                } else {
                        return $this->_state;
                }
        }

        /**
         * Get examination graduation.
         * @return Grades
         */
        public function getGrade()
        {
                if (!isset($this->_grade)) {
                        return $this->_grade = new Grade($this);
                } else {
                        return $this->_grade;
                }
        }

        /**
         * Get exam check.
         * @return Check
         */
        public function getCheck()
        {
                if (!isset($this->_check)) {
                        return $this->_check = new Check($this);
                } else {
                        return $this->_check;
                }
        }

        /**
         * Get exam status.
         * 
         * Use Check::STATUS_XXX for comparing readiness state of this exam.
         * @return int
         */
        public function getStatus()
        {
                return $this->getCheck()->getStatus();
        }

        /**
         * Get filter for result set.
         * @param array $params The query parameters.
         * @return Filter The result set filter object.
         */
        public function getFilter($params)
        {
                $filter = array();

                foreach (array('state', 'flags') as $key) {
                        if (isset($params[$key])) {
                                $filter[$key] = $params[$key];
                        }
                }

                if (count($filter) != 0) {
                        return new Filter($filter);
                } else {
                        return parent::getFilter($params);
                }
        }

        /**
         * Specialization of findFirst() for the exam model.
         * 
         * @param array $parameters The query parameters.
         * @return Model
         * @uses Exam::find()
         * 
         * @see http://docs.phalconphp.com/en/latest/reference/models.html#finding-records
         * @see http://docs.phalconphp.com/en/latest/api/Phalcon_Mvc_Model_Query_Builder.html
         */
        public static function findFirst($parameters = null)
        {
                if (!isset($parameters)) {
                        $parameters = array('limit' => 1);
                }
                if (is_numeric($parameters)) {
                        $parameters = "id = $parameters";
                }
                if (is_string($parameters)) {
                        $parameters = array('conditions' => $parameters, 'limit' => 1);
                }
                if (!isset($parameters['limit'])) {
                        $parameters['limit'] = 1;
                }
                return self::find($parameters)->getFirst();
        }

        /**
         * Specialization of find() for the exam model.
         * 
         * This function provides checked access for queries against the exam
         * model. If primary role is unset, user is not authenticated or if
         * accessed using a global role (teacher, admin, trusted or custom),
         * then the behavior is the same as calling parent::find().
         * 
         * @param array $parameters The query parameters.
         * @return mixed
         * @uses Model::find()
         * 
         * @see http://docs.phalconphp.com/en/latest/reference/models.html#finding-records
         * @see http://docs.phalconphp.com/en/latest/api/Phalcon_Mvc_Model_Query_Builder.html
         */
        public static function find($parameters = null)
        {
                $dependencyInjector = PhalconDI::getDefault();

                $user = $dependencyInjector->get('user');
                $role = $user->getPrimaryRole();

                // 
                // Wrap string search in array:
                // 
                if (is_string($parameters)) {
                        $parameters = array($parameters);
                }

                // 
                // Don't accept access to other models:
                // 
                if (isset($parameters['models'])) {
                        unset($parameters['models']);
                }

                // 
                // Group by exam by default:
                // 
                if (!isset($parameters['group'])) {
                        $parameters['group'] = self::getRelation('exam') . '.id';
                }

                // 
                // Use parent find() if user is not authenticated:
                // 
                if ($user->getUser() == null) {
                        return parent::find($parameters);
                }

                // 
                // Use parent find() if primary role is unset or if accessed 
                // using global role (these are not tied to any exam).
                // 
                if ($user->hasPrimaryRole() == false || Roles::isGlobal($role)) {
                        return parent::find($parameters);
                }

                // 
                // Qualify bind and order parameters:
                // 
                $parameters = self::getParameters(self::getRelation('exam'), $parameters);

                // 
                // Create the builder using supplied options (conditions,
                // order, limit, ...):
                // 
                $builder = new Builder($parameters);
                $builder->from(self::getRelation('exam'));

                if ($role == Roles::CORRECTOR) {
                        $builder
                            ->from(self::getRelation('exam'))
                            ->join(self::getRelation('question'), self::getRelation('exam', 'id', 'exam_id', 'question'))
                            ->join(self::getRelation('corrector'), self::getRelation('question', 'id', 'question_id', 'corrector'))
                            ->andWhere(sprintf("%s.user = '%s'", self::getRelation('corrector'), $user->getPrincipalName()));
                } elseif ($role == Roles::CREATOR) {
                        $builder
                            ->from(self::getRelation('exam'))
                            ->andWhere(sprintf("%s.creator = '%s'", self::getRelation('exam'), $user->getPrincipalName()));
                } else {
                        $builder
                            ->from(self::getRelation('exam'))
                            ->join(self::getRelation($role), self::getRelation('exam', 'id', 'exam_id'))
                            ->andWhere(sprintf("%s.user = '%s'", self::getRelation($role), $user->getPrincipalName()));
                }

                if (isset($parameters['bind'])) {
                        return $builder->getQuery()->execute($parameters['bind']);
                } else {
                        return $builder->getQuery()->execute();
                }
        }

        /**
         * Returns PHQL prepared with joins against role models. 
         * 
         * When used in a query, it will ensure that only exams where caller 
         * has the primary role is returned. Notice that the joins includes
         * a WHERE clause.
         * 
         * <code>
         * $result = $this->modelsManager->executeQuery(
         *      "SELECT Exam.* FROM " . Exam::getRelations() . " AND Exam.name LIKE '%test%'"
         * );
         * </code>
         * 
         * @return string
         * @see getQuery
         */
        private static function getRelations()
        {
                $dependencyInjector = PhalconDI::getDefault();

                $user = $dependencyInjector->get('user');
                $role = $user->getPrimaryRole();

                $builder = new Builder();

                if ($user->hasPrimaryRole() == false || Roles::isGlobal($role)) {
                        $builder
                            ->addFrom(self::getRelation('exam'), 'Exam');
                } elseif ($role == Roles::CORRECTOR) {
                        $builder
                            ->addFrom(self::getRelation('exam'), 'Exam')
                            ->join(self::getRelation('question'), 'Exam.id = Question.exam_id', 'Question')
                            ->join(self::getRelation('corrector'), 'Question.id = Corrector.question_id', 'Corrector')
                            ->andWhere(sprintf("Corrector.user = '%s'", $user->getPrincipalName()));
                } elseif ($role == Roles::CREATOR) {
                        $builder
                            ->addFrom(self::getRelation('exam'), 'Exam')
                            ->andWhere(sprintf("Exam.creator = '%s'", $user->getPrincipalName()));
                } else {
                        $builder
                            ->addFrom(self::getRelation('exam'), 'Exam')
                            ->join(self::getRelation($role), "$role.exam_id = Exam.id", $role)
                            ->andWhere(sprintf("%s.user = '%s'", $role, $user->getPrincipalName()));
                }

                $query = $builder->getPhql();
                return substr($query, strpos($query, "FROM") + 5);
        }

        /**
         * Get joined PHQL query.
         * 
         * <code>
         * // 
         * // Simple queries:
         * // 
         * $query = "SELECT Exam.* FROM Exam";
         * $query = "SELECT Exam.* FROM Exam LIMIT 1";
         * $query = "SELECT Exam.* FROM Exam LIMIT 3, OFFSET 6";
         * $query = "SELECT Exam.* FROM Exam WHERE Exam.name LIKE '%test%'";
         * $query = "SELECT Exam.* FROM Exam WHERE Exam.id IN (3,5,14)";
         * $query = "SELECT Exam.* FROM Exam WHERE Exam.id = 10 AND Exam.name = 'Name'";
         * 
         * $result = $this->modelsManager->executeQuery(
         *      Exam::getQuery($query)
         * );
         * </code>
         * 
         * <code>
         * // 
         * // Using bind parameters:
         * // 
         * $query = "SELECT Exam.* FROM Exam WHERE Exam.id = ?0 AND Exam.name = ?1";
         * $result = $this->modelsManager->executeQuery(
         *      Exam::getQuery($query), array(10, 'Name')
         * );
         * 
         * $query = "SELECT Exam.* FROM Exam WHERE Exam.id = :id: AND Exam.name = :name:";
         * $result = $this->modelsManager->executeQuery(
         *      Exam::getQuery($query), array('id' => 10, 'name' => 'Name')
         * );
         * </code>
         * 
         * Implicit joined models can be part of the where clause. This example
         * shows this when primary role is set to student:
         * <code>
         * $query = "SELECT Exam.* FROM Exam WHERE Student.user LIKE '%@example.com'";
         * 
         * $result = $this->modelsManager->executeQuery(
         *      Exam::getQuery($query)
         * );
         * </code>
         * 
         * This function can also be used with the query builder from model manager:
         * <code>
         * $query = $this->modelsManager->createQuery()
         *      ->from("Exam")
         *      ->where("published = 'Y'")
         *      ->orderBy('starttime')
         *      ->getPhql();
         * 
         * $result = $this->modelsManager->executeQuery(
         *      Exam::getQuery($query)
         * );
         * </code>
         * 
         * @param string $query The query string.
         * @return string
         */
        public static function getQuery($query)
        {
                $relations = self::getRelations();

                if (strpos($query, " Exam ") !== false) {
                        list($qs, $qe) = explode(" Exam ", $query . ' ');
                }
                if (strpos($query, "[Exam]") !== false) {
                        list($qs, $qe) = explode(" [Exam] ", $query . ' ');
                }

                $qs = trim($qs);
                $qe = trim($qe);

                if (strlen($qe) == 0) {
                        $result = sprintf("%s %s", $qs, $relations);
                } elseif (strpos($qe, "WHERE") !== false && strpos($relations, "WHERE") !== false) {
                        $result = sprintf("%s %s AND %s", $qs, $relations, substr($qe, 6));
                } else {
                        $result = sprintf("%s %s %s", $qs, $relations, $qe);
                }

                return $result;
        }

        /**
         * Modify start and/or end time.
         * 
         * Dynamic patch the starttime and/or endtime "on the fly" if called
         * having the student role. The datetime is taken from the student
         * table allowing to have per student (individual) start/end different
         * than the exam default.
         * 
         * @throws Exception
         */
        private function setStudentDatetime()
        {
                // 
                // Adjust start/endtime per student:
                // 
                $user = $this->getDI()->get('user');
                $role = $user->setPrimaryRole(Roles::SYSTEM);

                if ($role == Roles::STUDENT) {
                        if (($student = Student::findFirst(array(
                                    'conditions' => 'user = :user: AND exam_id = :exam:',
                                    'bind'       => array(
                                            'user' => $user->getprincipalName(),
                                            'exam' => $this->id
                                )))) == false) {
                                $user->setPrimaryRole($role);
                                throw new Exception("Failed lookup student by behavior");
                        }

                        if (isset($student->starttime)) {
                                $this->starttime = $student->starttime;
                        }
                        if (isset($student->endtime)) {
                                $this->endtime = $student->endtime;
                        }
                }
                $user->setPrimaryRole($role);
        }

        /**
         * Set organization data.
         */
        private function setOrganization()
        {
                if (!isset($this->orgunit)) {
                        if (($service = $this->getDI()->get('user'))) {
                                $this->orgunit = $service->departments->name;
                        }
                }
                if (!isset($this->orgunit)) {
                        if (($service = $this->getDI()->get('config'))) {
                                $this->orgunit = $service->user->orgname;
                        }
                }
                if (!isset($this->code)) {
                        if (($service = $this->getDI()->get('user'))) {
                                $this->code = $service->departments->code;
                        }
                }

                $parts = explode(";", $this->orgunit);
                $parts = array_pad($parts, 3, null);

                if (!isset($this->division) && isset($parts[0])) {
                        $this->division = trim($parts[0]);
                }
                if (!isset($this->department) && isset($parts[1])) {
                        $this->department = trim($parts[1]);
                }
                if (!isset($this->group) && isset($parts[2])) {
                        $this->group = trim($parts[2]);
                }
        }

}
