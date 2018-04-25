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
// File:    CatalogTask.php
// Created: 2016-11-14 07:22:00
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Console\Tasks;

use OpenExam\Library\Catalog\DirectoryService;
use OpenExam\Library\Catalog\Principal;

/**
 * Catalog service task.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class CatalogTask extends MainTask implements TaskInterface
{

        /**
         * Runtime options
         * @var array 
         */
        private $_options;
        /**
         * The directory service.
         * @var DirectoryService
         */
        private $_service;

        public function helpAction()
        {
                parent::showUsage(self::getUsage());
        }

        public static function getUsage()
        {
                return array(
                        'header'   => 'Catalog service query tool',
                        'action'   => '--catalog',
                        'usage'    => array(
                                '--principal --needle=val --search=attr [--domain=name] [--service=name] [--limit=num]',
                                '--attribute=name --principal=user',
                                '--attributes=name --principal=user',
                                '--groups --principal=user',
                                '--members --group=name',
                                '--domains|--services'
                        ),
                        'options'  => array(
                                '--principal'       => 'Search for user principal.',
                                '--principal=user'  => 'User principal as search parameter.',
                                '--search=attr'     => 'Search for attribute (user principal search).',
                                '--needle=val'      => 'The attribute value (user principal search).',
                                '--attribute=name'  => 'Search for user attributes.',
                                '--attributes=name' => 'Search for user attributes.',
                                '--groups'          => 'Search for groups where user principal is member.',
                                '--group=name'      => 'Use group name as search parameter for members listing.',
                                '--members'         => 'Search for group members.',
                                '--domain=name'     => 'Restrict search to given domain.',
                                '--service=name'    => 'Restrict search to given service.',
                                '--limit=num'       => 'Limit number of records returned from user principal search.',
                                '--domains'         => 'List all directory domains in catalog manager.',
                                '--services'        => 'List all services in catalog manager.',
                                '--format'          => 'Set output format (serialize,export,dump,json).',
                                '--verbose'         => 'Be more verbose.'
                        ),
                        'examples' => array(
                                array(
                                        'descr'   => 'List services in all domains',
                                        'command' => '--catalog --services'
                                ),
                                array(
                                        'descr'   => 'Look at yourself',
                                        'command' => '--catalog --principal=user@example.com'
                                ),
                                array(
                                        'descr'   => 'Get user principals with given name in domain user.uu.se',
                                        'command' => '--catalog --principal --search=gn --needle=Anders --domain=user.uu.se'
                                ),
                                array(
                                        'descr'   => 'Get user principals with given name in service akka',
                                        'command' => '--catalog --principal --search=gn --needle=Anders --service=akka --limit=15'
                                ),
                                array(
                                        'descr'   => 'Same, but restrict returned attributes',
                                        'command' => '--catalog --principal --search=gn --needle=Anders --service=akka --limit=15 --attributes=name,mail,uid,department'
                                ),
                                array(
                                        'descr'   => 'Get members of given group',
                                        'command' => '--catalog --members --group="BMC Mediateket CBE Manager"'
                                ),
                                array(
                                        'descr'   => 'Get user groups',
                                        'command' => '--catalog --groups --principal=user@example.com'
                                ),
                                array(
                                        'descr'   => 'Get all mail attribute for user',
                                        'command' => '--catalog --attributes=mail --principal=user@example.com'
                                )
                        )
                );
        }

        /**
         * Group search action.
         * @param array $params
         */
        public function groupsAction($params = array())
        {
                $this->setOptions($params, 'group');
                $result = $this->_service->getGroups($this->_options['principal']);
                $this->format($result);
        }

        /**
         * Members search action.
         * @param array $params
         */
        public function membersAction($params = array())
        {
                $this->setOptions($params, 'members');
                $result = $this->_service->getMembers($this->_options['group']);
                $this->format($result);
        }

        /**
         * Attribute search action.
         * @param array $params
         */
        public function attributeAction($params = array())
        {
                $this->setOptions($params, 'attribute');
                $result = $this->_service->getAttribute($this->_options['attribute'], $this->_options['principal']);
                $this->format($result);
        }

        /**
         * Attributes search action.
         * @param array $params
         */
        public function attributesAction($params = array())
        {
                $this->setOptions($params, 'attributes');
                $result = $this->_service->getAttributes($this->_options['attribute'], $this->_options['principal']);
                $this->format($result);
        }

        /**
         * Principals search action.
         * @param array $params
         */
        public function principalAction($params = array())
        {
                $this->setOptions($params, 'principal');

                if ($this->_options['domain']) {
                        $this->_service->setDefaultDomain($this->_options['domain']);
                }

                if ($this->_options['attributes']) {
                        if (strstr($this->_options['attributes'], ',')) {
                                $this->_options['attribute'] = explode(',', $this->_options['attributes']);
                        } else {
                                $this->_options['attribute'] = $this->_options['attributes'];
                        }
                }

                // 
                // Look at yourself:
                // 
                if (is_string($this->_options['principal'])) {
                        $this->_options['needle'] = $this->_options['principal'];
                        $this->_options['search'] = Principal::ATTR_PN;
                        if (!$this->_options['limit']) {
                                $this->_options['limit'] = 1;
                        }
                        if (!$this->_options['attribute']) {
                                $this->_options['attribute'] = Principal::ATTR_ALL;
                        }
                }

                if ($this->_options['limit'] == 1) {
                        $result = $this->_service->getPrincipal(
                            $this->_options['needle'], $this->_options['search'], $this->_options['domain'], $this->_options['attribute']
                        );
                } else {
                        $result = $this->_service->getPrincipals(
                            $this->_options['needle'], $this->_options['search'], array(
                                'domain' => $this->_options['domain'],
                                'limit'  => $this->_options['limit'],
                                'attr'   => $this->_options['attribute']
                        ));
                }

                $this->format($result);
        }

        /**
         * Doamin listing action.
         * @param array $params
         */
        public function domainsAction($params = array())
        {
                $this->setOptions($params, 'domains');
                $result = $this->_service->getDomains();
                $this->format($result);
        }

        /**
         * Services listing action.
         * @param array $params
         */
        public function servicesAction($params = array())
        {
                $this->setOptions($params, 'services');
                $result = $this->_service->getDomains();
                foreach ($result as $domain) {
                        $this->flash->notice(sprintf("%s ->", $domain));
                        foreach ($this->_service->getServices($domain) as $service) {
                                $this->flash->notice(sprintf("\t%s", $service->getServiceName()));
                        }
                }
        }

        /**
         * Format output.
         * @param array|string $result The result to format.
         */
        private function format($result)
        {
                switch ($this->_options['format']) {
                        case 'serialize':
                                echo serialize($result);
                                break;
                        case 'dump':
                                print_r($result);
                                break;
                        case 'export':
                                echo var_export($result);
                                break;
                        case 'json':
                                echo json_encode($result);
                                break;
                }
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
                $this->_options = array('verbose' => false, 'format' => 'dump');

                // 
                // Supported options.
                // 
                $options = array('verbose', 'groups', 'group', 'members', 'principal', 'attribute', 'attributes', 'domain', 'service', 'limit', 'needle', 'search', 'domains', 'services', 'format');
                $current = $action;

                // 
                // Set defaults.
                // 
                foreach ($options as $option) {
                        if (!isset($this->_options[$option])) {
                                $this->_options[$option] = false;
                        }
                }

                // 
                // Include action in options (for multitarget actions).
                // 
                if (isset($action)) {
                        $this->_options[$action] = true;
                }

                // 
                // Scan params for both --key and --key=val options.
                // 
                while (($option = array_shift($params))) {
                        if (in_array($option, $options)) {
                                $this->_options[$option] = true;
                                $current = $option;
                        } elseif (in_array($current, $options)) {
                                $this->_options[$current] = $option;
                        } else {
                                throw new Exception("Unknown task action/parameters '$option'");
                        }
                }

                // 
                // Pluralis form is more or less a convenient option:
                // 
                if ($action == 'attributes') {
                        $this->_options['attribute'] = $this->_options['attributes'];
                        unset($this->_options['attributes']);
                }

                // 
                // Use specific service if requested:
                // 
                if ($this->_options['service']) {
                        $this->_service = $this->catalog->getService($this->_options['service']);
                } else {
                        $this->_service = $this->catalog;
                }
                if (is_null($this->_service)) {
                        throw new Exception("Undefined service requested");
                }

                // 
                // Overcome limitation in options parsing when the the search
                // argument is the same as the option name:
                // 
                if ($this->_options['search'] && $this->_options['principal']) {
                        $this->_options['search'] = 'principal';
                }
        }

}
