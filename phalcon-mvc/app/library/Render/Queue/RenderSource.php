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
// File:    RenderSource.php
// Created: 2017-12-08 05:39:09
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Render\Queue;

use OpenExam\Models\Render;
use OpenExam\Models\Student;
use Phalcon\Mvc\User\Component;

/**
 * The render source handler.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class RenderSource extends Component
{

        /**
         * The render model.
         * @var Render 
         */
        private $_render;
        /**
         * The student model.
         * @var Student 
         */
        private $_student;

        /**
         * Constructor.
         * 
         * @param Render $render The render model.
         * @param Student $student Optional student model.
         */
        public function __construct($render, $student = null)
        {
                if (!isset($render->type)) {
                        throw new RenderException("The render type is missing");
                }

                switch ($render->type) {
                        case Render::TYPE_ARCHIVE:
                                if (!isset($render->exam_id)) {
                                        throw new RenderException("The render exam_id is missing");
                                }
                                break;
                        case Render::TYPE_EXPORT:
                        case Render::TYPE_EXTERN:
                                if (!isset($render->url)) {
                                        throw new RenderException("The render url is missing");
                                }
                                break;
                        case Render::TYPE_RESULT:
                                if (!isset($render->exam_id)) {
                                        throw new RenderException("The render exam_id is missing");
                                }
                                if (!isset($student->id)) {
                                        throw new RenderException("The student id is missing");
                                }
                                if (!isset($student->user)) {
                                        throw new RenderException("The student user is missing");
                                }
                                break;
                }

                $this->_render = $render;
                $this->_student = $student;
        }

        /**
         * Set source URL.
         */
        public function setUrl()
        {
                $render = $this->_render;

                switch ($render->type) {
                        case Render::TYPE_ARCHIVE:
                                $render->url = $this->getUrl(
                                    Render::TYPE_ARCHIVE, sprintf("exam/archive/%d", $render->exam_id)
                                );
                                break;
                        case Render::TYPE_RESULT:
                                $render->url = $this->getUrl(
                                    Render::TYPE_RESULT, sprintf("result/%d/view/%d", $render->exam_id, $this->_student->id)
                                );
                                break;
                }
        }

        /**
         * Get result URL.
         * 
         * @param string $type The render type.
         * @param string $path The sub path.
         * @return string
         */
        private function getUrl($type, $path)
        {
                $server = $this->config->render->server;
                $expand = $this->url->get($path);
                $source = sprintf("http://%s%s?token=%s&user=%s&render=%s", $server, $expand, $this->getToken(), $this->_student->user, $type);
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

}
