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
 * Created: 2018-06-07 23:32:09
 * File:    Purifier.php
 * 
 * Author:  Anders Lövgren (Nowise Systems)
 */

namespace OpenExam\Library\Model\Behavior\Transform;

use OpenExam\Library\Model\Behavior\ModelBehavior;
use Phalcon\Mvc\ModelInterface;
use const PROJ_DIR;

require_once(PROJ_DIR . '/vendor/ezyang/htmlpurifier/library/HTMLPurifier.safe-includes.php');

/**
 * HTML purifier.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Purifier extends ModelBehavior
{

        public function notify($type, ModelInterface $model)
        {
                if (($options = $this->getOptions($type))) {
                        if (!isset($options['fields'])) {
                                $options['fields'] = array_keys($model->columnMap());
                        }
                        if (!isset($options['config'])) {
                                $options['config'] = array(
                                        'enable' => true,
                                        'cache'  => '/tmp/purify'
                                );
                        }

                        if (!$options['config']['enable']) {
                                return true;
                        }
                        if (!file_exists($options['config']['cache'])) {
                                if (!mkdir($options['config']['cache'])) {
                                        $this->logger->system->error("Failed create purify cache directory");
                                }
                        }

                        if (is_string($options['fields'])) {
                                $fields = array($options['fields']);
                        }
                        if (is_array($options['fields'])) {
                                $fields = $options['fields'];
                        }

                        $cache = $options['config']['cache'];
                        $clean = $this->create($cache);

                        foreach ($fields as $field) {
                                if (!isset($model->$field)) {
                                        continue;
                                }
                                if (is_numeric($model->$field) ||
                                    is_object($model->$field) ||
                                    is_null($model->$field)) {
                                        continue;
                                }
                                if (is_string($model->$field)) {
                                        $model->$field = $clean->purify($model->$field);
                                }
                        }

                        return true;
                }
        }

        /**
         * Create HTML purifier.
         * 
         * @param string $cache The cache directory.
         * @return HTMLPurifier 
         */
        private function create($cache)
        {
                $config = \HTMLPurifier_Config::createDefault();
                $config->set("Cache.SerializerPath", $cache);
                $object = new \HTMLPurifier($config);

                return $object;
        }

}
