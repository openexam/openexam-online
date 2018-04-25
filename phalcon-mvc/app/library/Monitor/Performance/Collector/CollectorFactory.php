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
// File:    CollectorFactory.php
// Created: 2016-06-10 17:46:29
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Monitor\Performance\Collector;

use OpenExam\Library\Monitor\Exception;
use OpenExam\Library\Monitor\Performance\Collector;
use OpenExam\Library\Monitor\Performance\Trigger\Cleanup;
use OpenExam\Library\Monitor\Performance\Trigger\Timeline;
use OpenExam\Models\Performance;

/**
 * Factory class creating collectors.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class CollectorFactory
{

        /**
         * Create new performance collector.
         * 
         * <code>
         * $collector = CollectorFactory::create(
         *      Counter\Apache::TYPE, 
         *      array(
         *              'params' => array(
         *                      'rate' => 5,
         *                      'user' => 'apache'
         *              ),
         *              'triggers' => array(
         *                      'timeline' => true,
         *                      'cleanup'  => true
         *              )
         *      )
         * ));
         * </code>
         * 
         * @param string $type The collector type.
         * @param array $config The monitor config.
         * @return Collector The collector object.
         */
        public static function create($type, $config)
        {
                $params = $config['params'];

                // 
                // Create the collector object:
                // 
                switch ($type) {
                        case Performance::MODE_APACHE:
                                if (!isset($params['rate'])) {
                                        $params['rate'] = Apache::SAMPLE_RATE;
                                }
                                if (!isset($params['user'])) {
                                        $params['user'] = Apache::DEFAULT_USER;
                                }
                                $collector = new Apache($params['rate'], $params['user']);
                                break;

                        case Performance::MODE_DISK:
                                if (!isset($params['rate'])) {
                                        $params['rate'] = Disk::SAMPLE_RATE;
                                }
                                if (!isset($params['disk'])) {
                                        $params['disk'] = Disk::DEFAULT_DISK;
                                }
                                if (isset($params['source'])) {
                                        $params['disk'] = $params['source'];
                                }
                                $collector = new Disk($params['rate'], $params['disk']);
                                break;

                        case Performance::MODE_FILESYS:
                                if (!isset($params['rate'])) {
                                        $params['rate'] = FileSystem::SAMPLE_RATE;
                                }
                                if (!isset($params['path'])) {
                                        $params['path'] = FileSystem::DEFAULT_PATH;
                                }
                                if (!isset($params['type'])) {
                                        $params['type'] = FileSystem::DEFAULT_TYPE;
                                }
                                if (isset($params['source'])) {
                                        $params['path'] = $params['source'];
                                }
                                $collector = new FileSystem($params['rate'], $params['path'], $params['type']);
                                break;

                        case Performance::MODE_MYSQL:
                                if (!isset($params['rate'])) {
                                        $params['rate'] = MySQL::SAMPLE_RATE;
                                }
                                $collector = new MySQL($params['rate']);
                                break;

                        case Performance::MODE_NETWORK:
                                if (!isset($params['rate'])) {
                                        $params['rate'] = Network::SAMPLE_RATE;
                                }
                                if (!isset($params['name'])) {
                                        $params['name'] = Network::DEFAULT_NAME;
                                }
                                if (isset($params['source'])) {
                                        $params['name'] = $params['source'];
                                }
                                $collector = new Network($params['rate'], $params['name']);
                                break;

                        case Performance::MODE_PARTITION:
                                if (!isset($params['rate'])) {
                                        $params['rate'] = Partition::SAMPLE_RATE;
                                }
                                if (!isset($params['part'])) {
                                        $params['part'] = Partition::DEFAULT_PART;
                                }
                                if (isset($params['source'])) {
                                        $params['part'] = $params['source'];
                                }
                                $collector = new Partition($params['rate'], $params['part']);
                                break;

                        case Performance::MODE_SERVER:
                                if (!isset($params['rate'])) {
                                        $params['rate'] = Server::SAMPLE_RATE;
                                }
                                $collector = new Server($params['rate']);
                                break;

                        case Performance::MODE_SYSTEM:
                                // TODO: Add system performance collector
                                break;

                        default:
                                throw new Exception("The collector type $type is missing");
                }

                // 
                // Attach triggers to the collector:
                // 
                foreach ($config['triggers'] as $name => $options) {
                        switch ($name) {
                                case 'timeline':
                                        if (count($options) != 0) {
                                                $trigger = new Timeline($type, $options);
                                                $collector->addTrigger($trigger);
                                        } else {
                                                $trigger = new Timeline($type);
                                                $collector->addTrigger($trigger);
                                        }
                                        break;
                                case 'cleanup':
                                        if (count($options) != 0) {
                                                $trigger = new Cleanup($type, $options);
                                                $collector->addTrigger($trigger);
                                        } else {
                                                $trigger = new Cleanup($type);
                                                $collector->addTrigger($trigger);
                                        }
                                        break;
                                default:
                                        throw new Exception("Unknown trigger type $name");
                        }
                }

                return $collector;
        }

}
