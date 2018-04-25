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
// File:    Catalog.php
// Created: 2016-05-31 02:28:11
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Diagnostics;

use Phalcon\Mvc\User\Component;

/**
 * Diagnostics of catalog services.
 *
 * Perform catalog service diagnostics against selected domain. If domain
 * is missing, then all domains are used. The default user domain is set
 * by system config.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Catalog extends Component implements ServiceCheck
{

        /**
         * The check result.
         * @var array 
         */
        private $_result = array();
        /**
         * True if test has failed.
         * @var boolean 
         */
        private $_failed = false;
        /**
         * The domains to check against.
         * @var array 
         */
        private $_domains;

        /**
         * Constructor.
         * @param string|array $domains The domain(s) to check.
         */
        public function __construct($domains = null)
        {
                if (!isset($domains)) {
                        $this->_domains = $this->catalog->getDomains();
                } elseif (is_array($domains)) {
                        $this->_domains = $domains;
                } else {
                        $this->_domains = array($domains);
                }
        }

        /**
         * Set the domain list.
         * @param array $domains The domain list.
         */
        public function setDomains($domains)
        {
                $this->_domains = $domains;
        }

        /**
         * Get the list of domains.
         * @return array
         */
        public function getDomains()
        {
                return $this->_domains;
        }

        /**
         * Get check result.
         * @return array
         */
        public function getResult()
        {
                return $this->_result;
        }

        /**
         * Check if service is online.
         * @return boolean
         */
        public function isOnline()
        {
                $this->_failed = false;

                foreach ($this->_domains as $domain) {
                        if (!isset($this->_result[$domain])) {
                                $this->_result[$domain] = array();
                        }

                        foreach ($this->catalog->getServices($domain) as $service) {
                                if (($connection = $service->getConnection()) == null) {
                                        continue;
                                }

                                $hostname = $connection->hostname();
                                if (strstr($hostname, '://')) {
                                        $hostname = parse_url($hostname, PHP_URL_HOST);
                                }

                                $online = new OnlineStatus($hostname);

                                if (!$online->checkStatus()) {
                                        $this->_result[$domain][$service->getServiceName()]['online'] = $online->getResult();
                                        $this->_failed = true;
                                } else {
                                        $this->_result[$domain][$service->getServiceName()]['online'] = $online->getResult();
                                }
                        }
                }

                return $this->_failed != true;
        }

        /**
         * Check if service is working.
         * @return boolean
         */
        public function isWorking()
        {
                $this->_failed = false;

                foreach ($this->_domains as $domain) {
                        if (!isset($this->_result[$domain])) {
                                $this->_result[$domain] = array();
                        }

                        foreach ($this->catalog->getServices($domain) as $service) {
                                if (($connection = $service->getConnection()) == null) {
                                        $this->_result[$domain][$service->getServiceName()]['working'] = true;
                                } elseif ($connection->connected()) {
                                        $this->_result[$domain][$service->getServiceName()]['working'] = true;
                                } elseif ($connection->open()) {
                                        $this->_result[$domain][$service->getServiceName()]['working'] = true;
                                        $connection->close();
                                } else {
                                        $this->_result[$domain][$service->getServiceName()]['working'] = false;
                                        $this->_failed = true;
                                }
                        }
                }

                return $this->_failed != true;
        }

        /**
         * True if last check has failed.
         * @boolean
         */
        public function hasFailed()
        {
                return $this->_failed;
        }

}
