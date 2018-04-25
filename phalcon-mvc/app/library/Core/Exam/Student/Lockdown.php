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
// File:    Lockdown.php
// Created: 2014-12-17 03:22:09
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Core\Exam\Student;

use Exception;
use OpenExam\Library\Core\Error;
use OpenExam\Library\Model\Exception as ModelException;
use OpenExam\Library\Security\Exception as SecurityException;
use OpenExam\Models\Computer as ComputerModel;
use OpenExam\Models\Exam;
use OpenExam\Models\Lock;
use OpenExam\Models\Student;
use Phalcon\Mvc\User\Component;

/**
 * Handle client lockdown on student exam access.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Lockdown extends Component
{

        /**
         * @var Exam 
         */
        private $_exam;
        /**
         * @var Student 
         */
        private $_student;
        /**
         * @var string 
         */
        private $_remote;
        /**
         * The exam access approve object.
         * @var Approve 
         */
        private $_approve;

        /**
         * Constructor.
         * @param Exam $exam The current exam.
         * @param Student $student The current student.
         */
        public function __construct($exam, $student)
        {
                $this->_approve = new Approve($exam, $student);

                $this->_exam = $exam;
                $this->_student = $student;

                $this->_remote = $this->request->getClientAddress(true);
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                unset($this->_approve);
                unset($this->_exam);
                unset($this->_remote);
                unset($this->_student);
        }

        /**
         * Check if access is accepted for student. 
         * 
         * This function checks exam state, address restrictions and computer
         * locks. A computer lock is always created, but remains pending for
         * computers not within the list of allowed addresses. The invigilator
         * can then approve the pending lock to grant access.
         * 
         * Returns true if current student should have access to this exam,
         * otherwise return false.
         * 
         * @return int One of the Access::OPEN_XXX constants.
         * @throws \Exception
         */
        public function accepted()
        {
                try {
                        // 
                        // Check if exam is finished or closed:
                        // 
                        if ($this->_approve->isFinished() == true) {
                                $this->logger->access->debug(sprintf("Denied access to exam for %s from %s (is not accessable)", $this->_student->user, $this->_remote));
                                return Access::OPEN_DENIED;
                        }

                        // 
                        // Check if exam lock has been acquired:
                        // 
                        if ($this->_approve->isLocked() == true) {
                                $this->logger->access->debug(sprintf("Verified access to exam for %s from %s (id=%d)", $this->_student->user, $this->_remote, $this->_exam->id));
                                return Access::OPEN_APPROVED;
                        }

                        // 
                        // Check if remote location is accepted:
                        // 
                        if ($this->_approve->isAllowed() == false) {
                                $this->logger->access->notice(sprintf("Pending exam approval for %s from %s (blocked by ip-address)", $this->_student->user, $this->_remote));
                                $this->setLock(Lock::STATUS_PENDING);
                                return Access::OPEN_PENDING;
                        }

                        // 
                        // Check client side lockdown:
                        // 
                        if ($this->_approve->hasSecureClient($this->getComputer()) == false) {
                                $this->logger->access->notice(sprintf("Denied access to exam for %s from %s (locking failed)", $this->_student->user, $this->_remote));
                                return Access::OPEN_DENIED;
                        }

                        // 
                        // Connection should be approved:
                        // 
                        $this->setLock(Lock::STATUS_APPROVED);
                        $this->logger->access->debug(sprintf("Granted access to exam to %s from %s (id=%d)", $this->_student->user, $this->_remote, $this->_exam->id));

                        return Access::OPEN_SETUP;
                } catch (ModelException $exception) {
                        $this->logger->system->error(sprintf("Failed update database (%s)", $exception->getMessage()));
                        throw new Exception("Failed update database", Error::SERVICE_UNAVAILABLE, $exception);
                } catch (SecurityException $exception) {
                        $this->logger->access->notice(sprintf("Denied access to exam for %s from %s (%s)", $this->_student->user, $this->_remote, $exception->getMessage()));
                        throw $exception;
                }
        }

        /**
         * Get remote computer. 
         * 
         * The computer object will be created if missing and insert into
         * correct room, possibly creating that too. The computer is looked 
         * up from IP-address of request (including proxied for setting if
         * being used). 
         * 
         * @return ComputerModel
         * @throws ModelException
         */
        private function getComputer()
        {
                $computer = new Computer($this->_remote);
                return $computer->getComputer();
        }

        /**
         * Create or update computer lock with status $status.
         * @param string $status The lock status.
         */
        private function setLock($status)
        {
                $computer = $this->getComputer();

                if (($lock = $this->_approve->getLock()) === false) {
                        $lock = new Lock();
                        $lock->exam_id = $this->_exam->id;
                        $lock->student_id = $this->_student->id;
                        $lock->computer_id = $computer->id;
                        $lock->status = $status;
                } else {
                        $lock->computer_id = $computer->id;
                        $lock->status = $status;
                }

                if ($lock->save() == true) {
                        $this->logger->access->debug(sprintf("Wrote %s exam access lock for %s from %s (id=%d)", $status, $this->_student->user, $this->_remote, $this->_exam->id));
                } else {
                        throw new ModelException($lock->getMessages()[0]);
                }
        }

}
