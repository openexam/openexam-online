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
// File:    Diagnostics.php
// Created: 2016-06-02 02:28:58
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor;

use OpenExam\Library\Monitor\Diagnostics\Authenticator as AuthServiceCheck;
use OpenExam\Library\Monitor\Diagnostics\Catalog as CatalogServiceCheck;
use OpenExam\Library\Monitor\Diagnostics\Database as DatabaseServiceCheck;
use OpenExam\Library\Monitor\Diagnostics\ServiceCheck;
use OpenExam\Library\Monitor\Diagnostics\WebServer as WebServerCheck;

/**
 * System diagnostics.
 * 
 * Registry of service checks (diagnostics). This class also provides for
 * handling all service checks uniform:
 * 
 * <code>
 * $diag = new Diagnostics();
 * 
 * if ($diag->isOnline() && $diag->isWorking()) {
 *      $response->sendResult(array(
 *              "ok" => true
 *      ));
 * } else {
 *      $response->sendResult(array(
 *              "status" => $diag->getResult()
 *      ));
 * }
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Diagnostics implements ServiceCheck
{

        /**
         * The available checks.
         * @var array 
         */
        private $_checks = array();

        /**
         * Constructor.
         */
        public function __construct()
        {
                $this->register('auth', AuthServiceCheck::class);
                $this->register('catalog', CatalogServiceCheck::class);
                $this->register('database', DatabaseServiceCheck::class);
                $this->register('web', WebServerCheck::class);
        }

        /**
         * Test if service check is registered.
         * @param string $name The check name (e.g. 'auth')
         * @return boolean
         */
        public function hasServiceCheck($name)
        {
                return array_key_exists($name, $this->_checks);
        }

        /**
         * Get service check.
         * @param string $name The service check name (e.g. 'auth').
         * @return ServiceCheck
         */
        public function getServiceCheck($name)
        {
                if (!isset($this->_checks[$name])) {
                        return false;
                } else {
                        return $this->getCheckInstance($name);
                }
        }

        /**
         * Get all service checks.
         * @return ServiceCheck[]
         */
        public function getServiceChecks()
        {
                return $this->_checks;
        }

        /**
         * Register an diagnostics test.
         * @param string $name The diagnostics test name.
         * @param string $type The type name (class).
         */
        public function register($name, $type)
        {
                $this->_checks[$name] = $type;
        }

        /**
         * Get result from all service checks.
         * @return array
         */
        public function getResult()
        {
                $result = array();

                foreach (array_keys($this->_checks) as $name) {
                        $result[$name] = $this->getCheckInstance($name)->getResult();
                }

                return $result;
        }

        /**
         * Check online status for all service checks.
         * @return array
         */
        public function isOnline()
        {
                $result = array();

                foreach (array_keys($this->_checks) as $name) {
                        $result[$name] = $this->getCheckInstance($name)->isOnline();
                }

                return $result;
        }

        /**
         * Check working status for all service checks.
         * @return array
         */
        public function isWorking()
        {
                $result = array();

                foreach (array_keys($this->_checks) as $name) {
                        $result[$name] = $this->getCheckInstance($name)->isWorking();
                }

                return $result;
        }

        /**
         * Check failure status for all service checks.
         * 
         * This method returns false if at least of the services has failed.
         * One of the isOnline() or isWorking() has to be called in advance
         * to update the service check state.
         * 
         * @return boolean
         */
        public function hasFailed()
        {
                foreach (array_keys($this->_checks) as $name) {
                        if ($this->getCheckInstance($name)->hasFailed()) {
                                return true;
                        }
                }

                return false;
        }

        /**
         * On demand instantiate the service check.
         * 
         * @param string $name The service check name.
         * @return ServiceCheck
         */
        private function getCheckInstance($name)
        {
                if (!is_object($this->_checks[$name])) {
                        $this->_checks[$name] = new $this->_checks[$name]();
                }

                return $this->_checks[$name];
        }

}
