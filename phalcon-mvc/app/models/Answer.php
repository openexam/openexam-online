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
// File:    Answer.php
// Created: 2014-02-24 07:04:58
// 
// Author:  Anders Lövgren (Computing Department at BMC, Uppsala University)
// 

namespace OpenExam\Models;

use OpenExam\Library\Model\Behavior\Transform\FilterText;
use OpenExam\Library\Model\Behavior\Transform\Purifier;
use OpenExam\Library\Model\Guard\Question as QuestionModelGuard;
use OpenExam\Library\Model\Guard\Student as StudentModelGuard;
use OpenExam\Library\Model\Validation\Newer as NewerValidator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * The answer model.
 * 
 * @property File[] $files The files associated with this answer.
 * @property Result $result The related result.
 * @property Question $question The related question.
 * @property Student $student The related student.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Answer extends ModelBase
{

        /**
         * Guard against problematic methods use.
         */
        use QuestionModelGuard;
        use StudentModelGuard;

        /**
         * This object ID.
         * @var integer
         */
        public $id;
        /**
         * The question ID.
         * @var integer
         */
        public $question_id;
        /**
         * The student ID.
         * @var integer
         */
        public $student_id;
        /**
         * Is already answered?
         * @var bool
         */
        public $answered;
        /**
         *
         * @var string
         */
        public $answer;
        /**
         *
         * @var string
         */
        public $comment;
        /**
         * The modified datetime.
         * @var string 
         */
        public $modified;

        public function initialize()
        {
                parent::initialize();

                $this->hasMany("id", "OpenExam\Models\File", "answer_id", array(
                        'alias' => 'files'
                ));
                $this->hasOne('id', 'OpenExam\Models\Result', 'answer_id', array(
                        'alias' => 'result'
                ));
                $this->belongsTo('question_id', 'OpenExam\Models\Question', 'id', array(
                        'foreignKey' => true,
                        'alias'      => 'question'
                ));
                $this->belongsTo('student_id', 'OpenExam\Models\Student', 'id', array(
                        'foreignKey' => true,
                        'alias'      => 'student'
                ));

                $this->addBehavior(new Purifier(array(
                        'beforeSave' => array(
                                'config' => $this->getDI()->get('config')->get('purify')
                        )
                    )
                ));
        }

        /**
         * Get source table name.
         * @return string
         */
        public function getSource()
        {
                return 'answers';
        }

        /**
         * Get table column map.
         * @return array
         */
        public function columnMap()
        {
                return array(
                        'id'          => 'id',
                        'question_id' => 'question_id',
                        'student_id'  => 'student_id',
                        'answered'    => 'answered',
                        'answer'      => 'answer',
                        'comment'     => 'comment',
                        'modified'    => 'modified'
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
                    array('question_id', 'student_id'), new Uniqueness(
                    array(
                        'message' => "Student answer has already been inserted"
                    )
                ));
                $validator->add(
                    "modified", new NewerValidator(
                    array(
                        "message" => "The modified datetime is older than current",
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
                if (!isset($this->answered)) {
                        $this->answered = false;
                }
        }

        /**
         * Called before model is saved.
         */
        public function beforeSave()
        {
                $this->answered = $this->answered ? 'Y' : 'N';
        }

        /**
         * Called after model is saved.
         */
        public function afterSave()
        {
                $this->answered = $this->answered == 'Y';
        }

        /**
         * Called after the model was read.
         */
        public function afterFetch()
        {
                $this->answered = $this->answered == 'Y';
                parent::afterFetch();
        }

        /**
         * Updates a model instance.
         * 
         * This method overides parent method to convert values for boolean
         * properties to real boolean values. It also checks whether any data
         * has changes and by-passes update otherwise.
         * 
         * @param mixed $data
         * @param mixed $whiteList
         * @return boolean
         */
        public function update($data = null, $whiteList = null)
        {
                // 
                // Convert AJAX property values to bool:
                // 
                if (!is_bool($this->answered)) {
                        if (intval($this->answered) != 0) {
                                $this->answered = true;
                        } else {
                                $this->answered = false;
                        }
                }

                // 
                // Assign data if requested:
                // 
                if (isset($data)) {
                        $this->assign($data, null, $whiteList);
                }

                // 
                // Call parent update() on modified:
                // 
                if (!$this->hasSnapshotData()) {
                        return parent::update();
                }
                if ($this->hasChanged()) {
                        return parent::update();
                }

                // 
                // No update is a success:
                // 
                return true;
        }

}
