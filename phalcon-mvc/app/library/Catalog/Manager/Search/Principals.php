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
// File:    Principals.php
// Created: 2017-04-12 00:10:14
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Catalog\Manager\Search;

use OpenExam\Library\Catalog\DirectoryManager;
use OpenExam\Library\Catalog\DirectoryService;
use OpenExam\Library\Catalog\Exception;
use OpenExam\Library\Catalog\Manager\Search;
use OpenExam\Library\Catalog\Principal as UserPrincipal;

/**
 * Directory principals search.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Principals implements Search
{

        /**
         * The attribute search string.
         * @var string 
         */
        private $_needle;
        /**
         * The attribute to query.
         * @var string 
         */
        private $_attrib;
        /**
         * Miscellanous search options
         * @var array 
         */
        private $_options;

        /**
         * Constructor.
         * 
         * @param DirectoryManager $manager The directory manager.
         * @param string $needle The attribute search string.
         * @param string $attrib The attribute to query (optional).
         * @param array $options Miscellaneous search options (optional).
         */
        public function __construct($manager, $needle, $attrib = null, $options = null)
        {
                if (!isset($attrib) || $attrib == false) {
                        $attrib = DirectoryManager::DEFAULT_SEARCH_ATTRIB;
                }
                if (!isset($options) || $options == false) {
                        $options = array();
                }
                if (!isset($options['attr']) || $options['attr'] == false) {
                        $options['attr'] = $manager->getFilter();
                }
                if (!isset($options['limit']) || $options['limit'] == false) {
                        $options['limit'] = DirectoryManager::DEFAULT_RESULT_LIMIT;
                }
                if ($attrib == UserPrincipal::ATTR_PN) {
                        $options['domain'] = $manager->getRealm($needle);
                }
                if (!is_array($options['attr'])) {
                        $options['attr'] = array($options['attr']);
                }

                $this->_needle = $needle;
                $this->_attrib = $attrib;
                $this->_options = $options;
        }

        /**
         * Get search attribute.
         * @return string
         */
        public function getAttribute()
        {
                return $this->_attrib;
        }

        /**
         * Set search attribute.
         * @param string $attrib The search attribute.
         */
        public function setAttribute($attrib)
        {
                $this->_attrib = $attrib;
        }

        /**
         * Set search string.
         * @param string $needle The search string.
         */
        public function setNeedle($needle)
        {
                $this->_needle = $needle;
        }

        /**
         * Get search options.
         * @return array
         */
        public function getOptions()
        {
                return $this->_options;
        }

        /**
         * Set search domain.
         * @param string $domain The search domain.
         */
        public function setDomain($domain)
        {
                $this->_options['domain'] = $domain;
        }

        /**
         * Set attributes filter.
         * @param array $attributes The returned attributes.
         */
        public function setFilter($attributes)
        {
                $this->_options['attr'] = $attributes;
        }

        /**
         * Get directory manager search result.
         * @param DirectoryManager $manager The directory manager.
         * @return string|array
         */
        public function getResult($manager)
        {
                $result = array();
                $domain = $this->_options['domain'];

                $limit = $this->_options['limit'];

                foreach ($manager->getServices($domain) as $name => $service) {
                        if (($principals = $this->getPrincipals($manager, $service, $name)) != null) {
                                if ($limit == 0) {
                                        $result = array_merge($result, $principals);
                                } elseif (count($principals) + count($result) < $limit) {
                                        $result = array_merge($result, $principals);
                                } else {
                                        $insert = $limit - count($result);
                                        $result = array_merge($result, array_slice($principals, 0, $insert));
                                        return $result;
                                }
                        }
                }

                return $result;
        }

        /**
         * Get directory principals.
         * 
         * @param DirectoryManager $manager The directory manager.
         * @param DirectoryService $service The directory service.
         * @param string $name The service name.
         * @return array
         */
        private function getPrincipals($manager, $service, $name)
        {
                try {
                        return $service->getPrincipals($this->_needle, $this->_attrib, $this->_options);
                } catch (Exception $exception) {
                        $manager->report($exception, $service, $name);
                }
        }

}
