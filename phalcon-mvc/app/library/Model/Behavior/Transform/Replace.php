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
// File:    Replace.php
// Created: 2017-09-20 13:25:42
// 
// Author:  Anders Lövgren (Computing Department at BMC, Uppsala University)
// 

namespace OpenExam\Library\Model\Behavior\Transform;

use OpenExam\Library\Model\Behavior\ModelBehavior;
use OpenExam\Library\Model\Exception;
use Phalcon\Mvc\ModelInterface;

/**
 * String replace on field.
 * 
 * This behavior performs a search and replace of substring on requested 
 * model property.
 *
 * @author Anders Lövgren (Computing Department at BMC, Uppsala University)
 */
class Replace extends ModelBehavior
{

        public function notify($type, ModelInterface $model)
        {
                if (($options = $this->getOptions($type))) {

                        if (!isset($options['search'])) {
                                throw new Exception("Missing required behavior option search");
                        }
                        if (!isset($options['replace'])) {
                                throw new Exception("Missing required behavior option replace");
                        }
                        if (!isset($options['field'])) {
                                throw new Exception("Missing required behavior option field");
                        }

                        if (is_string($options['field'])) {
                                $fields = array($options['field']);
                        }
                        if (is_array($options['field'])) {
                                $fields = $options['field'];
                        }

                        foreach ($fields as $field) {
                                $model->$field = str_replace($options['search'], $options['replace'], $model->$field);
                        }

                        return true;
                }
        }

}
