<?php

/*
 * Copyright (C) 2017-2018 The OpenExam Project
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
// File:    Archive.php
// Created: 2017-09-19 04:48:51
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Core\Exam;

use DateTime;
use OpenExam\Library\Render\Renderer;
use OpenExam\Models\Exam;
use Phalcon\Mvc\User\Component;
use RuntimeException;

/**
 * Exam archive class.
 * 
 * Use this class for create exam archives. Currently its only use case is for
 * rendering exams to PDF to be used for backup/archive or paper exams.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Archive extends Component
{

        /**
         * Smallest accepted PDF file size.
         */
        const MIN_FILE_SIZE = 50000;

        /**
         * The exam model.
         * @var Exam 
         */
        private $_exam;
        /**
         * The target directory.
         * @var string 
         */
        private $_path;
        /**
         * The target file.
         * @var string 
         */
        private $_file;
        /**
         * The download name.
         * @var string 
         */
        private $_name;
        /**
         * Get HTML from this URL.
         * @var string 
         */
        private $_from;

        /**
         * Constructor.
         * 
         * @param Exam $exam The exam model.
         * @param boolean $correct Display correct answers.
         */
        public function __construct($exam, $correct = false)
        {
                $this->_exam = $exam;

                $this->_path = sprintf("%s/archive", $this->config->application->cacheDir);
                $this->_file = sprintf("%s/%d.pdf", $this->_path, $this->_exam->id);
                $this->_name = sprintf("%s.pdf", $this->_exam->name);

                $this->_from = $this->getSource($this->getToken(), $correct);

                $this->prepare();
        }

        /**
         * Check that file exist.
         * @return boolean
         */
        public function exists()
        {
                return file_exists($this->_file);
        }

        /**
         * Create PDF file.
         */
        public function create()
        {
                $this->render();
        }

        /**
         * Delete PDF file.
         * @throws RuntimeException
         */
        public function delete()
        {
                if (!unlink($this->_file)) {
                        throw new RuntimeException("Failed unlink $this->_file");
                }
        }

        /**
         * Send PDF file.
         */
        public function send()
        {
                // 
                // Flush output buffering to get chunked mode:
                // 
                while (ob_get_level()) {
                        ob_end_clean();
                        ob_end_flush();
                }

                // 
                // Required by some browsers for actually caching:
                // 
                $expires = new DateTime();
                $expires->modify("+2 months");

                // 
                // Disable view:
                // 
                $this->view->disable();
                $this->view->finish();

                // 
                // Set headers for cache and chunked transfer mode:
                // 
                $this->response->setContentType('application/pdf', 'UTF-8');
                $this->response->setHeader("Cache-Control", "max-age=86400");
                $this->response->setHeader("Pragma", "public");
                $this->response->setHeader("Content-Disposition", sprintf('filename="%s"', $this->_name));
                $this->response->setExpires($expires);

                // 
                // Send response headers and file:
                // 
                $this->response->send();
                readfile($this->_file);
        }

        /**
         * Verify that PDF file is sane.
         * @return boolean
         */
        public function verify()
        {
                if (!file_exists($this->_file)) {
                        return false;
                }
                if (filesize($this->_file) < self::MIN_FILE_SIZE) {
                        return false;
                }
                if (filemtime($this->_file) < strtotime($this->_exam->updated)) {
                        return false;
                }

                return true;
        }

        /**
         * Check that caller can access archive.
         * @return boolean
         */
        public function accessable()
        {
                if ($this->user->roles->isAdmin()) {
                        return true;
                }
                if ($this->user->getPrincipalName() == $this->_exam->creator) {
                        return true;
                }
                return false;
        }

        /**
         * Render archive source to PDF.
         */
        private function render()
        {
                $config = array(array('page' => $this->_from));

                $render = $this->render->getRender(Renderer::FORMAT_PDF);
                $render->save($this->_file, $config);
        }

        /**
         * Create target directory if missing.
         * @throws RuntimeException
         */
        private function prepare()
        {
                if (file_exists($this->_path) && is_writable($this->_path)) {
                        return;
                }
                if (file_exists($this->_path) && is_readable($this->_path)) {
                        throw new RuntimeException("Target directory for exam archives is read-only");
                }
                if (!mkdir($this->_path, 0750, true)) {
                        throw new RuntimeException("Failed create target directory for exam archives");
                }
        }

        /**
         * Get source URL.
         * 
         * @param string $token The render token.
         * @param boolean $correct Display correct answers.
         * @return string
         */
        private function getSource($token, $correct = false)
        {
                $expand = $this->url->get(sprintf("exam/archive/%d", $this->_exam->id));
                $source = sprintf("http://%s%s?token=%s&user=%s&render=archive&correct=%s", $this->config->render->server, $expand, $token, $this->_exam->creator, $correct ? 'true' : 'false');
                return $source;
        }

        /**
         * Get authentication token for system local service
         * @return string
         */
        private function getToken()
        {
                if (file_exists($this->config->render->token)) {
                        return file_get_contents($this->config->render->token);
                } else {
                        return $this->config->render->token;
                }
        }

        /**
         * Get associated archive file.
         * @return string
         */
        public function getFilename()
        {
                return $this->_file;
        }

}
