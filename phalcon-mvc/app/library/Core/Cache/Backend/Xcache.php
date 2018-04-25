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
// File:    Xcache.php
// Created: 2017-01-02 07:33:08
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace OpenExam\Library\Core\Cache\Backend;

use Phalcon\Cache\Backend;
use Phalcon\Cache\Backend\Prefixable;
use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Exception;

/**
 * Alternative XCache backend.
 * 
 * The XCache backend in Phalcon caused memory limit exhausted errors in some
 * cases. This class can be used as a replacement for that class.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Xcache extends Backend implements BackendInterface
{

        use Prefixable;

        /**
         * {@inheritdoc}
         *
         * @param  string  $keyName
         * @return bool
         */
        public function delete($keyName)
        {
                $ckey = $this->getPrefixedIdentifier($keyName);
                return xcache_unset($ckey);
        }

        /**
         * {@inheritdoc}
         *
         * @param  string  $keyName
         * @param  string  $lifetime
         * @return bool
         */
        public function exists($keyName = null, $lifetime = null)
        {
                $ckey = $this->getPrefixedIdentifier($keyName);
                return xcache_isset($ckey);
        }

        /**
         * {@inheritdoc}
         *
         * @param  string     $keyName
         * @param  integer    $lifetime
         * @return mixed|null
         */
        public function get($keyName, $lifetime = null)
        {
                $ckey = $this->getPrefixedIdentifier($keyName);
                $data = xcache_get($ckey);

                $this->_lastKey = $ckey;

                return $this->_frontend->afterRetrieve($data);
        }

        /**
         * {@inheritdoc}
         *
         * @param  string $keyName
         * @param  string $content
         * @param  int    $lifetime
         * @param  bool   $stopBuffer
         * @throws Exception
         */
        public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
        {
                if ($keyName === null) {
                        $ckey = $this->_lastKey;
                } else {
                        $ckey = $this->getPrefixedIdentifier($keyName);
                }

                if ($content === null) {
                        $data = $this->_frontend->getContent();
                } else {
                        $data = $content;
                }

                if (is_string($data)) {
                        xcache_set($ckey, $data, $lifetime);
                } else {
                        xcache_set($ckey, serialize($data), $lifetime);
                }

                if ($stopBuffer) {
                        $this->_frontend->stop();
                }

                $this->_started = false;
        }

        /**
         * {@inheritdoc}
         * 
         * @todo Not yet implemented
         * @param  string $prefix
         * @return array
         */
        public function queryKeys($prefix = null)
        {
                return null;
        }

}
