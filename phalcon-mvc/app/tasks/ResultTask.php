<?php

// 
// The source code is copyrighted, with equal shared rights, between the
// authors (see the file AUTHORS) and the OpenExam project, Uppsala University 
// unless otherwise explicit stated elsewhere.
// 
// File:    ResultTask.php
// Created: 2016-04-19 22:00:41
// 
// Author:  Anders Lövgren (QNET/BMC CompDept)
// 

namespace OpenExam\Console\Tasks;

use OpenExam\Library\Core\Result as ResultHandler;
use OpenExam\Models\Exam;

/**
 * Downloadable results task.
 * 
 * Use this task to maintain the directory of downloadable result file. It 
 * can be runned periodical to cleanup expired result files or pre-generate
 * files for newly decoded exams.
 *
 * Sensible defaults could be:
 * 
 * o) Remove files older than 4 weeks.
 * o) Pre-generate for last 2 weeks.
 * 
 * @author Anders Lövgren (QNET/BMC CompDept)
 */
class ResultTask extends MainTask implements TaskInterface
{

        /**
         * Runtime options
         * @var array 
         */
        private $options;
        /**
         * The result directory.
         * @var string 
         */
        private $resdir;

        public function __construct()
        {
                $this->resdir = sprintf("%s/results", $this->config->application->cacheDir);
        }

        public function helpAction()
        {
                parent::showUsage(self::getUsage());
        }

        public static function getUsage()
        {
                return array(
                        'header'   => 'Maintenance downloadable results.',
                        'action'   => '--result',
                        'usage'    => array(
                                '--create --days=num [--force]',
                                '--delete --days=num'
                        ),
                        'options'  => array(
                                '--create'   => 'Generate result files.',
                                '--delete'   => 'Remove generated result files.',
                                '--days=num' => 'Older/newer than num days.',
                                '--generate' => 'Alias for --create.',
                                '--remove'   => 'Alias for --delete',
                                '--force'    => 'Force generate files even if existing.',
                                '--verbose'  => 'Be more verbose.'
                        ),
                        'examples' => array(
                                array(
                                        'descr'   => 'Generate result files for decoded exams newer than two weeks',
                                        'command' => '--create --days=14'
                                ),
                                array(
                                        'descr'   => 'Remove result files older than one month',
                                        'command' => '--delete --days=31'
                                )
                        )
                );
        }

        /**
         * Create result files action.
         * @param array $params
         */
        public function createAction($params = array())
        {
                $this->setOptions($params, 'create');

                if ($this->options['verbose']) {
                        $this->flash->notice("Starting result file generation");
                }

                $exams = $this->getExams();

                foreach ($exams as $exam) {
                        if ($this->options['verbose']) {
                                $this->flash->notice(sprintf("Processing exam %d", $exam->id));
                        }
                        $result = new ResultHandler($exam);
                        $result->setForced($this->options['force']);

                        foreach ($exam->students as $student) {
                                if ($this->options['verbose']) {
                                        $this->flash->notice(sprintf("  Generating result for student %d (%s)", $student->id, $student->code));
                                }
                                $result->createFile($student);
                        }
                        if ($this->options['verbose']) {
                                $this->flash->notice(sprintf("  Creating zip-file for exam %d", $exam->id));
                        }
                        $result->createArchive();
                }

                if ($this->options['verbose']) {
                        $this->flash->success("Finished result file generation");
                }
        }

        public function generateAction($params = array())
        {
                $this->createAction($params);
        }

        /**
         * Remove generated result files action.
         * @param array $params
         */
        public function deleteAction($params = array())
        {
                $this->setOptions($params, 'delete');

                if ($this->options['verbose']) {
                        $this->flash->notice("Starting result file cleanup");
                }

                $exams = $this->getExams();
                $remove = array();

                if (!file_exists($this->resdir)) {
                        $this->flash->error("The result directory is missing");
                        return false;
                }

                if (($handle = opendir($this->resdir)) !== false) {
                        while (($name = readdir($handle))) {
                                if ($name == "." || $name == "..") {
                                        continue;
                                } else {
                                        $path = sprintf("%s/%s", $this->resdir, $name);
                                }
                                if (is_dir($path)) {
                                        $found = $exams->filter(function($exam) use($name) {
                                                if ($exam->id == intval($name)) {
                                                        return $exam;
                                                }
                                        });
                                        if (count($found) == 0) {
                                                if (($exam = Exam::findFirst(intval($name)))) {
                                                        $remove[] = $exam;
                                                } elseif ($this->options['force']) {
                                                        $this->deleteDirectory($path);
                                                        $this->flash->notice("  Deleted result directory $name (forced)");
                                                } else {
                                                        $this->flash->warning("  Ignoring result directory $name without matching model (--force to delete).");
                                                }
                                        }
                                }
                        }
                        closedir($handle);
                } else {
                        throw new Exception("Failed open cache directory handle.");
                }

                foreach ($remove as $exam) {
                        $result = new ResultHandler($exam);
                        if ($this->options['verbose']) {
                                $this->flash->notice(sprintf("  Processing exam %d", $exam->id));
                        }
                        foreach ($exam->students as $student) {
                                if ($this->options['verbose']) {
                                        $this->flash->notice(sprintf("  Removing result for student %d (%s)", $student->id, $student->code));
                                }
                                $result->delete($student);
                        }
                        if ($this->options['verbose']) {
                                $this->flash->notice(sprintf("  Cleanup of exam %d", $exam->id));
                        }
                        $result->clean();
                }

                if ($this->options['verbose']) {
                        $this->flash->success("Finished result file cleanup");
                }
        }

        public function removeAction($params = array())
        {
                $this->deleteAction($params);
        }

        private function deleteDirectory($root)
        {
                if (($handle = opendir($root)) !== false) {
                        while (($name = readdir($handle))) {
                                if ($name == "." || $name == "..") {
                                        continue;
                                }

                                $path = sprintf("%s/%s", $root, $name);
                                if (is_dir($path)) {
                                        $this->deleteDirectory($path);
                                } else {
                                        unlink($path);
                                }
                        }
                        closedir($handle);
                        rmdir($root);
                }
        }

        private function getExams()
        {
                if (($exams = Exam::find(array(
                            'conditions' => "endtime > :date: AND decoded = 'Y'",
                            'bind'       => array('date' => $this->options['time'])
                    ))) == false) {
                        throw new Exception("Failed get exam models.");
                }

                return $exams;
        }

        /**
         * Set options from task action parameters.
         * @param array $params The task action parameters.
         * @param string $action The calling action.
         */
        private function setOptions($params, $action = null)
        {
                // 
                // Default options.
                // 
                $this->options = array('verbose' => false, 'force' => false);

                // 
                // Supported options.
                // 
                $options = array('verbose', 'create', 'delete', 'days', 'remove', 'generate', 'force');
                $current = $action;

                // 
                // Set defaults.
                // 
                foreach ($options as $option) {
                        if (!isset($this->options[$option])) {
                                $this->options[$option] = false;
                        }
                }

                // 
                // Include action in options (for multitarget actions).
                // 
                if (isset($action)) {
                        $this->options[$action] = true;
                }

                // 
                // Scan params for both --key and --key=val options.
                // 
                while (($option = array_shift($params))) {
                        if (in_array($option, $options)) {
                                $this->options[$option] = true;
                                $current = $option;
                        } elseif (in_array($current, $options)) {
                                $this->options[$current] = $option;
                        } else {
                                throw new Exception("Unknown task action/parameters '$option'");
                        }
                }

                if (!$this->options['days']) {
                        throw new Exception("Required option '--days' is missing.");
                } else {
                        $this->options['time'] = strftime(
                            "%Y-%m-%d %H:%M:%S", time() - 24 * 3600 * intval($this->options['days'])
                        );
                }
        }

}