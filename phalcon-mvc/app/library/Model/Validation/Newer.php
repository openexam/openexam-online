<?php

/*
 * Copyright (C) 2018 The OpenExam Project
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

/*
 * Created: 2018-05-23 23:10:07
 * File:    Newer.php
 * 
 * Author:  Anders Lövgren (Nowise Systems)
 */

namespace OpenExam\Library\Model\Validation;

use OpenExam\Models\Answer;
use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;

/**
 * Modified datetime check.
 * 
 * Check that a model about to be written is newer than the previous saved
 * in database.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Newer extends Validator implements ValidatorInterface
{

        /**
         * Executes the validation
         *
         * @param Validation $validator
         * @param string     $attribute
         * @return boolean
         */
        public function validate(Validation $validator, $attribute)
        {
                // 
                // Get bound model:
                // 
                $record = $validator->getEntity();
                
                // 
                // Get current values:
                // 
                if ($this->hasOption("current")) {
                        $current = $this->getOption("current");
                } else {
                        $current = array();
                }
                
                if(!isset($current[$attribute])) {
                        $current[$attribute] = strftime("%x %X", 0);
                }
                
                // 
                // Get UNIX timestamp for compare:
                //
                $ctime = strtotime($current[$attribute]);
                $mtime = strtotime($record->$attribute);

                // 
                // Check if new record is older than current:
                // 
                if ($mtime < $ctime) {
                        $message = $this->getOption("message");
                        $validator->appendMessage(new Message($message, $attribute));
                        return false;
                }

                // 
                // The new record is newer:
                // 
                return true;
        }

}
