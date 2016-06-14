<?php

// 
// The source code is copyrighted, with equal shared rights, between the
// authors (see the file AUTHORS) and the OpenExam project, Uppsala University 
// unless otherwise explicit stated elsewhere.
// 
// File:    ExamController.php
// Created: 2014-09-18 18:11:50
// 
// Author:  Ahsan Shahzad (MedfarmDoIT)
// 

namespace OpenExam\Controllers\Gui;

use OpenExam\Controllers\GuiController;
use OpenExam\Library\Security\Roles;
use OpenExam\Models\Corrector;
use OpenExam\Models\Exam;
use OpenExam\Models\Question;
use OpenExam\Models\Student;
use OpenExam\Models\Topic;
use Phalcon\Mvc\View;

/**
 * Controller for loading Exam pages
 *
 * @author Ahsan Shahzad (MedfarmDoIT)
 */
class ExamController extends GuiController
{

        /**
         * Exams to list per page on exam/index
         */
        const EXAMS_PER_PAGE = 3;

        /**
         * Home page for exam management to list all exams
         * exam/index
         */
        public function indexAction()
        {
                // initializations
                $loggedIn = $this->user->getPrincipalName();
                $colList = "e.*";

                #------------ Upcoming student exam --------#
                $exams['student-upcoming'] = $this->phql
                    ->executeQuery(
                    "select $colList from OpenExam\Models\Exam e "
                    . "inner join OpenExam\Models\Student s "
                    . "where "
                    . "s.user = :user: and "
                    . "("
                    . "(s.endtime is NULL and e.endtime >= NOW()) or "
                    . "(s.endtime is not NULL  and s.endtime >= NOW()) or"
                    . "(e.starttime is not NULL  and e.endtime is null)"
                    . ") and "
                    . "e.published = 'Y' "
                    . "order by e.starttime desc "
                    , array("user" => $loggedIn)
                );
                $stExamsToday = $exams['student-upcoming']->filter(function($resource) {
                        if (date("Y-m-d", strtotime($resource->starttime)) == date('Y-m-d')) {
                                return $resource;
                        }
                });
                if (count($stExamsToday) == 1) {
                        return $this->response->redirect('exam/' . $stExamsToday[0]->id);
                }

                if (!count($stExamsToday)) {
                        #------------ Finished student exam --------#                
                        $exams['student-finished'] = $this->phql
                            ->executeQuery(
                            "select $colList from OpenExam\Models\Exam e "
                            . "inner join OpenExam\Models\Student s "
                            . "where "
                            . "s.user = :user: and "
                            . "("
                            . "(s.endtime is NULL and e.endtime < NOW()) or "
                            . "(s.endtime is not NULL and s.endtime < NOW())"
                            . ") and "
                            . "e.published = 'Y' "
                            . "order by e.endtime desc "
                            , array("user" => $loggedIn)
                        );
                }

                #------------ Exam creator --------#
                if ($this->user->aquire(array(Roles::TEACHER))) {
                        $exams['creator'] = $this->phql
                            ->executeQuery(
                            "select $colList from OpenExam\Models\Exam e "
                            . "where creator = :user: order by created desc "
                            //.   "limit " . self::EXAMS_PER_PAGE
                            , array("user" => $loggedIn)
                        );
                }

                #------------ Exam contributor --------#
                $exams['contributor'] = $this->phql
                    ->executeQuery(
                    "select $colList from OpenExam\Models\Exam e "
                    . "inner join OpenExam\Models\Contributor c "
                    . "where c.user = :user: order by created desc "
                    //.   "limit " . self::EXAMS_PER_PAGE
                    , array("user" => $loggedIn)
                );

                #------------ Exam invigilator --------#
                $exams['invigilator'] = $this->phql
                    ->executeQuery(
                    "select $colList from OpenExam\Models\Exam e "
                    . "inner join OpenExam\Models\Invigilator i "
                    . "where i.user = :user: order by created desc "
                    //.   "limit " . self::EXAMS_PER_PAGE
                    , array("user" => $loggedIn)
                );

                #------------ Question corrector --------#
                $exams['corrector'] = $this->phql
                    ->executeQuery(
                    "select distinct $colList from OpenExam\Models\Exam e "
                    . "inner join OpenExam\Models\Question q "
                    . "inner join OpenExam\Models\Corrector c on q.id = c.question_id "
                    . "where c.user = :user: order by created desc"
                    //.   "limit " . self::EXAMS_PER_PAGE
                    , array("user" => $loggedIn)
                );

                #------------ Exam decoder --------#
                $exams['decoder'] = $this->phql
                    ->executeQuery(
                    "select $colList from OpenExam\Models\Exam e "
                    . "inner join OpenExam\Models\Decoder d "
                    . "where d.user = :user: order by created desc "
                    //.   "limit " . self::EXAMS_PER_PAGE
                    , array("user" => $loggedIn)
                );

                // pass data to view
                $this->view->setVars(array(
                        'roleBasedExamList' => $exams,
                        'baseRole'          => $baseRole
                ));
        }

        /**
         * Show create view for exam
         * On exam create request, new records are inserted 
         * exam/create
         * 
         * Allowed to Roles: teacher
         */
        public function createAction()
        {
                // create a new exam
                $exam = new Exam();
                $examSaved = $exam->save(array(
                        'name'    => ' ',
                        'descr'   => ' ',
                        'creator' => $this->user->getPrincipalName(),
                        'grades'  => 'U:0&#13;&#10;G:50&#13;&#10;VG:75',
                        'details' => 7
                ));

                if (!$examSaved) {
                        throw new \Exception(
                        sprintf("Failed to initialize exam (%s)", $exam->getMessages()[0])
                        );
                }

                return $this->response->redirect('exam/update/' . $exam->id . '/creator/new-exam');
        }

        /**
         * Update view for exam
         * exam/update/{exam-id}
         * 
         * Allowed to Roles: creator, contributor
         */
        public function updateAction($examId)
        {
                /*                //@ToDO: get roles from access.def
                  //someone will be able to access this action when he is permitted
                  //to be here
                  // allowed roles for a user to be in this action
                  $allowedRoleList = array('admin', 'creator', 'contributor');

                  // get the role because of which this looged in person has got
                  // permissions to be in current controller/action
                  $role = $this->user->aquire($allowedRoleList, $examId, false)[0];
                 */
                // sanitize
                $examId = $this->filter->sanitize($examId, "int");

                // check if role has been passed
                $params = $this->dispatcher->getParams();
                if (isset($params[1]) && in_array($params[1], $this->capabilities->getRoles())) {
                        $this->user->setPrimaryRole($params[1]);
                } else {
                        throw new \Exception("Invalid URL.");
                }

                // fetch data
                $exam = Exam::findFirst($examId);

                // pass data to view
                $this->view->setVars(array(
                        'exam' => $exam
                    //'myRole' => $role,
                ));
        }

        /**
         * Allows exam creator to replicate his exam
         * 
         * exam/replicate/{exam-id}
         * Allowed to Roles: teacher
         */
        public function replicateAction($examId)
        {
                $this->view->disable();

                $loggedIn = $this->user->getPrincipalName();
                $examId = $this->filter->sanitize($examId, "int");

                if ($examId && $exam = Exam::findFirst($examId)) {

                        // only exam creator can replicate an exam
                        if ($exam->creator != $loggedIn)
                                return $this->response->setJsonContent(array("status" => "failed"));

                        // create exam by replicating exam data
                        $newExam = new Exam();
                        $examSaved = $newExam->save(array(
                                "name"    => $exam->name,
                                "descr"   => $exam->descr,
                                //"starttime" => $exam->starttime,
                                //"endtime" => $exam->endtime,
                                "creator" => $exam->creator,
                                "details" => $exam->details,
                                "orgunit" => $exam->orgunit,
                                "grades"  => $exam->grades
                        ));
                        if (!$examSaved)
                                return $this->response->setJsonContent(array("status" => "failed"));

                        // replicate other data if options are provided for replication
                        $replicateOpts = $this->request->getPost('options');
                        if (count($replicateOpts)) {
                                ## replicate topics if selected
                                $topicMap = array();
                                if (in_array('topics', $replicateOpts)) {
                                        $topics = Topic::find('exam_id = ' . $exam->id);
                                        if (is_object($topics) && $topics->count()) {
                                                foreach ($topics as $topic) {
                                                        $newTopic = new Topic();
                                                        $topicSaved = $newTopic->save(array(
                                                                "exam_id"   => $newExam->id,
                                                                "name"      => $topic->name,
                                                                "randomize" => $topic->randomize,
                                                                "grades"    => $topic->grades,
                                                                "depend"    => $topic->depend
                                                        ));

                                                        if (!$topicSaved) {
                                                                $newTopic = Topic::findFirst('exam_id = ' . $newExam->id);
                                                        }
                                                        $topicMap[$newTopic->id] = $topic->id;
                                                }
                                        }
                                }

                                ## replicate questions and correctors if selected
                                if (in_array('questions', $replicateOpts)) {

                                        $questions = Question::find('exam_id = ' . $exam->id);
                                        if (is_object($questions) && $questions->count()) {

                                                foreach ($questions as $quest) {

                                                        // replicate questions
                                                        $newQuest = new Question();
                                                        $newQuest->save(array(
                                                                "exam_id"  => $newExam->id,
                                                                "topic_id" => array_search($quest->topic_id, $topicMap),
                                                                "score"    => $quest->score,
                                                                "name"     => $quest->name,
                                                                "quest"    => $quest->quest,
                                                                "status"   => $quest->status,
                                                                "comment"  => $quest->comment,
                                                                "grades"   => $quest->grades
                                                        ));

                                                        //replicate question correctors
                                                        $correctors = $quest->getCorrectors();
                                                        if (is_object($correctors) && $correctors->count()) {
                                                                foreach ($correctors as $corrector) {

                                                                        $newCorrector = new Corrector();
                                                                        $newCorrector->save(array(
                                                                                "question_id" => $newQuest->id,
                                                                                "user"        => $corrector->user
                                                                        ));
                                                                }
                                                        }
                                                }
                                        }
                                }

                                ## replicate roles if selected
                                if (in_array('roles', $replicateOpts)) {

                                        // roles to be replicated
                                        $roles = array(
                                                'contributors' => '\OpenExam\Models\Contributor',
                                                'decoders'     => '\OpenExam\Models\Decoder',
                                                'invigilators' => '\OpenExam\Models\Invigilator',
                                        );

                                        foreach ($roles as $role => $roleClass) {

                                                // replicate contributors
                                                $roleUsers = $exam->$role;
                                                if (is_object($roleUsers) && $roleUsers->count()) {
                                                        foreach ($roleUsers as $roleUser) {

                                                                // skip creator to be added 
                                                                // (exam model insert creator for all roles)
                                                                if ($roleUser->user == $loggedIn) {
                                                                        continue;
                                                                }

                                                                $newRoleUser = new $roleClass();
                                                                $newRoleUser->save(array(
                                                                        "exam_id" => $newExam->id,
                                                                        "user"    => $roleUser->user
                                                                ));
                                                        }
                                                }
                                        }
                                }
                        }

                        $this->session->set('draft-exam-id', $newExam->id);
                        return $this->response->setJsonContent(array("status" => "success", "exam_id" => $newExam->id));
                }

                return $this->response->setJsonContent(array("status" => "failed"));
        }

        /**
         * Shows exam instructions for student and for test exam
         * exam/{exam_id}
         * 
         * Allowed to Roles: student
         */
        public function instructionAction($examId)
        {
                $loggedIn = $this->user->getPrincipalName();

                // sanitize
                $examId = $this->filter->sanitize($this->dispatcher->getParam("examId"), "int");

                // fetch exam data if it has not been finished yet
                $exam = Exam::findFirst($examId);
                if (!$exam) {
                        return $this->response->redirect('exam/index');
                }

                if ($exam->creator == $loggedIn) {
                        $testMode = TRUE;
                } else {

                        ## find student id of this logged in person for this exam
                        $student = Student::findFirst("user = '" . $loggedIn . "' and exam_id = " . $examId);
                        if (!$student) {
                                throw new \Exception("You are not authorized to access this exam");
                        }

                        $examStartsAt = !is_null($student->starttime) ? $student->starttime : $exam->starttime;
                        $examEndsAt = !is_null($student->endtime) ? $student->endtime : $exam->endtime;

                        ## load exam with time checking
                        if (!is_null($examEndsAt) && strtotime($examEndsAt) < strtotime("now")) {
                                return $this->response->redirect('exam/index');
                        }
                }

                $this->view->setVars(array(
                        "exam"    => $exam,
                        "student" => $student
                ));

                // $this->view->setVar("tr", new Translate('admin'));
                $this->view->setLayout('thin-layout');
        }

        /**
         * Load popup for student management under the exam
         * exam/students
         * 
         * Allowed to Roles: invigilator
         */
        public function studentsAction()
        {
                $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

                // sanitize

                $examId = $this->filter->sanitize($this->request->getPost("exam_id"), "int");
                if ($examId) {

                        // load exam data if exam time has not been finished
                        $exam = Exam::findFirst("id = " . $examId . " and (endtime IS NULL or endtime > NOW())");
                        if (!$exam) {
                                throw new \Exception("Sorry! "
                                . "Exam time has been finished. <br>"
                                . "It is no more possible to manage student's data.");
                        }
                } else {
                        throw new \Exception("Unable to load student list for this exam");
                }

                $this->view->setVar("exam", $exam);
                $this->view->setVar("domains", $this->catalog->getDomains());
        }

        /**
         * Load popup for exam settings
         * exam/settings
         * 
         * Allowed to Roles: creator
         */
        public function settingsAction()
        {
                $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

                // sanitize
                $examId = $this->filter->sanitize($this->request->getPost("exam_id"), "int");
                if ($examId) {

                        // load exam data if exam time has not been finished
                        $exam = Exam::findFirst($examId);
                        if (!$exam) {
                                throw new \Exception("Unable to load exam settings.");
                        }
                }

                $this->view->setVar("exam", $exam);
        }

        /**
         * Load popup for exam security
         * exam/security
         * 
         * Allowed to Roles: creator
         */
        public function securityAction()
        {
                $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

                if (($examId = $this->request->getPost("exam_id", "int")) == null) {
                        throw new \Exception("Missing exam ID");
                }
                if (($exam = Exam::findFirst($examId)) == false) {
                        throw new \Exception("Unable to load exam settings.");
                }

                $this->view->setVar("active", $this->location->getActive($examId));
                $this->view->setVar("system", $this->location->getSystem());
                $this->view->setVar("recent", $this->location->getRecent());
                $this->view->setVar("exam", $exam);
        }

        /**
         * Action for pending exam access.
         */
        public function pendingAction()
        {
                $exam = $this->dispatcher->getParam('exam');
                $this->view->setVar('exam', $exam);
                $this->view->setVar('icon', $this->url->get('/img/clock.png'));
                $this->view->setVar('retry', $this->url->get('/exam/' . $exam->id . '/question/1'));
        }

}
