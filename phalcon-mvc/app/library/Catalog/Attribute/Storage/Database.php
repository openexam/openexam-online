<?php

/*
 * Copyright (C) 2016-2018 The OpenExam Project
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
// File:    Database.php
// Created: 2016-11-13 23:41:55
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Catalog\Attribute\Storage;

use OpenExam\Library\Catalog\Attribute\Storage\Backend;
use OpenExam\Library\Catalog\Exception;
use OpenExam\Models\User;

/**
 * Database storage backend.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Database implements Backend
{

        /**
         * The handled domains.
         * @var array
         */
        private $_domains;

        /**
         * Constructor
         * @param array|string $domains The handled domains.
         */
        public function __construct($domains = '*')
        {
                if (is_string($domains)) {
                        $this->_domains = array($domains);
                } else {
                        $this->_domains = $domains;
                }
        }

        /**
         * Destructor.
         */
        public function __destruct()
        {
                unset($this->_domains);
        }

        /**
         * Check if user exist.
         * @param string $principal The user principal name.
         * @return boolean 
         */
        public function exists($principal)
        {
                if (!$this->handled($principal)) {
                        return false;
                }

                return User::count(array(
                            'conditions' => 'principal = :principal:',
                            'bind'       => array(
                                    'principal' => $principal
                            )
                    )) > 0;
        }

        /**
         * Insert user attributes.
         * @param User $user The user model.
         */
        public function insert($user)
        {
                if (!$this->handled($user->principal)) {
                        return false;
                }

                if (!$user->save()) {
                        throw new Exception($user->getMessages()[0]);
                }
        }

        /**
         * Delete user.
         * @param string $principal The user principal name.
         */
        public function delete($principal)
        {
                if (!$this->handled($principal)) {
                        return false;
                }

                if (($user = User::find(array(
                            'conditions' => 'principal = :principal:',
                            'bind'       => array(
                                    'principal' => $principal
                            )
                    ))) != null) {
                        if (!$user->delete()) {
                                throw new Exception($user->getMessages()[0]);
                        }
                }
        }

        /**
         * Check if domain is handled.
         * @param string $principal The user principal name.
         * @return boolean
         */
        private function handled($principal)
        {
                if ($this->_domains[0] == '*') {
                        return true;
                }

                $domain = substr($principal, strpos($principal, '@') + 1);
                $result = in_array($domain, $this->_domains);

                return $result;
        }

}
